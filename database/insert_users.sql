-- ====================================
-- INSERT DEFAULT USERS
-- ====================================
-- Jalankan query ini di phpMyAdmin
-- ====================================

USE tourism_map_db;

-- Hapus user lama jika ada
DELETE FROM users WHERE username IN ('admin', 'user');

-- Insert 2 akun default
INSERT INTO users (username, email, password, full_name, role, is_active) VALUES
-- AKUN ADMIN
-- Username: admin
-- Password: admin123
('admin', 'admin@tourismmap.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 1),

-- AKUN USER
-- Username: user
-- Password: user123
('user', 'user@tourismmap.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'User Demo', 'user', 1);

-- Verifikasi data
SELECT id, username, email, full_name, role, is_active, created_at FROM users;
