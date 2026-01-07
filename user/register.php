<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../public/index.html');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Tourism Map</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --dark: #0f172a;
            --dark-light: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Hide Scrollbar */
        ::-webkit-scrollbar {
            display: none;
        }

        * {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .register-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #6366f1;
            padding: 20px;
            position: relative;
            overflow: auto;
        }

        /* Animated gradient orbs */
        .register-page::before,
        .register-page::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.5;
            animation: float 20s infinite ease-in-out;
        }

        .register-page::before {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, transparent 70%);
            top: -100px;
            left: -100px;
        }

        .register-page::after {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.4) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { 
                transform: translate(0, 0);
            }
            33% { 
                transform: translate(30px, -30px);
            }
            66% { 
                transform: translate(-20px, 20px);
            }
        }

        /* Grid Background Effect */
        .grid-background {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
            z-index: 0;
        }

        @keyframes gridMove {
            0% { transform: translateY(0); }
            100% { transform: translateY(50px); }
        }

        .register-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .register-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 35px;
            box-shadow: 
                0 20px 60px rgba(0,0,0,0.2),
                0 0 0 1px rgba(255,255,255,0.1) inset;
            position: relative;
            animation: slideUp 0.6s ease-out;
        }

        .register-box::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 24px;
            padding: 2px;
            background: linear-gradient(135deg, 
                rgba(99, 102, 241, 0.3), 
                rgba(139, 92, 246, 0.3));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .register-box:hover::before {
            opacity: 1;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 38px;
            color: white;
            box-shadow: 
                0 10px 30px rgba(99, 102, 241, 0.4),
                0 0 0 8px rgba(99, 102, 241, 0.1);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { 
                transform: translateY(0);
            }
            50% { 
                transform: translateY(-10px);
            }
        }

        .logo i {
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .register-header h1 {
            color: var(--dark);
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .register-header p {
            color: #64748b;
            font-size: 14px;
            font-weight: 400;
        }

        .register-form {
            animation: fadeIn 0.6s ease 0.3s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--dark);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.2px;
        }

        .form-group label i {
            margin-right: 6px;
            color: var(--primary);
            font-size: 13px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 12px;
            color: var(--dark);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 
                0 0 0 4px rgba(99, 102, 241, 0.1),
                0 8px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .password-input {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 8px;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .toggle-password:hover {
            color: var(--primary);
            background: #f1f5f9;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 8px 20px rgba(99, 102, 241, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 8px;
            position: relative;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 12px 30px rgba(99, 102, 241, 0.4),
                0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 
                0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-primary i {
            margin-right: 8px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 500;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1.5px solid #fca5a5;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1.5px solid #6ee7b7;
        }

        .register-footer {
            margin-top: 28px;
            text-align: center;
        }

        .register-footer p {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .register-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .back-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            background: #f1f5f9;
            margin-top: 8px;
        }

        .back-link:hover {
            background: #e2e8f0;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .back-link i {
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .register-box {
                padding: 35px 28px;
            }

            .register-header h1 {
                font-size: 24px;
            }

            .logo {
                width: 70px;
                height: 70px;
            }

            .logo i {
                font-size: 34px;
            }
        }
    </style>
</head>
<body class="register-page">
    <!-- Grid Background -->
    <div class="grid-background"></div>

    <div class="register-container">
        <div class="register-box" id="registerBox">
            <div class="register-header">
                <div class="logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Daftar Akun</h1>
                <p>Buat akun untuk mengakses fitur Tourism Map</p>
            </div>
            
            <form id="register-form" class="register-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required autocomplete="username" autofocus placeholder="Masukkan username">
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="Masukkan email">
                </div>

                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-id-card"></i>
                        Nama Lengkap (Opsional)
                    </label>
                    <input type="text" id="full_name" name="full_name" autocomplete="name" placeholder="Masukkan nama lengkap">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Minimal 6 karakter">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="eye-icon-password"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Konfirmasi Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder="Ulangi password">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye" id="eye-icon-confirm"></i>
                        </button>
                    </div>
                </div>
                
                <div id="alert-message" class="alert" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary btn-block" id="register-btn">
                    <i class="fas fa-user-plus"></i>
                    Daftar
                </button>
            </form>
            
            <div class="register-footer">
                <p>Sudah punya akun? <a href="../admin/login.php">Login di sini</a></p>
                <a href="../index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const eyeIcon = document.getElementById('eye-icon-' + (fieldId === 'password' ? 'password' : 'confirm'));
            
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

        function showAlert(message, type) {
            const alertBox = document.getElementById('alert-message');
            alertBox.className = `alert alert-${type}`;
            alertBox.textContent = message;
            alertBox.style.display = 'block';
            
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 5000);
        }

        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const registerBtn = document.getElementById('register-btn');
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const fullName = document.getElementById('full_name').value;
            
            // Validate password match
            if (password !== confirmPassword) {
                showAlert('Password dan konfirmasi password tidak cocok', 'error');
                return;
            }
            
            // Show loading
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';
            
            try {
                const response = await fetch('../api/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        username, 
                        email, 
                        password,
                        full_name: fullName 
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Registrasi berhasil! Mengalihkan ke halaman login...', 'success');
                    setTimeout(() => {
                        window.location.href = '../admin/login.php';
                    }, 2000);
                } else {
                    showAlert(result.message || 'Registrasi gagal', 'error');
                    registerBtn.disabled = false;
                    registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> Daftar';
                }
                
            } catch (error) {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan. Silakan coba lagi.', 'error');
                registerBtn.disabled = false;
                registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> Daftar';
            }
        });
    </script>
</body>
</html>
