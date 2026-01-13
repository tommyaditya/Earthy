// Core Map Logic for Interactive Tourism Map

class TourismMap {
    constructor() {
        this.map = null;
        this.markers = [];
        this.markerClusterGroup = null;
        this.currentFilters = {
            category: 'all',
            rating: null,
            searchQuery: ''
        };
        this.favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        this.userLocation = null;
        this.weatherCache = JSON.parse(localStorage.getItem('weatherCache') || '{}');
        this.cacheExpiry = 10 * 60 * 1000; // 10 minutes in milliseconds

        this.init();
    }

    init() {
        this.initializeMap();
        this.setupEventListeners();
        this.loadDestinations();
        this.initializeWeatherWidget();
        this.updateFavoritesCount();
    }

    initializeMap() {
        // Initialize Leaflet map centered on Indonesia
        this.map = L.map('map').setView([-2.5489, 118.0149], 5);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(this.map);

        // Initialize marker cluster group
        this.markerClusterGroup = L.markerClusterGroup({
            chunkedLoading: true,
            chunkInterval: 200,
            chunkDelay: 50
        });

        this.map.addLayer(this.markerClusterGroup);
    }

    setupEventListeners() {
        // Search input with debounce
        const searchInput = document.getElementById('search-input');
        const debouncedSearch = this.debounce(this.handleSearch.bind(this), 300);
        searchInput.addEventListener('input', debouncedSearch);

        // Clear search button
        document.getElementById('clear-search').addEventListener('click', () => {
            searchInput.value = '';
            this.currentFilters.searchQuery = '';
            this.filterDestinations();
        });

        // Category filters
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.currentFilters.category = e.target.dataset.category;
                this.filterDestinations();
            });
        });

        // Rating filters
        document.querySelectorAll('.rating-filters input').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checkedRatings = Array.from(document.querySelectorAll('.rating-filters input:checked'))
                    .map(cb => parseInt(cb.value));
                this.currentFilters.rating = checkedRatings.length > 0 ? Math.min(...checkedRatings) : null;
                this.filterDestinations();
            });
        });

        // Tourism list items - click to show quick view
        document.addEventListener('click', (e) => {
            if (e.target.closest('.tourism-item')) {
                const item = e.target.closest('.tourism-item');
                const destinationId = item.dataset.id;
                const destination = this.tourismData.find(d => d.id == destinationId);
                if (destination) {
                    this.showQuickView(destination);
                }
            }
        });

        // Sidebar toggle
        document.getElementById('sidebar-toggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Location button
        document.getElementById('location-btn').addEventListener('click', () => {
            this.getUserLocation();
        });

        // Favorites button
        document.getElementById('favorites-btn').addEventListener('click', () => {
            this.showFavoritesModal();
        });

        // Modal close buttons
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.modal').style.display = 'none';
            });
        });

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    }

    async loadDestinations(forceRefetch = false) {
        // Only fetch from API if data not cached or force refetch
        if (!this.tourismData || this.tourismData.length === 0 || forceRefetch) {
            try {
                // Load data from API
                const response = await fetch('api/destinations.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();

                // Convert API response to destination format
                this.tourismData = result.data.map(dest => ({
                    id: dest.id,
                    name: dest.name,
                    category: dest.category,
                    location: dest.location,
                    coords: [parseFloat(dest.latitude), parseFloat(dest.longitude)],
                    rating: dest.rating || 0,
                    description: dest.description,
                    hours: dest.opening_hours || '-',
                    price: dest.ticket_price || '-',
                    images: dest.primary_image ? [dest.primary_image] : []
                }));
            } catch (error) {
                window.showToast('Gagal memuat data destinasi. Silakan refresh halaman.', 'error');
                this.tourismData = [];
            }
        }

        // Clear existing markers
        this.clearMarkers();

        const filteredData = this.getFilteredData();

        filteredData.forEach(destination => {
            this.addMarker(destination);
        });

        this.updateResultsList(filteredData);
        this.updateResultsCount(filteredData.length);
    }

    async addMarker(destination) {
        const popupContent = await this.createPopupContent(destination);
        const customIcon = this.getCategoryIcon(destination.category);

        const marker = L.marker(destination.coords, { icon: customIcon })
            .bindPopup(popupContent);

        // Add to cluster group
        this.markerClusterGroup.addLayer(marker);
        this.markers.push({ marker, destination });

        // Add click event to center map and show details
        marker.on('click', () => {
            this.map.setView(destination.coords, 15);
        });
    }

    async createPopupContent(destination) {
        const isFavorite = this.favorites.includes(destination.id);

        // Get weather for this destination
        let weatherInfo = '';
        try {
            const weatherData = await this.getWeatherForDestination(destination.coords);
            if (weatherData) {
                weatherInfo = `
                    <div class="popup-weather">
                        <i class="fas fa-${this.getWeatherIcon(weatherData.weather[0].main)}"></i>
                        <span>${Math.round(weatherData.main.temp)}°C</span>
                        <small>${weatherData.weather[0].description}</small>
                    </div>
                `;
            }
        } catch (error) {
            console.warn('Could not load weather for popup:', error);
        }

        // Resolve image path (use helper to support http links, relative paths, and filenames)
        const imgSrc = this.resolveImagePath(Array.isArray(destination.images) && destination.images.length ? destination.images[0] : null);

        return `
            <div class="popup-content">
                <img src="${imgSrc}" alt="${destination.name}" class="popup-image">
                <h3>${destination.name}</h3>
                <p class="popup-location">${destination.location}</p>
                <p class="popup-description">${destination.description}</p>
                <div class="popup-price">
                    <i class="fas fa-tag"></i>
                    <span>${destination.price}</span>
                </div>
                ${weatherInfo}
                <div class="popup-rating">
                    ${window.createStarRating(destination.rating)}
                    <span>${destination.rating}/5</span>
                </div>
                <div class="popup-category" style="background-color: ${window.getCategoryColor(destination.category)}">
                    ${destination.category.charAt(0).toUpperCase() + destination.category.slice(1)}
                </div>
                <div class="popup-actions">
                    <a href="detail.html?id=${destination.id}" class="popup-btn">Lihat Detail</a>
                    <button class="popup-favorite ${isFavorite ? 'active' : ''}" onclick="mapInstance.toggleFavorite(${destination.id})">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            </div>
        `;
    }

    async getWeatherForDestination(coords) {
        const cacheKey = `${coords[0]},${coords[1]}`;
        const now = Date.now();

        // Check cache first
        if (this.weatherCache[cacheKey] && (now - this.weatherCache[cacheKey].timestamp) < this.cacheExpiry) {
            return this.weatherCache[cacheKey].data;
        }



        try {
            // BMKG API endpoint for weather data
            const response = await fetch(
                `https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${coords[0]},${coords[1]}`
            );

            if (!response.ok) {
                return null;
            }

            const data = await response.json();

            // Cache the data
            this.weatherCache[cacheKey] = {
                data: data,
                timestamp: now
            };
            localStorage.setItem('weatherCache', JSON.stringify(this.weatherCache));

            return data;
        } catch (error) {
            console.warn('Weather API error for destination:', error);
            return null;
        }
    }

    clearMarkers() {
        this.markerClusterGroup.clearLayers();
        this.markers = [];
    }

    handleSearch(e) {
        this.currentFilters.searchQuery = e.target.value.trim();
        this.filterDestinations();
    }

    filterDestinations() {
        // Use cached data, don't refetch from API
        this.loadDestinations(false);
    }

    getFilteredData() {
        let filtered = this.tourismData || [];

        // Apply category filter
        if (this.currentFilters.category !== 'all') {
            filtered = filtered.filter(item => item.category === this.currentFilters.category);
        }

        // Apply rating filter
        if (this.currentFilters.rating) {
            filtered = filtered.filter(item => item.rating >= this.currentFilters.rating);
        }

        // Apply search filter
        if (this.currentFilters.searchQuery) {
            const query = this.currentFilters.searchQuery.toLowerCase();
            filtered = filtered.filter(item =>
                item.name.toLowerCase().includes(query) ||
                item.location.toLowerCase().includes(query) ||
                item.description.toLowerCase().includes(query)
            );
        }

        return filtered;
    }

    updateResultsList(destinations) {
        const resultsList = document.getElementById('results-list');
        resultsList.innerHTML = '';

        if (destinations.length === 0) {
            resultsList.innerHTML = '<div class="no-results">Tidak ada destinasi yang ditemukan</div>';
            return;
        }

        destinations.forEach(destination => {
            const item = this.createResultItem(destination);
            resultsList.appendChild(item);
        });

        // Update tourism list in filters section
        this.updateTourismList(destinations);
    }

    updateTourismList(destinations) {
        const tourismList = document.querySelector('.tourism-list');
        if (!tourismList) return;

        tourismList.innerHTML = '';

        // Show all filtered destinations in the list (no limit for tourism list)
        destinations.forEach(destination => {
            const item = document.createElement('div');
            item.className = 'tourism-item';
            item.dataset.id = destination.id;

            const iconMap = {
                'alam': 'tree',
                'budaya': 'landmark',
                'kuliner': 'utensils',
                'sejarah': 'monument'
            };
            const iconName = iconMap[destination.category] || 'map-marker-alt';

            item.innerHTML = `
                <div class="tourism-item-icon">
                    <i class="fas fa-${iconName}"></i>
                </div>
                <div class="tourism-item-info">
                    <h5>${destination.name}</h5>
                    <p>${destination.location}</p>
                </div>
            `;

            tourismList.appendChild(item);
        });
    }

    createResultItem(destination) {
        const item = document.createElement('div');
        item.className = 'result-item';
        if (this.favorites.includes(destination.id)) {
            item.classList.add('favorite');
        }

        // Resolve image path with fallback
        const imgSrc = this.resolveImagePath(destination.images && destination.images.length ? destination.images[0] : null);

        item.innerHTML = `
            <img src="${imgSrc}" alt="${destination.name}" class="result-image" onerror="this.onerror=null;this.src='assets/images/placeholder.jpg';">
            <div class="result-info">
                <h4><a href="detail.html?id=${destination.id}">${destination.name}</a></h4>
                <p>${window.truncateText(destination.description, 100)}</p>
                <div class="result-meta">
                    <div class="result-rating">
                        <div class="stars">${window.createStarRating(destination.rating)}</div>
                        <span>${destination.rating}/5</span>
                    </div>
                    <div class="result-category" style="background-color: ${window.getCategoryColor(destination.category)}">
                        ${destination.category.charAt(0).toUpperCase() + destination.category.slice(1)}
                    </div>
                </div>
            </div>
        `;

        // Single click event listener (removed duplicate)
        item.addEventListener('click', () => {
            this.showQuickView(destination);
        });

        return item;
    }

    updateResultsCount(count) {
        const countElement = document.getElementById('results-count');
        countElement.textContent = `${count} destinasi`;
    }

    getUserLocation() {
        if (!navigator.geolocation) {
            window.showToast('Geolokasi tidak didukung oleh browser ini', 'error');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const { latitude, longitude } = position.coords;
                this.userLocation = [latitude, longitude];

                // Add user location marker
                if (this.userMarker) {
                    this.map.removeLayer(this.userMarker);
                }

                this.userMarker = L.marker([latitude, longitude], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<i class="fas fa-user"></i>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    })
                }).addTo(this.map).bindPopup('Lokasi Anda');

                this.map.setView([latitude, longitude], 13);
                window.showToast('Lokasi Anda berhasil ditemukan', 'success');

                // Update weather widget with user location
                await this.updateWeather([latitude, longitude]);

                // Show nearby destinations
                this.showNearbyDestinations([latitude, longitude]);
            },
            (error) => {
                let message = 'Tidak dapat mengakses lokasi Anda';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Akses lokasi ditolak. Izinkan akses lokasi untuk fitur ini.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        message = 'Waktu permintaan lokasi habis.';
                        break;
                }
                window.showToast(message, 'error');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    }

    showNearbyDestinations(userCoords) {
        const nearby = (this.tourismData || [])
            .map(dest => ({
                ...dest,
                distance: window.calculateDistance(userCoords, dest.coords)
            }))
            .filter(dest => dest.distance <= 50) // Within 50km
            .sort((a, b) => a.distance - b.distance)
            .slice(0, 5);

        if (nearby.length > 0) {
            // Add nearby markers with different styling
            nearby.forEach(dest => {
                const nearbyMarker = L.marker(dest.coords, {
                    icon: L.divIcon({
                        className: 'nearby-marker',
                        html: '<i class="fas fa-map-marker-alt"></i>',
                        iconSize: [25, 25],
                        iconAnchor: [12, 25]
                    })
                }).addTo(this.map).bindPopup(`
                    <b>${dest.name}</b><br>
                    ${dest.location}<br>
                    <small>${dest.distance.toFixed(1)} km dari lokasi Anda</small>
                `);
            });
        }
    }

    toggleFavorite(id) {
        const index = this.favorites.indexOf(id);
        if (index > -1) {
            this.favorites.splice(index, 1);
        } else {
            this.favorites.push(id);
        }

        localStorage.setItem('favorites', JSON.stringify(this.favorites));
        this.updateFavoritesCount();
        this.loadDestinations(); // Refresh to update favorite indicators
        window.showToast(
            index > -1 ? 'Dihapus dari favorit' : 'Ditambahkan ke favorit',
            'success'
        );
    }

    showQuickView(destination) {
        const modal = document.getElementById('modal-quick-view');
        const title = document.getElementById('quick-view-title');
        const mainImage = document.getElementById('quick-view-main-image');
        const thumbnails = document.getElementById('quick-view-thumbnails');
        const rating = document.getElementById('quick-view-rating');
        const category = document.getElementById('quick-view-category');
        const location = document.getElementById('quick-view-location');
        const description = document.getElementById('quick-view-description');
        const hours = document.getElementById('quick-view-hours');
        const price = document.getElementById('quick-view-price');
        const contact = document.getElementById('quick-view-contact');
        const contactContainer = document.getElementById('quick-view-contact-container');
        const detailBtn = document.getElementById('quick-view-detail-btn');
        const favoriteBtn = document.getElementById('quick-view-favorite-btn');
        const shareBtn = document.getElementById('quick-view-share-btn');
        const directionsBtn = document.getElementById('quick-view-directions-btn');

        // Set content
        title.textContent = destination.name;
        mainImage.src = destination.images[0];
        mainImage.alt = destination.name;
        rating.textContent = destination.rating;

        category.textContent = destination.category.charAt(0).toUpperCase() + destination.category.slice(1);
        category.style.backgroundColor = window.getCategoryColor(destination.category);

        location.textContent = destination.location;
        description.textContent = destination.description;
        hours.textContent = destination.hours || 'Tidak tersedia';
        price.textContent = destination.price || 'Gratis';

        if (destination.contact) {
            contact.textContent = destination.contact;
            contactContainer.style.display = 'flex';
        } else {
            contactContainer.style.display = 'none';
        }

        // Generate thumbnails
        thumbnails.innerHTML = '';
        destination.images.forEach((img, index) => {
            const thumb = document.createElement('img');
            thumb.src = img;
            thumb.alt = `${destination.name} ${index + 1}`;
            thumb.classList.add('thumbnail');
            if (index === 0) thumb.classList.add('active');
            thumb.addEventListener('click', () => {
                mainImage.src = img;
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
            thumbnails.appendChild(thumb);
        });

        // Set button actions
        detailBtn.href = `detail.html?id=${destination.id}`;

        const isFavorite = this.favorites.includes(destination.id);
        favoriteBtn.classList.toggle('active', isFavorite);
        favoriteBtn.onclick = () => {
            this.toggleFavorite(destination.id);
            favoriteBtn.classList.toggle('active');
        };

        shareBtn.onclick = () => this.shareDestination(destination);
        directionsBtn.onclick = () => this.getDirections(destination.coords);

        // Show modal
        modal.classList.add('active');
    }

    shareDestination(destination) {
        const shareData = {
            title: destination.name,
            text: `Check out ${destination.name} - ${destination.description}`,
            url: `${window.location.origin}${window.location.pathname.replace('map.html', '')}detail.html?id=${destination.id}`
        };

        if (navigator.share) {
            navigator.share(shareData)
                .then(() => window.showToast('Berhasil dibagikan', 'success'))
                .catch(() => { });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(shareData.url)
                .then(() => window.showToast('Link disalin ke clipboard', 'success'))
                .catch(() => window.showToast('Gagal menyalin link', 'error'));
        }
    }

    getDirections(coords) {
        const url = `https://www.google.com/maps/dir/?api=1&destination=${coords[0]},${coords[1]}`;
        window.open(url, '_blank');
    }

    updateFavoritesCount() {
        const count = this.favorites.length;
        const badge = document.getElementById('favorites-count');
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }

    showFavoritesModal() {
        const modal = document.getElementById('favorites-modal');
        const list = document.getElementById('favorites-list');

        list.innerHTML = '';

        if (this.favorites.length === 0) {
            list.innerHTML = '<div class="no-favorites">Belum ada destinasi favorit</div>';
        } else {
            this.favorites.forEach(id => {
                const destination = (this.tourismData || []).find(d => d.id === id);
                if (destination) {
                    const item = document.createElement('div');
                    item.className = 'favorite-item';
                    item.innerHTML = `
                        <img src="${destination.images[0]}" alt="${destination.name}" class="favorite-image">
                        <div class="favorite-info">
                            <h4><a href="detail.html?id=${destination.id}">${destination.name}</a></h4>
                            <p>${destination.location}</p>
                            <div class="favorite-rating">
                                ${window.createStarRating(destination.rating)}
                                <span>${destination.rating}/5</span>
                            </div>
                        </div>
                    `;
                    item.addEventListener('click', () => {
                        this.map.setView(destination.coords, 15);
                        modal.style.display = 'none';
                    });
                    list.appendChild(item);
                }
            });
        }

        modal.style.display = 'block';
    }

    initializeWeatherWidget() {
        // Initialize with default location (Jakarta)
        this.updateWeather([-6.2088, 106.8456]);
    }

    async updateWeather(coords) {
        const cacheKey = `${coords[0]},${coords[1]}`;
        const now = Date.now();

        // Check cache first
        if (this.weatherCache[cacheKey] && (now - this.weatherCache[cacheKey].timestamp) < this.cacheExpiry) {
            this.displayWeatherData(this.weatherCache[cacheKey].data);
            return;
        }

        // Show loading state
        this.showWeatherLoading();

        try {
            // BMKG API endpoint for weather data
            const response = await fetch(
                `https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${coords[0]},${coords[1]}`
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil data cuaca dari BMKG');
            }

            const data = await response.json();

            // Transform BMKG data to match expected format
            const transformedData = this.transformBMKGData(data);

            // Cache the data
            this.weatherCache[cacheKey] = {
                data: transformedData,
                timestamp: now
            };
            localStorage.setItem('weatherCache', JSON.stringify(this.weatherCache));

            // Clean up expired cache entries
            this.cleanExpiredCache();

            this.displayWeatherData(transformedData);
        } catch (error) {
            console.error('Weather API error:', error);
            this.displayWeatherError(error.message || 'Gagal memuat cuaca');
        }
    }

    showWeatherLoading() {
        document.getElementById('weather-content').innerHTML = `
            <div class="weather-info loading">
                <div class="weather-icon"><i class="fas fa-spinner fa-spin"></i></div>
                <div class="weather-details">
                    <div class="weather-temp">--°C</div>
                    <div class="weather-location">Memuat...</div>
                </div>
            </div>
        `;
    }

    displayWeatherData(data) {
        document.getElementById('weather-content').innerHTML = `
            <div class="weather-info">
                <div class="weather-icon"><i class="fas fa-${this.getWeatherIcon(data.weather[0].main)}"></i></div>
                <div class="weather-details">
                    <div class="weather-temp">${Math.round(data.main.temp)}°C</div>
                    <div class="weather-desc">${data.weather[0].description}</div>
                    <div class="weather-location">${data.name || 'Lokasi tidak diketahui'}</div>
                </div>
            </div>
        `;
    }

    displayWeatherError(message) {
        document.getElementById('weather-content').innerHTML = `
            <div class="weather-info error">
                <div class="weather-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="weather-details">
                    <div class="weather-temp">--°C</div>
                    <div class="weather-location">${message}</div>
                </div>
            </div>
        `;
    }

    cleanExpiredCache() {
        const now = Date.now();
        const validEntries = {};

        Object.keys(this.weatherCache).forEach(key => {
            if ((now - this.weatherCache[key].timestamp) < this.cacheExpiry) {
                validEntries[key] = this.weatherCache[key];
            }
        });

        this.weatherCache = validEntries;
        localStorage.setItem('weatherCache', JSON.stringify(this.weatherCache));
    }

    transformBMKGData(bmkgData) {
        // Transform BMKG API response to match OpenWeatherMap format
        // BMKG data structure: https://data.bmkg.go.id/DataMKG/MEWS/DigitalForecast/DigitalForecast-Indonesia.xml
        // For simplicity, we'll create a mock transformation based on typical BMKG response

        // Assuming bmkgData has a structure like:
        // { data: [{ lokasi: {...}, cuaca: [...] }] }

        if (!bmkgData || !bmkgData.data || bmkgData.data.length === 0) {
            throw new Error('Invalid BMKG data structure');
        }

        const locationData = bmkgData.data[0];
        const weatherData = locationData.cuaca ? locationData.cuaca[0] : {};

        // Map BMKG weather codes to OpenWeatherMap conditions
        const weatherCode = weatherData[0] ? weatherData[0].weather : 'Cerah';
        const conditionMap = {
            'Cerah': 'Clear',
            'Cerah Berawan': 'Clouds',
            'Berawan': 'Clouds',
            'Berawan Tebal': 'Clouds',
            'Hujan Ringan': 'Rain',
            'Hujan Sedang': 'Rain',
            'Hujan Lebat': 'Rain',
            'Hujan Lokal': 'Rain',
            'Hujan Petir': 'Thunderstorm'
        };

        const mainCondition = conditionMap[weatherCode] || 'Clear';

        return {
            name: locationData.lokasi ? locationData.lokasi.nama : 'Lokasi BMKG',
            main: {
                temp: weatherData[0] ? weatherData[0].t : 25 // Default temperature
            },
            weather: [{
                main: mainCondition,
                description: weatherCode || 'Cerah'
            }]
        };
    }

    getWeatherIcon(condition) {
        const icons = {
            'Clear': 'sun',
            'Clouds': 'cloud',
            'Rain': 'cloud-rain',
            'Drizzle': 'cloud-rain',
            'Thunderstorm': 'bolt',
            'Snow': 'snowflake',
            'Mist': 'smog',
            'Fog': 'smog'
        };
        return icons[condition] || 'cloud-sun';
    }

    getCategoryIcon(category) {
        const iconMap = {
            'alam': 'tree',
            'budaya': 'landmark',
            'kuliner': 'utensils',
            'sejarah': 'monument'
        };

        const iconName = iconMap[category] || 'map-marker-alt';

        return L.divIcon({
            className: `category-marker category-${category}`,
            html: `<i class="fas fa-${iconName}"></i>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
        });
    }

    // helper to resolve image path for popup and lists
    resolveImagePath(imageName) {
        if (!imageName) return 'assets/images/placeholder.jpg';
        const s = String(imageName).trim();
        if (/^https?:\/\//i.test(s)) return `api/image-proxy.php?url=${encodeURIComponent(s)}`;
        // If already a relative path (contains a slash) or assets/, use as-is
        if (s.startsWith('assets/') || s.indexOf('/') !== -1) return s;
        // Otherwise assume filename and prepend uploads folder
        return 'uploads/destinations/' + s;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize map when DOM is loaded
let mapInstance;
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('map')) {
        mapInstance = new TourismMap();
    }
});

// Make toggleFavorite available globally for popup buttons
window.mapInstance = mapInstance;
