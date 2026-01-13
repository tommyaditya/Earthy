class DestinationDetail {
    constructor() {
        this.destinationId = null;
        this.destinationData = null;
        this.init();
    }

    init() {
        this.getDestinationId();
        if (this.destinationId) {
            this.loadDestinationData();
        } else {
            this.showError();
        }
    }

    getDestinationId() {
        const urlParams = new URLSearchParams(window.location.search);
        this.destinationId = parseInt(urlParams.get('id'));
    }

    async loadDestinationData() {
        try {
            this.showLoading();

            const apiUrl = `./api/destination.php?id=${this.destinationId}`;

            const response = await fetch(apiUrl);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error('Non-JSON response from server: ' + (text ? text.slice(0, 1000) : '[empty]'));
            }

            const result = await response.json();

            if (!result.success || !result.data) {
                throw new Error('Destination not found');
            }

            // API returns: { success, data: { destination, images, primary_image } }
            // Merge destination fields with images and primary_image
            const dest = result.data.destination || result.data;
            this.destinationData = {
                ...dest,
                images: result.data.images || [],
                primary_image: result.data.primary_image || null
            };

            this.renderDestinationDetail(this.destinationData);
            this.loadReviews();

        } catch (error) {
            this.showError();
        }
    }

    showLoading() {
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('error').classList.add('hidden');
        document.getElementById('detail-content').classList.add('hidden');
    }

    showError() {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('error').classList.remove('hidden');
        document.getElementById('detail-content').classList.add('hidden');
    }

    showContent() {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('error').classList.add('hidden');
        document.getElementById('detail-content').classList.remove('hidden');
    }

    // helper to resolve image path
    resolveImagePath(imageName) {
        if (!imageName) return 'assets/images/placeholder.jpg';
        const trimmed = String(imageName).trim();
        if (/^https?:\/\//i.test(trimmed)) return `api/image-proxy.php?url=${encodeURIComponent(trimmed)}`;
        // treat as relative/path or filename -> prepend uploads folder for simple filenames
        if (trimmed.startsWith('assets/') || trimmed.indexOf('/') !== -1) return trimmed;
        return 'uploads/destinations/' + trimmed;
    }

    renderDestinationDetail(dest = this.destinationData) {
        if (!dest) {
            this.showError();
            return;
        }

        const mainImage = document.getElementById('main-image');
        const gallery = document.getElementById('image-gallery') || document.getElementById('gallery-grid');

        // Handle images
        try {
            const primary = dest.primary_image || (Array.isArray(dest.images) && dest.images.length ? dest.images[0] : null);
            const mainSrc = this.resolveImagePath(primary);

            if (mainImage) {
                mainImage.src = mainSrc;
                mainImage.onerror = function () {
                    this.onerror = null;
                    this.src = 'assets/images/placeholder.jpg';
                };
            }

            const heroEl = document.getElementById('hero');
            if (heroEl) {
                heroEl.style.backgroundImage = `url(${this.resolveImagePath(primary)})`;
            }

            if (gallery) {
                gallery.innerHTML = '';
                const imgs = Array.isArray(dest.images) && dest.images.length > 0 ? dest.images : [];
                if (imgs.length === 0) {
                    // Show placeholder if no images
                    const wrapper = document.createElement('div');
                    wrapper.className = 'gallery-item';
                    const el = document.createElement('img');
                    el.src = 'assets/images/placeholder.jpg';
                    el.alt = 'No image available';
                    wrapper.appendChild(el);
                    gallery.appendChild(wrapper);
                } else {
                    imgs.forEach((img) => {
                        const resolved = this.resolveImagePath(img);
                        const wrapper = document.createElement('div');
                        wrapper.className = 'gallery-item';
                        const el = document.createElement('img');
                        el.src = resolved;
                        el.alt = dest.name || 'destination image';
                        el.onerror = function () { this.src = 'assets/images/placeholder.jpg'; };
                        wrapper.appendChild(el);
                        gallery.appendChild(wrapper);
                    });
                }
            }
        } catch (imgErr) {
            if (mainImage) mainImage.src = 'assets/images/placeholder.jpg';
            if (gallery) gallery.innerHTML = '<img src="assets/images/placeholder.jpg" class="thumb" alt="placeholder">';
        }

        // Set hero content
        try {
            const heroTitle = document.getElementById('hero-title');
            const heroCategory = document.getElementById('hero-category');
            if (heroTitle) heroTitle.textContent = dest.name || 'Destinasi';
            if (heroCategory) {
                heroCategory.innerHTML = `
                    <i class="fas fa-tag"></i>
                    <span>${dest.category || 'Umum'}</span>
                `;
            }
        } catch (e) { }

        // Set location, hours, price
        try {
            const locationText = document.getElementById('location-text');
            const hoursText = document.getElementById('hours-text');
            const priceText = document.getElementById('price-text');
            if (locationText) locationText.textContent = dest.location || '-';
            if (hoursText) hoursText.textContent = dest.opening_hours || '-';
            if (priceText) priceText.textContent = dest.ticket_price || 'Gratis';
        } catch (e) { }

        // Set description
        try {
            const descText = document.getElementById('description-text');
            if (descText) descText.textContent = dest.long_description || dest.description || 'Tidak ada deskripsi.';
        } catch (e) { }

        // Set rating
        try {
            this.renderRating();
        } catch (e) { }

        // Render tags
        try {
            this.renderTags();
        } catch (e) { }

        // Initialize rating and comments
        try {
            this.initRatingComments();
        } catch (e) { }

        // ALWAYS show content (hide loading)
        this.showContent();
    }

    renderRating() {
        const rating = this.destinationData?.rating || 0;
        const starsContainer = document.getElementById('rating-stars');
        const ratingText = document.getElementById('rating-text');

        if (starsContainer) starsContainer.innerHTML = this.createStarRating(rating);
        if (ratingText) ratingText.textContent = `${rating}/5`;
    }

    createStarRating(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

        let starsHtml = '';

        // Full stars
        for (let i = 0; i < fullStars; i++) {
            starsHtml += '<i class="fas fa-star"></i>';
        }

        // Half star
        if (hasHalfStar) {
            starsHtml += '<i class="fas fa-star-half-alt"></i>';
        }

        // Empty stars
        for (let i = 0; i < emptyStars; i++) {
            starsHtml += '<i class="far fa-star"></i>';
        }

        return starsHtml;
    }

    renderTags() {
        const tagsSection = document.getElementById('tags-section');
        const tagsContainer = document.getElementById('tags-container');

        if (this.destinationData.tags && this.destinationData.tags.length > 0) {
            tagsContainer.innerHTML = '';
            this.destinationData.tags.forEach(tag => {
                const tagElement = document.createElement('span');
                tagElement.className = 'tag';
                tagElement.textContent = tag;
                tagsContainer.appendChild(tagElement);
            });
            tagsSection.style.display = 'block';
        } else {
            tagsSection.style.display = 'none';
        }
    }

    retryLoad() {
        this.init();
    }

    initRatingComments() {
        this.loadComments();
        this.initStarRating();
        this.initSubmitReview();
    }

    initStarRating() {
        const stars = document.querySelectorAll('#stars-input i');
        // Initialize selectedRating di constructor atau di sini
        this.selectedRating = 0;

        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                this.selectedRating = index + 1;
                this.updateStarDisplay(this.selectedRating, true);
            });

            star.addEventListener('mouseover', () => {
                this.updateStarDisplay(index + 1, false);
            });

            star.addEventListener('mouseout', () => {
                this.updateStarDisplay(this.selectedRating, false);
            });
        });
    }

    updateStarDisplay(rating, isSelected = true) {
        const stars = document.querySelectorAll('#stars-input i');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('far');
                star.classList.add('fas');
                if (isSelected) star.classList.add('active');
            } else {
                star.classList.remove('fas', 'active');
                star.classList.add('far');
            }
        });
    }

    initSubmitReview() {
        const submitBtn = document.getElementById('submit-review');
        const commentInput = document.getElementById('comment-input');

        submitBtn.addEventListener('click', () => {
            const comment = commentInput.value.trim();
            const rating = this.selectedRating;

            if (rating === 0) {
                showNotification('Silakan pilih rating terlebih dahulu!');
                return;
            }

            if (!comment) {
                showNotification('Silakan tulis ulasan Anda!');
                return;
            }

            this.submitReview(rating, comment);
        });
    }

    async submitReview(rating, comment) {
        try {
            const response = await fetch('./api/reviews.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    destination_id: this.destinationId,
                    user_name: 'Pengguna Anonim',
                    rating: rating,
                    comment: comment
                })
            });

            const result = await response.json();

            if (result.success) {
                // Clear form
                document.getElementById('comment-input').value = '';
                this.selectedRating = 0;
                this.updateStarDisplay(0, true);

                // Reload comments
                await this.loadComments();

                showNotification('Ulasan berhasil dikirim!');
            } else {
                showNotification('Gagal: ' + (result.message || 'Silakan coba lagi.'));
            }
        } catch (error) {
            showNotification('Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    async loadReviews() {
        await this.loadComments();
    }

    async loadComments() {
        const commentsList = document.getElementById('comments-list');
        if (!commentsList) return;

        try {
            // Load reviews from API
            const response = await fetch(`./api/reviews.php?destination_id=${this.destinationId}`);
            const result = await response.json();

            if (!result.success || !result.data || result.data.length === 0) {
                commentsList.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">Belum ada ulasan. Jadilah yang pertama memberikan ulasan!</p>';
                return;
            }

            const reviews = result.data;
            commentsList.innerHTML = '';

            reviews.forEach(review => {
                const commentItem = document.createElement('div');
                commentItem.className = 'comment-item';
                commentItem.innerHTML = `
                    <div class="comment-header">
                        <span class="comment-author">${review.visitor_name || review.user_name}</span>
                        <div class="comment-rating">
                            <div class="stars">${this.createStarRating(review.rating)}</div>
                            <span class="rating-value">${review.rating}/5</span>
                        </div>
                    </div>
                    <span class="comment-date">${new Date(review.created_at).toLocaleDateString('id-ID')}</span>
                    <p class="comment-text">${review.comment}</p>
                `;
                commentsList.appendChild(commentItem);
            });
        } catch (error) {
            commentsList.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">Belum ada ulasan. Jadilah yang pertama memberikan ulasan!</p>';
        }
    }
}

