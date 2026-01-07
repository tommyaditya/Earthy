<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Check if user has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // User is logged in but not an admin, deny access
    session_destroy();
    header('Location: login.php?error=unauthorized');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? $_SESSION['admin_username'];
$admin_email = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Destinasi - Tourism Map Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin-new.css">
</head>
<body class="admin-panel">
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-map-marked-alt"></i>
            <h2>Tourism Map</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item">
                <i class="fas fa-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="destinations.php" class="nav-item active">
                <i class="fas fa-map-marker-alt"></i>
                <span>Destinasi</span>
            </a>
            <a href="reviews.php" class="nav-item">
                <i class="fas fa-star"></i>
                <span>Reviews</span>
            </a>
            <a href="tags.php" class="nav-item">
                <i class="fas fa-tags"></i>
                <span>Tags/Kategori</span>
            </a>
            <a href="../index.html" class="nav-item" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                <span>Lihat Website</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <div>
                    <strong><?php echo htmlspecialchars($admin_name); ?></strong>
                    <small><?php echo htmlspecialchars($admin_email); ?></small>
                </div>
            </div>
            <button onclick="logout()" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="page-header">
            <div>
                <h1>Kelola Destinasi</h1>
                <p>Tambah, edit, dan hapus destinasi wisata</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="showAddModal()">
                    <i class="fas fa-plus"></i>
                    Tambah Destinasi
                </button>
                <button class="btn btn-secondary" onclick="loadDestinations()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </header>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-group">
                <input type="text" id="search-input" placeholder="Cari destinasi..." class="search-input">
            </div>
            <div class="filter-group">
                <select id="category-filter" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <option value="alam">Alam</option>
                    <option value="budaya">Budaya</option>
                    <option value="kuliner">Kuliner</option>
                    <option value="sejarah">Sejarah</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="rating-filter" class="filter-select">
                    <option value="">Semua Rating</option>
                    <option value="5">★★★★★ (5)</option>
                    <option value="4">★★★★ (4+)</option>
                    <option value="3">★★★ (3+)</option>
                </select>
            </div>
        </div>

        <!-- Destinations Table -->
        <div class="content-section">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Rating</th>
                            <th>Featured</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="destinations-table">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination">
            <!-- Pagination will be generated by JavaScript -->
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="destination-modal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="modal-title">Tambah Destinasi</h3>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="destination-form">
                    <input type="hidden" id="destination-id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nama Destinasi *</label>
                            <input type="text" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Kategori *</label>
                            <select id="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="alam">Alam</option>
                                <option value="budaya">Budaya</option>
                                <option value="kuliner">Kuliner</option>
                                <option value="sejarah">Sejarah</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi Singkat * <small>(Short Description)</small></label>
                        <textarea id="description" rows="2" required placeholder="Contoh: Candi Buddha terbesar dan situs UNESCO."></textarea>
                        <small class="form-hint">Deskripsi singkat yang muncul di kartu destinasi</small>
                    </div>

                    <div class="form-group">
                        <label for="long_description">Deskripsi Lengkap <small>(Long Description)</small></label>
                        <textarea id="long_description" rows="4" placeholder="Contoh: Borobudur adalah warisan dunia UNESCO yang menampilkan relief yang kaya..."></textarea>
                        <small class="form-hint">Deskripsi detail yang muncul di halaman detail</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="location">Lokasi * <small>(Location)</small></label>
                            <input type="text" id="location" required placeholder="Contoh: Magelang, Jawa Tengah">
                            <small class="form-hint">Kota, Provinsi</small>
                        </div>
                        <div class="form-group">
                            <label for="address">Alamat Lengkap</label>
                            <input type="text" id="address" placeholder="Contoh: Jl. Raya Borobudur...">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="latitude">Latitude * <small>(Koordinat Y)</small></label>
                            <input type="number" step="any" id="latitude" required placeholder="Contoh: -7.607874">
                            <small class="form-hint">Gunakan Google Maps untuk mendapatkan koordinat</small>
                        </div>
                        <div class="form-group">
                            <label for="longitude">Longitude * <small>(Koordinat X)</small></label>
                            <input type="number" step="any" id="longitude" required placeholder="Contoh: 110.203751">
                            <small class="form-hint">Format: desimal (gunakan titik, bukan koma)</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <input type="number" step="0.1" min="0" max="5" id="rating" value="0">
                        </div>
                        <div class="form-group">
                            <label for="featured">Featured</label>
                            <select id="featured">
                                <option value="0">Tidak</option>
                                <option value="1">Ya</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="opening_hours">Jam Operasional <small>(Hours)</small></label>
                            <input type="text" id="opening_hours" placeholder="Contoh: 06:00 - 17:00 atau Buka 24 jam">
                            <small class="form-hint">Format: HH:MM - HH:MM atau teks bebas</small>
                        </div>
                        <div class="form-group">
                            <label for="ticket_price">Harga Tiket <small>(Price)</small></label>
                            <input type="text" id="ticket_price" placeholder="Contoh: Rp 50.000 - Rp 350.000">
                            <small class="form-hint">Bisa berupa range harga atau teks bebas</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gambar Destinasi <small>(Images)</small></label>
                        <div class="image-upload-section">
                            <div class="upload-methods">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="addImageUpload()">
                                    <i class="fas fa-upload"></i> Upload File
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="addImageUrl()">
                                    <i class="fas fa-link"></i> Tambah URL Gambar
                                </button>
                            </div>
                            <small class="form-hint">Upload file gambar atau masukkan URL gambar dari internet (Unsplash, Pexels, dll)</small>
                            
                            <div id="images-container" class="images-container">
                                <!-- Image inputs will be added here dynamically -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="is_active">
                            <span>Aktif</span>
                        </label>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3>Konfirmasi Hapus</h3>
                <button class="modal-close" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus destinasi ini?</p>
                <p><strong id="delete-destination-name"></strong></p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i>
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let destinations = [];
        let currentEditId = null;
        let currentDeleteId = null;

        async function loadDestinations() {
            try {
                // Add timestamp to prevent caching
                const response = await fetch('../api/destinations.php?t=' + new Date().getTime());
                const result = await response.json();

                console.log('Loaded destinations:', result);

                if (result.success) {
                    destinations = result.data;
                    renderDestinations(destinations);
                } else {
                    showNotification('Gagal memuat data destinasi', 'error');
                }
            } catch (error) {
                console.error('Error loading destinations:', error);
                showNotification('Terjadi kesalahan saat memuat data', 'error');
            }
        }

        function renderDestinations(data) {
            const tbody = document.getElementById('destinations-table');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">Tidak ada destinasi</td></tr>';
                return;
            }

            data.forEach(dest => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>#${dest.id}</td>
                    <td><strong>${dest.name}</strong></td>
                    <td><span class="badge badge-${dest.category}">${dest.category}</span></td>
                    <td>${dest.location}</td>
                    <td><i class="fas fa-star" style="color: #fbbf24;"></i> ${dest.rating}</td>
                    <td>${dest.featured ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>'}</td>
                    <td>
                        <button class="btn-icon" onclick="editDestination(${dest.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="../detail.html?id=${dest.id}" class="btn-icon" title="View" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn-icon btn-danger" onclick="deleteDestination(${dest.id}, '${dest.name}')" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function showAddModal() {
            document.getElementById('modal-title').textContent = 'Tambah Destinasi';
            document.getElementById('destination-form').reset();
            document.getElementById('destination-id').value = '';
            currentEditId = null;
            document.getElementById('destination-modal').style.display = 'flex';
        }

        function editDestination(id) {
            const dest = destinations.find(d => d.id === id);
            if (!dest) return;

            document.getElementById('modal-title').textContent = 'Edit Destinasi';
            document.getElementById('destination-id').value = dest.id;
            document.getElementById('name').value = dest.name;
            document.getElementById('category').value = dest.category;
            document.getElementById('description').value = dest.description || '';
            document.getElementById('long_description').value = dest.long_description || '';
            document.getElementById('location').value = dest.location;
            document.getElementById('address').value = dest.address || '';
            document.getElementById('latitude').value = dest.latitude;
            document.getElementById('longitude').value = dest.longitude;
            document.getElementById('rating').value = dest.rating;
            document.getElementById('featured').value = dest.featured ? '1' : '0';
            document.getElementById('opening_hours').value = dest.opening_hours || '';
            document.getElementById('ticket_price').value = dest.ticket_price || '';

            // Load existing images
            if (dest.images && dest.images.length > 0) {
                const container = document.getElementById('images-container');
                container.innerHTML = '';
                dest.images.forEach((imageUrl, index) => {
                    imageCounter++;
                    const div = document.createElement('div');
                    div.className = 'image-item';
                    div.dataset.id = imageCounter;
                    div.innerHTML = `
                        <div class="image-preview has-image">
                            <img src="${imageUrl}" alt="Image ${index + 1}">
                        </div>
                        <div class="image-inputs">
                            <input type="hidden" name="existing_images[]" value="${imageUrl}">
                            <input type="text" name="image_captions[]" placeholder="Caption (opsional)" class="form-control">
                            <label style="margin-top: 8px;">
                                <input type="checkbox" name="is_primary_${imageCounter}" ${index === 0 ? 'checked' : ''}> Set sebagai gambar utama
                            </label>
                        </div>
                        <button type="button" class="btn-remove-image" onclick="removeImage(${imageCounter})" title="Hapus gambar">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    container.appendChild(div);
                });
            }

            currentEditId = id;
            document.getElementById('destination-modal').style.display = 'flex';
        }

        function deleteDestination(id, name) {
            currentDeleteId = id;
            document.getElementById('delete-destination-name').textContent = name;
            document.getElementById('delete-modal').style.display = 'flex';
        }

        async function confirmDelete() {
            if (!currentDeleteId) return;

            try {
                const response = await fetch(`../api/delete.php?id=${currentDeleteId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Destinasi berhasil dihapus', 'success');
                    closeDeleteModal();
                    loadDestinations();
                } else {
                    showNotification(result.message || 'Gagal menghapus destinasi', 'error');
                }
            } catch (error) {
                console.error('Error deleting destination:', error);
                showNotification('Terjadi kesalahan saat menghapus', 'error');
            }
        }

        document.getElementById('destination-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                name: document.getElementById('name').value,
                category: document.getElementById('category').value,
                description: document.getElementById('description').value,
                long_description: document.getElementById('long_description').value,
                location: document.getElementById('location').value,
                address: document.getElementById('address').value,
                latitude: parseFloat(document.getElementById('latitude').value),
                longitude: parseFloat(document.getElementById('longitude').value),
                rating: parseFloat(document.getElementById('rating').value),
                featured: parseInt(document.getElementById('featured').value),
                opening_hours: document.getElementById('opening_hours').value,
                ticket_price: document.getElementById('ticket_price').value
            };

            // Collect image data
            const imageUrls = [];
            const imageCaptions = [];
            
            // Get existing images
            document.querySelectorAll('input[name="existing_images[]"]').forEach(input => {
                if (input.value) imageUrls.push(input.value);
            });
            
            // Get new image URLs
            document.querySelectorAll('input[name="image_urls[]"]').forEach(input => {
                if (input.value) imageUrls.push(input.value);
            });
            
            // Get captions
            document.querySelectorAll('input[name="image_captions[]"]').forEach(input => {
                imageCaptions.push(input.value || '');
            });
            
            if (imageUrls.length > 0) {
                formData.images = imageUrls;
            }

            console.log('Form Data:', formData);
            console.log('Current Edit ID:', currentEditId);

            try {
                let url = '../api/create.php';
                let method = 'POST';

                if (currentEditId) {
                    url = `../api/update.php?id=${currentEditId}`;
                    method = 'PUT';
                }

                console.log('Sending to:', url, 'Method:', method);

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                console.log('Response Status:', response.status);
                const result = await response.json();
                console.log('Response Result:', result);

                if (result.success) {
                    showNotification(
                        currentEditId ? 'Destinasi berhasil diupdate' : 'Destinasi berhasil ditambahkan',
                        'success'
                    );
                    closeModal();
                    loadDestinations();
                } else {
                    showNotification(result.message || 'Gagal menyimpan destinasi', 'error');
                    console.error('Server error:', result);
                }
            } catch (error) {
                console.error('Error saving destination:', error);
                showNotification('Terjadi kesalahan saat menyimpan', 'error');
            }
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = destinations.filter(dest =>
                dest.name.toLowerCase().includes(query) ||
                dest.location.toLowerCase().includes(query)
            );
            renderDestinations(filtered);
        });

        // Category filter
        document.getElementById('category-filter').addEventListener('change', (e) => {
            const category = e.target.value;
            const filtered = category ? destinations.filter(dest => dest.category === category) : destinations;
            renderDestinations(filtered);
        });

        // Rating filter
        document.getElementById('rating-filter').addEventListener('change', (e) => {
            const rating = parseFloat(e.target.value);
            const filtered = rating ? destinations.filter(dest => dest.rating >= rating) : destinations;
            renderDestinations(filtered);
        });

        // Image Management Functions
        let imageCounter = 0;

        function addImageUpload() {
            const container = document.getElementById('images-container');
            const imageDiv = createImageDiv('upload');
            container.appendChild(imageDiv);
        }

        function addImageUrl() {
            const container = document.getElementById('images-container');
            const imageDiv = createImageDiv('url');
            container.appendChild(imageDiv);
        }

        function createImageDiv(type) {
            imageCounter++;
            const div = document.createElement('div');
            div.className = 'image-item';
            div.dataset.id = imageCounter;

            if (type === 'upload') {
                div.innerHTML = `
                    <div class="image-preview">
                        <i class="fas fa-image"></i>
                        <span>Pilih gambar</span>
                    </div>
                    <div class="image-inputs">
                        <input type="file" name="images[]" accept="image/*" onchange="previewImage(this, ${imageCounter})" class="form-control">
                        <input type="text" name="image_captions[]" placeholder="Caption (opsional)" class="form-control" style="margin-top: 8px;">
                        <label style="margin-top: 8px;">
                            <input type="checkbox" name="is_primary_${imageCounter}"> Set sebagai gambar utama
                        </label>
                    </div>
                    <button type="button" class="btn-remove-image" onclick="removeImage(${imageCounter})" title="Hapus gambar">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            } else {
                div.innerHTML = `
                    <div class="image-preview">
                        <i class="fas fa-link"></i>
                        <span>URL gambar</span>
                    </div>
                    <div class="image-inputs">
                        <input type="url" name="image_urls[]" placeholder="https://example.com/image.jpg" class="form-control" onchange="previewImageUrl(this, ${imageCounter})">
                        <input type="text" name="image_captions[]" placeholder="Caption (opsional)" class="form-control" style="margin-top: 8px;">
                        <label style="margin-top: 8px;">
                            <input type="checkbox" name="is_primary_${imageCounter}"> Set sebagai gambar utama
                        </label>
                    </div>
                    <button type="button" class="btn-remove-image" onclick="removeImage(${imageCounter})" title="Hapus gambar">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            }

            return div;
        }

        function previewImage(input, id) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = input.closest('.image-item').querySelector('.image-preview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        }

        function previewImageUrl(input, id) {
            const url = input.value;
            if (url) {
                const preview = input.closest('.image-item').querySelector('.image-preview');
                preview.innerHTML = `<img src="${url}" alt="Preview" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-exclamation-triangle\\'></i><span>URL tidak valid</span>'">`;
                preview.classList.add('has-image');
            }
        }

        function removeImage(id) {
            const imageItem = document.querySelector(`.image-item[data-id="${id}"]`);
            if (imageItem) {
                imageItem.remove();
            }
        }

        function closeModal() {
            document.getElementById('destination-modal').style.display = 'none';
            // Clear images container
            document.getElementById('images-container').innerHTML = '';
            imageCounter = 0;
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').style.display = 'none';
            currentDeleteId = null;
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => notification.classList.add('show'), 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function logout() {
            if (confirm('Yakin ingin logout?')) {
                fetch('../api/auth.php?action=logout')
                    .then(() => window.location.href = 'login.php')
                    .catch(() => window.location.href = 'login.php');
            }
        }

        // Load destinations on page load
        loadDestinations();
    </script>
    <script src="assets/js/admin-common.js"></script>
</body>
</html>
