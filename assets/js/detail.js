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

            console.log('Loading destination ID:', this.destinationId);
            const apiUrl = `../api/destination.php?id=${this.destinationId}`;
            console.log('Fetching from:', apiUrl);
            
            const response = await fetch(apiUrl);
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('API result:', result);

            if (!result.success || !result.data) {
                throw new Error('Destination not found');
            }

            this.destinationData = result.data;
            this.renderDestinationDetail();
            this.loadReviews();

        } catch (error) {
            console.error('Error loading destination data:', error);
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

    renderDestinationDetail() {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('error').classList.add('hidden');
        document.getElementById('detail-content').classList.remove('hidden');

        // Set main image
        const mainImage = document.getElementById('main-image');
        if (this.destinationData.primary_image) {
            mainImage.src = 'uploads/destinations/' + this.destinationData.primary_image;
        } else {
            mainImage.src = 'assets/images/placeholder.jpg';
        }
        mainImage.alt = this.destinationData.name;

        // Set hero content
        document.getElementById('hero-title').textContent = this.destinationData.name;
        document.getElementById('hero-category').innerHTML = `
            <i class="fas fa-tag"></i>
            <span>${this.destinationData.category}</span>
        `;

        // Set location, hours, price
        document.getElementById('location-text').textContent = this.destinationData.location;
        document.getElementById('hours-text').textContent = this.destinationData.opening_hours || '-';
        document.getElementById('price-text').textContent = this.destinationData.ticket_price || 'Gratis';

        // Set description
        document.getElementById('description-text').textContent = this.destinationData.long_description || this.destinationData.description;

        // Set rating
        this.renderRating();

        // Render gallery
        this.renderGallery();

        // Render tags
        this.renderTags();

        // Initialize rating and comments
        this.initRatingComments();
    }

    renderGallery() {
        const galleryGrid = document.getElementById('gallery-grid');
        if (!galleryGrid) return;
        
        galleryGrid.innerHTML = '';

        // Get images from API response
        const images = this.destinationData.images || [];
        
        if (images.length === 0) {
            galleryGrid.innerHTML = '<p>Tidak ada galeri foto tersedia</p>';
            return;
        }

        images.forEach((imageObj, index) => {
            // Handle both string URLs and object with image_url property
            const imagePath = typeof imageObj === 'string' ? imageObj : imageObj.image_url;
            const galleryItem = document.createElement('div');
            galleryItem.className = 'gallery-item';
            // Use imagePath directly - it's already a full URL or relative path
            galleryItem.innerHTML = `<img src="${imagePath}" alt="Gallery image ${index + 1}">`;
            galleryItem.addEventListener('click', () => this.openImageModal(index));
            galleryGrid.appendChild(galleryItem);
        });
    }

    openImageModal(index) {
        // Create modal for full-size image view
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
            <div class="modal-content">
                <button class="modal-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
                <img src="${this.destinationData.images[index]}" alt="Full size image">
                <div class="modal-navigation">
                    ${index > 0 ? `<button class="nav-btn prev-btn" onclick="changeImage(${index - 1})"><i class="fas fa-chevron-left"></i></button>` : ''}
                    ${index < this.destinationData.images.length - 1 ? `<button class="nav-btn next-btn" onclick="changeImage(${index + 1})"><i class="fas fa-chevron-right"></i></button>` : ''}
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Add modal styles
        const style = document.createElement('style');
        style.textContent = `
            .image-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
            }
            .modal-content {
                position: relative;
                max-width: 90vw;
                max-height: 90vh;
                z-index: 1001;
            }
            .modal-content img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            .modal-close {
                position: absolute;
                top: -50px;
                right: 0;
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .modal-navigation {
                position: absolute;
                top: 50%;
                left: 0;
                right: 0;
                transform: translateY(-50%);
                display: flex;
                justify-content: space-between;
                padding: 0 20px;
            }
            .nav-btn {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.3s ease;
            }
            .nav-btn:hover {
                background: rgba(255, 255, 255, 0.3);
            }
        `;
        document.head.appendChild(style);
    }

    renderRating() {
        const rating = this.destinationData.rating;
        const starsContainer = document.getElementById('rating-stars');
        const ratingText = document.getElementById('rating-text');

        starsContainer.innerHTML = this.createStarRating(rating);
        ratingText.textContent = `${rating}/5`;
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
        window.location.reload();
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
            const response = await fetch('../api/reviews.php', {
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
            console.log('Submit review response:', result);

            if (result.success) {
                // Clear form
                document.getElementById('comment-input').value = '';
                this.selectedRating = 0;
                this.updateStarDisplay(0, true);

                // Reload comments
                await this.loadComments();

                showNotification('Ulasan berhasil dikirim!');
            } else {
                console.error('API Error:', result.message);
                showNotification('Gagal: ' + (result.message || 'Silakan coba lagi.'));
            }
        } catch (error) {
            console.error('Error submitting review:', error);
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
            const response = await fetch(`../api/reviews.php?destination_id=${this.destinationId}`);
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
            console.error('Error loading reviews:', error);
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