// Utility functions
function goBack() {
    // Cek apakah ada history
    if (window.history.length > 1 && document.referrer !== '') {
        window.history.back();
    } else {
        // Jika tidak ada history, redirect ke beranda
        window.location.href = 'index.html';  // Tetap relatif karena sama-sama di public/
    }
}

function retryLoad() {
    window.location.reload();
}

function shareDestination() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: 'Lihat destinasi wisata ini!',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('Link berhasil disalin!');
        });
    }
}

function toggleFavorite() {
    const btn = document.querySelector('.action-btn:nth-child(2) i');
    const isFavorited = btn.classList.contains('fas');

    if (isFavorited) {
        btn.classList.remove('fas');
        btn.classList.add('far');
        showNotification('Dihapus dari favorit');
    } else {
        btn.classList.remove('far');
        btn.classList.add('fas');
        showNotification('Ditambahkan ke favorit');
    }

    // Here you would typically save to localStorage or send to server
    const destinationId = new URLSearchParams(window.location.search).get('id');
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');

    if (isFavorited) {
        const index = favorites.indexOf(destinationId);
        if (index > -1) favorites.splice(index, 1);
    } else {
        if (!favorites.includes(destinationId)) favorites.push(destinationId);
    }

    localStorage.setItem('favorites', JSON.stringify(favorites));
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add notification animations to CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.destinationDetail = new DestinationDetail();

    // Load favorite state
    const destinationId = new URLSearchParams(window.location.search).get('id');
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const btn = document.querySelector('.action-btn:nth-child(2) i');

    if (favorites.includes(destinationId)) {
        btn.classList.remove('far');
        btn.classList.add('fas');
    }
});
