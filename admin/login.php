<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Tourism Map</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h1>Tourism Map Admin</h1>
                <p>Silakan login untuk mengelola destinasi wisata</p>
            </div>
            
            <form id="login-form" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required autocomplete="username" autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>
                </div>
                
                <div id="alert-message" class="alert" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary btn-block" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>
            
            <div class="login-footer">
                <p><i class="fas fa-info-circle"></i> Default: <strong>admin</strong> / <strong>admin123</strong></p>
                <a href="../index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Website
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function showAlert(message, type = 'error') {
            const alert = document.getElementById('alert-message');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alert.style.display = 'block';
            
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const loginBtn = document.getElementById('login-btn');
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Show loading
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            
            try {
                console.log('Attempting login with:', { username }); // Debug log
                
                const response = await fetch('../api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });
                
                console.log('Response status:', response.status); // Debug log
                
                const result = await response.json();
                console.log('Response data:', result); // Debug log
                
                if (result.success) {
                    showAlert('Login berhasil! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showAlert(result.message || 'Login gagal', 'error');
                    console.error('Login failed:', result); // Debug log
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
                }
                
            } catch (error) {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan. Silakan coba lagi. Check console for details.', 'error');
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
            }
        });
        
        // Add link to troubleshooting page
        window.addEventListener('DOMContentLoaded', () => {
            const footer = document.querySelector('.login-footer');
            const troubleshootLink = document.createElement('p');
            troubleshootLink.innerHTML = '<a href="../check_database.php" style="color: #667eea;">🔍 Troubleshoot Login Issues</a>';
            footer.appendChild(troubleshootLink);
        });
    </script>
</body>
</html>
