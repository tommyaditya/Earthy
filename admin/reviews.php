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
    <title>Kelola Reviews - Tourism Map Admin</title>
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
            <a href="destinations.php" class="nav-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Destinasi</span>
            </a>
            <a href="reviews.php" class="nav-item active">
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
                <h1>Kelola Reviews</h1>
                <p>Moderasi dan kelola ulasan pengguna</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="loadReviews()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <h3 id="total-reviews">0</h3>
                    <p>Total Reviews</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fbbf24 0%, #fb923c 100%);">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3 id="avg-rating">0.0</h3>
                    <p>Rating Rata-rata</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #22c55e 0%, #10b981 100%);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="positive-reviews">0</h3>
                    <p>Reviews Positif (4+)</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-group">
                <input type="text" id="search-input" placeholder="Cari review..." class="search-input">
            </div>
            <div class="filter-group">
                <select id="destination-filter" class="filter-select">
                    <option value="">Semua Destinasi</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="rating-filter" class="filter-select">
                    <option value="">Semua Rating</option>
                    <option value="5">★★★★★ (5)</option>
                    <option value="4">★★★★ (4)</option>
                    <option value="3">★★★ (3)</option>
                    <option value="2">★★ (2)</option>
                    <option value="1">★ (1)</option>
                </select>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="content-section">
            <div class="reviews-grid" id="reviews-list">
                <div style="text-align: center; padding: 40px; grid-column: 1 / -1;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </main>

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
                <p>Apakah Anda yakin ingin menghapus review ini?</p>
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

    <style>
        .reviews-grid {
            display: grid;
            gap: 20px;
        }

        .review-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .review-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .user-info small {
            color: #6b7280;
            font-size: 12px;
        }

        .review-rating {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .review-stars {
            color: #fbbf24;
            font-size: 14px;
        }

        .review-destination {
            display: inline-block;
            background: #f3f4f6;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 12px;
            color: #374151;
        }

        .review-comment {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .review-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }

        .review-date {
            color: #9ca3af;
            font-size: 13px;
        }

        .review-actions {
            display: flex;
            gap: 8px;
        }
    </style>

    <script>
        let allReviews = [];
        let destinations = [];
        let currentDeleteId = null;

        async function loadDestinations() {
            try {
                const response = await fetch('../api/destinations.php');
                const result = await response.json();
                
                if (result.success) {
                    destinations = result.data;
                    
                    // Populate destination filter
                    const filter = document.getElementById('destination-filter');
                    filter.innerHTML = '<option value="">Semua Destinasi</option>';
                    destinations.forEach(dest => {
                        filter.innerHTML += `<option value="${dest.id}">${dest.name}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error loading destinations:', error);
            }
        }

        async function loadReviews() {
            try {
                allReviews = [];
                
                // Load reviews for each destination
                for (const dest of destinations) {
                    const response = await fetch(`../api/reviews.php?destination_id=${dest.id}`);
                    const result = await response.json();
                    
                    if (result.success && result.data.length > 0) {
                        result.data.forEach(review => {
                            allReviews.push({
                                ...review,
                                destination_id: dest.id,
                                destination_name: dest.name
                            });
                        });
                    }
                }

                // Sort by date (newest first)
                allReviews.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                updateStats();
                renderReviews(allReviews);
            } catch (error) {
                console.error('Error loading reviews:', error);
                showNotification('Terjadi kesalahan saat memuat reviews', 'error');
            }
        }

        function updateStats() {
            document.getElementById('total-reviews').textContent = allReviews.length;
            
            if (allReviews.length > 0) {
                const avgRating = allReviews.reduce((sum, r) => sum + parseInt(r.rating), 0) / allReviews.length;
                document.getElementById('avg-rating').textContent = avgRating.toFixed(1);
                
                const positiveReviews = allReviews.filter(r => parseInt(r.rating) >= 4).length;
                document.getElementById('positive-reviews').textContent = positiveReviews;
            } else {
                document.getElementById('avg-rating').textContent = '0.0';
                document.getElementById('positive-reviews').textContent = '0';
            }
        }

        function renderReviews(reviews) {
            const container = document.getElementById('reviews-list');
            
            if (reviews.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 40px; grid-column: 1 / -1; color: #6b7280;">Tidak ada review</div>';
                return;
            }

            container.innerHTML = '';
            reviews.forEach(review => {
                const card = document.createElement('div');
                card.className = 'review-card';
                
                const stars = '★'.repeat(parseInt(review.rating)) + '☆'.repeat(5 - parseInt(review.rating));
                const initials = review.user_name.split(' ').map(n => n[0]).join('').toUpperCase();
                const date = new Date(review.created_at).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                card.innerHTML = `
                    <div class="review-header">
                        <div class="review-user">
                            <div class="user-avatar">${initials}</div>
                            <div class="user-info">
                                <h4>${review.user_name}</h4>
                                <small>${review.user_email || 'Email tidak tersedia'}</small>
                            </div>
                        </div>
                        <div class="review-rating">
                            <span class="review-stars">${stars}</span>
                            <strong>${review.rating}/5</strong>
                        </div>
                    </div>
                    
                    <span class="review-destination">
                        <i class="fas fa-map-marker-alt"></i>
                        ${review.destination_name}
                    </span>
                    
                    <p class="review-comment">${review.comment || 'Tidak ada komentar'}</p>
                    
                    <div class="review-footer">
                        <span class="review-date">
                            <i class="fas fa-clock"></i> ${date}
                        </span>
                        <div class="review-actions">
                            <a href="../detail.html?id=${review.destination_id}" class="btn-icon" title="Lihat Destinasi" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <button class="btn-icon btn-danger" onclick="deleteReview(${review.id})" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                container.appendChild(card);
            });
        }

        // Search functionality
        document.getElementById('search-input').addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = allReviews.filter(review =>
                review.user_name.toLowerCase().includes(query) ||
                review.comment?.toLowerCase().includes(query) ||
                review.destination_name.toLowerCase().includes(query)
            );
            renderReviews(filtered);
        });

        // Destination filter
        document.getElementById('destination-filter').addEventListener('change', (e) => {
            const destId = e.target.value;
            const filtered = destId ? allReviews.filter(r => r.destination_id == destId) : allReviews;
            renderReviews(filtered);
        });

        // Rating filter
        document.getElementById('rating-filter').addEventListener('change', (e) => {
            const rating = e.target.value;
            const filtered = rating ? allReviews.filter(r => r.rating == rating) : allReviews;
            renderReviews(filtered);
        });

        function deleteReview(id) {
            currentDeleteId = id;
            document.getElementById('delete-modal').style.display = 'flex';
        }

        async function confirmDelete() {
            if (!currentDeleteId) return;

            // Note: This requires a delete endpoint in the API
            // For now, we'll show a message
            showNotification('Fitur hapus review akan segera tersedia', 'info');
            closeDeleteModal();
            
            // TODO: Implement delete endpoint
            // const response = await fetch(`../api/reviews.php?id=${currentDeleteId}`, {
            //     method: 'DELETE'
            // });
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

        // Load data on page load
        async function init() {
            await loadDestinations();
            await loadReviews();
        }

        init();
    </script>
    <script src="assets/js/admin-common.js"></script>
</body>
</html>
