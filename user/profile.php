<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: ../admin/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$full_name = $_SESSION['full_name'] ?? '';
$role = $_SESSION['role'];

// Get profile picture from database
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$query = "SELECT profile_picture FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_picture = $userData['profile_picture'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($username); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --dark: #0f172a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
            position: relative;
        }

        .avatar-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
            overflow: hidden;
            position: relative;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar i {
            font-size: 48px;
        }

        .avatar-upload {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            border: 3px solid white;
        }

        .avatar-upload i {
            font-size: 14px;
            color: var(--primary);
        }

        .avatar-upload:hover {
            background: var(--primary);
            transform: scale(1.1);
        }

        .avatar-upload:hover i {
            color: white;
        }

        .avatar-upload input {
            display: none;
        }

        .profile-header h1 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .profile-header p {
            color: #64748b;
            font-size: 16px;
        }

        .badge {
            display: inline-block;
            padding: 6px 16px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section h2 {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item {
            display: flex;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .info-item i {
            width: 40px;
            height: 40px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            color: var(--dark);
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--dark);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .upload-progress {
            display: none;
            margin-top: 10px;
            text-align: center;
        }

        .upload-progress.show {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-card">
            <div id="alert-message" class="alert"></div>

            <div class="profile-header">
                <div class="avatar-wrapper">
                    <div class="avatar" id="avatar-preview">
                        <?php if ($profile_picture && file_exists('../' . $profile_picture)): ?>
                            <img src="../<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <label class="avatar-upload" title="Upload foto profil">
                        <i class="fas fa-camera"></i>
                        <input type="file" id="profile-picture-input" accept="image/*">
                    </label>
                </div>
                <div class="upload-progress" id="upload-progress">
                    <div class="spinner"></div>
                    <p>Uploading...</p>
                </div>
                <h1><?php echo htmlspecialchars($full_name ?: $username); ?></h1>
                <p>@<?php echo htmlspecialchars($username); ?></p>
                <span class="badge">
                    <?php echo $role === 'admin' ? '👑 Administrator' : '👤 User'; ?>
                </span>
            </div>

            <div class="info-section">
                <h2><i class="fas fa-info-circle"></i> Informasi Akun</h2>
                
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <div class="info-content">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div class="info-content">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-id-card"></i>
                    <div class="info-content">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value"><?php echo htmlspecialchars($full_name ?: '-'); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-shield-alt"></i>
                    <div class="info-content">
                        <div class="info-label">Role</div>
                        <div class="info-value"><?php echo ucfirst($role); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-fingerprint"></i>
                    <div class="info-content">
                        <div class="info-label">User ID</div>
                        <div class="info-value">#<?php echo htmlspecialchars($user_id); ?></div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <?php if ($role === 'admin'): ?>
                <a href="../admin/index.php" class="btn">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard Admin
                </a>
                <?php endif; ?>
                <a href="../index.html" class="btn btn-secondary">
                    <i class="fas fa-home"></i>
                    Kembali ke Beranda
                </a>
                <a href="../api/auth.php?action=logout" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('profile-picture-input');
        const avatarPreview = document.getElementById('avatar-preview');
        const uploadProgress = document.getElementById('upload-progress');
        const alertMessage = document.getElementById('alert-message');

        function showAlert(message, type) {
            alertMessage.textContent = message;
            alertMessage.className = `alert alert-${type} show`;
            setTimeout(() => {
                alertMessage.classList.remove('show');
            }, 5000);
        }

        fileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            
            if (!file) return;

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showAlert('Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, dan WEBP.', 'error');
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showAlert('Ukuran file terlalu besar. Maksimal 5MB.', 'error');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = avatarPreview.querySelector('img');
                if (img) {
                    img.src = e.target.result;
                } else {
                    const icon = avatarPreview.querySelector('i');
                    if (icon) icon.remove();
                    const newImg = document.createElement('img');
                    newImg.src = e.target.result;
                    newImg.alt = 'Profile Picture';
                    avatarPreview.insertBefore(newImg, avatarPreview.firstChild);
                }
            };
            reader.readAsDataURL(file);

            // Upload file
            const formData = new FormData();
            formData.append('profile_picture', file);

            uploadProgress.classList.add('show');

            try {
                const response = await fetch('../api/upload_profile.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Foto profil berhasil diupdate!', 'success');
                } else {
                    showAlert(result.message || 'Upload gagal', 'error');
                }
            } catch (error) {
                console.error('Upload error:', error);
                showAlert('Terjadi kesalahan saat upload', 'error');
            } finally {
                uploadProgress.classList.remove('show');
            }
        });
    </script>
</body>
</html>
