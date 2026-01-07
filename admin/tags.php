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
    <title>Kelola Tags - Tourism Map Admin</title>
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
            <a href="reviews.php" class="nav-item">
                <i class="fas fa-star"></i>
                <span>Reviews</span>
            </a>
            <a href="tags.php" class="nav-item active">
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
                <h1>Kelola Tags & Kategori</h1>
                <p>Tambah dan kelola tags untuk destinasi wisata</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="showAddModal()">
                    <i class="fas fa-plus"></i>
                    Tambah Tag
                </button>
                <button class="btn btn-secondary" onclick="loadTags()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </header>

        <!-- Stats Card -->
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-info">
                    <h3 id="total-tags">0</h3>
                    <p>Total Tags</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-link"></i>
                </div>
                <div class="stat-info">
                    <h3 id="tags-in-use">0</h3>
                    <p>Tags Terpakai</p>
                </div>
            </div>
        </div>

        <!-- Tags Grid -->
        <div class="content-section">
            <div class="tags-grid" id="tags-grid">
                <div style="text-align: center; padding: 40px; grid-column: 1 / -1;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Tag Modal -->
    <div id="tag-modal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3 id="modal-title">Tambah Tag</h3>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="tag-form">
                    <input type="hidden" id="tag-id">
                    
                    <div class="form-group">
                        <label for="tag-name">Nama Tag *</label>
                        <input type="text" id="tag-name" required placeholder="Contoh: Pantai Indah">
                    </div>

                    <div class="form-group">
                        <label for="tag-slug">Slug *</label>
                        <input type="text" id="tag-slug" required placeholder="Contoh: pantai-indah">
                        <small>Otomatis dibuat dari nama, atau isi manual</small>
                    </div>

                    <div class="form-group">
                        <label for="tag-description">Deskripsi</label>
                        <textarea id="tag-description" rows="3" placeholder="Deskripsi tag (opsional)"></textarea>
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
                <p>Apakah Anda yakin ingin menghapus tag ini?</p>
                <p><strong id="delete-tag-name"></strong></p>
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
        .tags-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .tag-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .tag-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .tag-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .tag-card h3 {
            font-size: 18px;
            margin: 0 0 8px 0;
            color: var(--dark);
        }

        .tag-slug {
            display: inline-block;
            background: #f3f4f6;
            color: #6b7280;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-family: monospace;
            margin-bottom: 12px;
        }

        .tag-description {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 16px;
        }

        .tag-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }

        .tag-usage {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 500;
        }

        .tag-actions {
            display: flex;
            gap: 8px;
        }

        .color-badge {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: inline-block;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>

    <script>
        let tags = [];
        let currentEditId = null;
        let currentDeleteId = null;

        async function loadTags() {
            try {
                const response = await fetch('../api/tags.php');
                const result = await response.json();

                if (result.success) {
                    tags = result.data;
                    document.getElementById('total-tags').textContent = tags.length;
                    
                    // Count tags in use (you would need to check destination_tags table)
                    document.getElementById('tags-in-use').textContent = tags.length;
                    
                    renderTags(tags);
                } else {
                    showNotification('Gagal memuat data tags', 'error');
                }
            } catch (error) {
                console.error('Error loading tags:', error);
                showNotification('Terjadi kesalahan saat memuat data', 'error');
            }
        }

        function renderTags(data) {
            const grid = document.getElementById('tags-grid');
            grid.innerHTML = '';

            if (data.length === 0) {
                grid.innerHTML = '<div style="text-align: center; padding: 40px; grid-column: 1 / -1; color: #6b7280;">Tidak ada tags</div>';
                return;
            }

            data.forEach(tag => {
                const card = document.createElement('div');
                card.className = 'tag-card';
                
                card.innerHTML = `
                    <div class="tag-card-header">
                        <div style="flex: 1;">
                            <h3>${tag.name}</h3>
                            <span class="tag-slug">${tag.slug}</span>
                        </div>
                    </div>
                    
                    ${tag.description ? `<p class="tag-description">${tag.description}</p>` : ''}
                    
                    <div class="tag-card-footer">
                        <span class="tag-usage">
                            <i class="fas fa-link"></i>
                            <span>ID: ${tag.id}</span>
                        </span>
                        <div class="tag-actions">
                            <button class="btn-icon" onclick="editTag(${tag.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon btn-danger" onclick="deleteTag(${tag.id}, '${tag.name}')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                grid.appendChild(card);
            });
        }

        function showAddModal() {
            document.getElementById('modal-title').textContent = 'Tambah Tag';
            document.getElementById('tag-form').reset();
            document.getElementById('tag-id').value = '';
            currentEditId = null;
            document.getElementById('tag-modal').style.display = 'flex';
        }

        function editTag(id) {
            const tag = tags.find(t => t.id === id);
            if (!tag) return;

            document.getElementById('modal-title').textContent = 'Edit Tag';
            document.getElementById('tag-id').value = tag.id;
            document.getElementById('tag-name').value = tag.name;
            document.getElementById('tag-slug').value = tag.slug;
            document.getElementById('tag-description').value = tag.description || '';

            currentEditId = id;
            document.getElementById('tag-modal').style.display = 'flex';
        }

        function deleteTag(id, name) {
            currentDeleteId = id;
            document.getElementById('delete-tag-name').textContent = name;
            document.getElementById('delete-modal').style.display = 'flex';
        }

        async function confirmDelete() {
            if (!currentDeleteId) return;

            // Note: This requires a delete endpoint in the API
            showNotification('Fitur hapus tag akan segera tersedia', 'info');
            closeDeleteModal();
            
            // TODO: Implement delete endpoint for tags
        }

        // Auto-generate slug from name
        document.getElementById('tag-name').addEventListener('input', (e) => {
            if (!currentEditId) {
                const slug = e.target.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                document.getElementById('tag-slug').value = slug;
            }
        });

        document.getElementById('tag-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                name: document.getElementById('tag-name').value,
                slug: document.getElementById('tag-slug').value,
                description: document.getElementById('tag-description').value
            };

            // Note: This requires create/update endpoints for tags in the API
            showNotification('Fitur tambah/edit tag akan segera tersedia', 'info');
            closeModal();
            
            // TODO: Implement create/update endpoints
            /*
            try {
                let url = '../api/tags.php';
                let method = 'POST';

                if (currentEditId) {
                    url = `../api/tags.php?id=${currentEditId}`;
                    method = 'PUT';
                }

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification(
                        currentEditId ? 'Tag berhasil diupdate' : 'Tag berhasil ditambahkan',
                        'success'
                    );
                    closeModal();
                    loadTags();
                } else {
                    showNotification(result.message || 'Gagal menyimpan tag', 'error');
                }
            } catch (error) {
                console.error('Error saving tag:', error);
                showNotification('Terjadi kesalahan saat menyimpan', 'error');
            }
            */
        });

        function closeModal() {
            document.getElementById('tag-modal').style.display = 'none';
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

        // Load tags on page load
        loadTags();
    </script>
    <script src="assets/js/admin-common.js"></script>
</body>
</html>
