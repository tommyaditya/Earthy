<?php
session_start();

// Check if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $redirectUrl = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? '../admin/index.php' : '../index.html';
    $roleName = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'User';
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sudah Login - Tourism Map</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: #6366f1;
            }

            .card {
                background: white;
                padding: 40px;
                border-radius: 20px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
                max-width: 400px;
                width: 90%;
            }

            h1 {
                color: #0f172a;
                margin-bottom: 10px;
                font-size: 24px;
            }

            p {
                color: #64748b;
                margin-bottom: 30px;
            }

            .btn {
                display: block;
                width: 100%;
                padding: 12px;
                margin-bottom: 10px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600;
                transition: 0.3s;
            }

            .btn-primary {
                background: #6366f1;
                color: white;
            }

            .btn-secondary {
                background: #f1f5f9;
                color: #0f172a;
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .avatar {
                width: 80px;
                height: 80px;
                background: #eef2ff;
                color: #6366f1;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                font-size: 32px;
            }
        </style>
    </head>

    <body>
        <div class="card">
            <div class="avatar"><i class="fas fa-user-check"></i></div>
            <h1>Anda Sudah Login</h1>
            <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong>! Anda sedang aktif
                sebagai <?php echo $roleName; ?>.</p>
            <a href="<?php echo $redirectUrl; ?>" class="btn btn-primary"><i class="fas fa-home"></i> Kembali ke
                Dashboard/Home</a>
            <a href="../api/auth.php?action=logout" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </body>

    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tourism Map</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
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

        .login-page {
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
        .login-page::before,
        .login-page::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.5;
            animation: float 20s infinite ease-in-out;
            z-index: -1;
            /* Low positive index */
        }

        /* ... */

        /* Grid Background Effect */
        .grid-background {
            position: absolute;
            width: 100%;
            height: 100%;
            /* ... */
            z-index: -1;
        }

        .login-container {
            position: relative;
            z-index: 100;
            /* Increased to verify */
            /* ... */
        }

        .login-box {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 35px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            position: relative;
            animation: slideUp 0.6s ease-out;
        }

        .login-box::before {
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

        .login-box:hover::before {
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

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 42px;
            color: white;
            box-shadow:
                0 10px 30px rgba(99, 102, 241, 0.4),
                0 0 0 8px rgba(99, 102, 241, 0.1);
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .logo i {
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .login-header h1 {
            color: var(--dark);
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: #64748b;
            font-size: 15px;
            font-weight: 400;
        }

        .login-form {
            animation: fadeIn 0.6s ease 0.3s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            color: var(--dark);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            letter-spacing: 0.2px;
        }

        .form-group label i {
            margin-right: 8px;
            color: var(--primary);
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 12px;
            color: var(--dark);
            font-size: 15px;
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

        .checkbox-label {
            display: flex;
            align-items: center;
            color: var(--dark);
            font-size: 14px;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        .checkbox-label input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow:
                0 8px 20px rgba(99, 102, 241, 0.3),
                0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
        }

        /* Button shine effect */
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
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
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
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

        .login-footer {
            margin-top: 32px;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .login-footer p {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 16px;
            font-weight: 500;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .back-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            background: #f1f5f9;
            margin-top: 10px;
        }

        .back-link:hover {
            background: #e2e8f0;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-decoration: none !important;
        }

        .back-link i {
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-box {
                padding: 40px 30px;
            }

            .login-header h1 {
                font-size: 26px;
            }

            .logo {
                width: 75px;
                height: 75px;
            }

            .logo i {
                font-size: 36px;
            }

        }
    </style>
</head>

<body class="login-page">
    <!-- Grid Background -->
    <div class="grid-background"></div>

    <div class="login-container">
        <div class="login-box" id="loginBox">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h1>Login Akun</h1>
                <p>Masuk untuk menjelajahi peta wisata</p>
            </div>

            <form id="login-form" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required autocomplete="username" autofocus
                        placeholder="Masukkan username">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            placeholder="Masukkan password">
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
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                <a href="../index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Beranda
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
                const response = await fetch('../api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert('Login berhasil! Mengalihkan...', 'success');
                    setTimeout(() => {
                        window.location.href = result.redirect || '../index.html';
                    }, 1000);
                } else {
                    showAlert(result.message || 'Login gagal', 'error');
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
                }

            } catch (error) {
                console.error('Error:', error);
                showAlert('Gagal terhubung ke server.', 'error');
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
            }
        });
    </script>
</body>

</html>