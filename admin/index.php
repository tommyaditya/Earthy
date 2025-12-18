<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
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
    <title>Dashboard Admin - Tourism Map</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-panel">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-map-marked-alt"></i>
            <h2>Tourism Map</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item active">
                <i class="fas fa-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="destinations.php" class="nav-item">
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
                <h1>Dashboard</h1>
                <p>Selamat datang kembali, <?php echo htmlspecialchars($admin_name); ?>!</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="total-destinations">-</h3>
                    <p>Total Destinasi</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3 id="total-reviews">-</h3>
                    <p>Total Reviews</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3 id="avg-rating">-</h3>
                    <p>Rating Rata-rata</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-info">
                    <h3 id="total-tags">-</h3>
                    <p>Total Kategori</p>
                </div>
            </div>
        </div>

        <!-- Recent Destinations -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Destinasi Terbaru</h2>
                <a href="destinations.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Destinasi
                </a>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Rating</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="recent-destinations">
                        <tr>
                            <td colspan="7" style="text-align: center;">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="content-section">
            <div class="section-header">
                <h2><i class="fas fa-comments"></i> Review Terbaru</h2>
                <a href="reviews.php" class="btn btn-secondary">Lihat Semua</a>
            </div>
            
            <div class="reviews-list" id="recent-reviews">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </main>

    <script>
        async function loadDashboardData() {
            try {
                // Load destinations
                const destResponse = await fetch('../api/destinations.php');
                const destResult = await destResponse.json();
                
                if (destResult.success) {
                    const destinations = destResult.data;
                    
                    // Update stats
                    document.getElementById('total-destinations').textContent = destinations.length;
                    
                    // Calculate average rating
                    const avgRating = destinations.reduce((sum, d) => sum + parseFloat(d.rating), 0) / destinations.length;
                    document.getElementById('avg-rating').textContent = avgRating.toFixed(1);
                    
                    // Load recent destinations (last 5)
                    const recent = destinations.slice(0, 5);
                    const tbody = document.getElementById('recent-destinations');
                    tbody.innerHTML = '';
                    
                    recent.forEach(dest => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>#${dest.id}</td>
                            <td><strong>${dest.name}</strong></td>
                            <td><span class="badge badge-${dest.category}">${dest.category}</span></td>
                            <td><i class="fas fa-star" style="color: #fbbf24;"></i> ${dest.rating}</td>
                            <td>${dest.location}</td>
                            <td>${new Date().toLocaleDateString('id-ID')}</td>
                            <td>
                                <a href="destinations.php?edit=${dest.id}" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../detail.html?id=${dest.id}" class="btn-icon" title="View" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
                
                // Load tags count
                const tagsResponse = await fetch('../api/tags.php');
                const tagsResult = await tagsResponse.json();
                if (tagsResult.success) {
                    document.getElementById('total-tags').textContent = tagsResult.data.length;
                }
                
                // Load reviews
                loadRecentReviews();
                
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        async function loadRecentReviews() {
            try {
                // Get all destinations first to get total reviews
                const destResponse = await fetch('../api/destinations.php');
                const destResult = await destResponse.json();
                
                let allReviews = [];
                let totalReviews = 0;
                
                if (destResult.success) {
                    for (const dest of destResult.data.slice(0, 5)) {
                        const reviewResponse = await fetch(`../api/reviews.php?destination_id=${dest.id}`);
                        const reviewResult = await reviewResponse.json();
                        
                        if (reviewResult.success && reviewResult.data.length > 0) {
                            totalReviews += reviewResult.data.length;
                            reviewResult.data.forEach(review => {
                                allReviews.push({
                                    ...review,
                                    destination_name: dest.name
                                });
                            });
                        }
                    }
                }
                
                document.getElementById('total-reviews').textContent = totalReviews;
                
                // Sort by date and take last 5
                allReviews.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                allReviews = allReviews.slice(0, 5);
                
                const container = document.getElementById('recent-reviews');
                container.innerHTML = '';
                
                if (allReviews.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #999;">Belum ada review</p>';
                    return;
                }
                
                allReviews.forEach(review => {
                    const div = document.createElement('div');
                    div.className = 'review-card';
                    div.innerHTML = `
                        <div class="review-header">
                            <div>
                                <strong>${review.user_name}</strong>
                                <small>pada <a href="../detail.html?id=${review.destination_id}">${review.destination_name}</a></small>
                            </div>
                            <div class="review-rating">
                                ${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}
                            </div>
                        </div>
                        <p>${review.comment || 'Tidak ada komentar'}</p>
                        <small class="review-date">${new Date(review.created_at).toLocaleDateString('id-ID', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}</small>
                    `;
                    container.appendChild(div);
                });
                
            } catch (error) {
                console.error('Error loading reviews:', error);
            }
        }

        function logout() {
            if (confirm('Yakin ingin logout?')) {
                fetch('../api/auth.php?action=logout')
                    .then(() => {
                        window.location.href = 'login.php';
                    })
                    .catch(() => {
                        window.location.href = 'login.php';
                    });
            }
        }

        // Load data on page load
        loadDashboardData();
    </script>
</body>
</html>
