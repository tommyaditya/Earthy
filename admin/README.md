# 🔐 Panduan Login Panel Admin

## 📍 Akses Panel Admin

### URL Login
```
http://localhost/Maps/admin/login.php
```

### Default Credentials
```
Username: admin
Password: admin123
```

## 🎯 Fitur Panel Admin

### 1. **Dashboard** (admin/index.php)
- Statistik total destinasi
- Statistik total reviews
- Rating rata-rata
- Total kategori
- Daftar destinasi terbaru
- Review terbaru

### 2. **Manage Destinasi** (Akan Dibuat)
- Tambah destinasi baru
- Edit destinasi
- Hapus destinasi
- Upload gambar
- Manage tags/kategori

### 3. **Manage Reviews** (Akan Dibuat)
- Lihat semua review
- Moderasi review
- Hapus review spam

### 4. **Manage Tags/Kategori** (Akan Dibuat)
- Tambah kategori baru
- Edit kategori
- Hapus kategori

## 🔒 Keamanan

### Session Management
- Menggunakan PHP session untuk authentication
- Auto redirect ke login jika belum login
- Logout functionality
- Session timeout

### Password Security
- Password di-hash menggunakan `password_hash()` dengan BCRYPT
- Password verification menggunakan `password_verify()`
- Password minimal 6 karakter

### API Security
- PDO prepared statements untuk prevent SQL injection
- Input validation
- CORS headers untuk development

## 🛠️ Cara Membuat Admin Baru

### Via phpMyAdmin:
```sql
-- Generate password hash dulu
-- Gunakan online tool atau PHP:
-- <?php echo password_hash('password_anda', PASSWORD_DEFAULT); ?>

INSERT INTO admin_users 
(username, email, password_hash, full_name, role) 
VALUES 
('admin_baru', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Baru', 'admin');
```

### Via PHP Script:
```php
<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$username = 'admin_baru';
$email = 'admin@example.com';
$password = 'password123';
$full_name = 'Admin Baru';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO admin_users (username, email, password_hash, full_name, role) 
          VALUES (:username, :email, :password_hash, :full_name, 'admin')";

$stmt = $db->prepare($query);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password_hash', $password_hash);
$stmt->bindParam(':full_name', $full_name);
$stmt->execute();

echo "Admin berhasil ditambahkan!";
?>
```

## 📱 Struktur File Admin

```
admin/
├── login.php          # Halaman login
├── index.php          # Dashboard
├── destinations.php   # Manage destinasi (coming soon)
├── reviews.php        # Manage reviews (coming soon)
├── tags.php           # Manage tags (coming soon)
└── assets/
    └── css/
        └── admin.css  # Styling admin panel
```

## 🚀 Cara Menggunakan

### 1. Login ke Panel Admin
1. Buka browser
2. Akses: `http://localhost/Maps/admin/login.php`
3. Masukkan username: `admin`
4. Masukkan password: `admin123`
5. Klik "Login"

### 2. Dashboard Admin
Setelah login, Anda akan diarahkan ke dashboard yang menampilkan:
- Total destinasi dari database
- Total reviews
- Rating rata-rata semua destinasi
- Total kategori/tags
- 5 destinasi terbaru
- 5 review terbaru

### 3. Navigasi
- Klik menu di sidebar untuk navigasi
- "Lihat Website" membuka website utama di tab baru
- "Logout" untuk keluar dari panel admin

## 🔧 Troubleshooting

### Tidak Bisa Login?
1. Pastikan database sudah di-import
2. Check tabel `admin_users` ada data admin
3. Check password hash benar
4. Check PHP session enabled

### Error "Database connection failed"?
1. Pastikan MySQL di XAMPP sudah running
2. Check config di `config/database.php`
3. Pastikan nama database benar: `tourism_map_db`

### Error "Call to undefined function password_verify()"?
- Update PHP ke versi 5.5 atau lebih baru

### Session Tidak Tersimpan?
1. Check folder session writable
2. Check `session.save_path` di php.ini
3. Restart Apache

## 📊 API Endpoints yang Digunakan

### Authentication
```
POST /api/auth.php
- Login admin

GET /api/auth.php?action=check
- Check session status

GET /api/auth.php?action=logout
- Logout admin
```

### Data Management
```
GET /api/destinations.php
- Get all destinations

GET /api/destination.php?id=1
- Get single destination

GET /api/reviews.php?destination_id=1
- Get reviews for destination

GET /api/tags.php
- Get all tags/categories
```

## 🎨 Customization

### Mengubah Warna Tema
Edit file `admin/assets/css/admin.css`:
```css
:root {
    --primary: #667eea;      /* Warna utama */
    --secondary: #764ba2;    /* Warna sekunder */
    --success: #10b981;      /* Hijau */
    --danger: #ef4444;       /* Merah */
    --warning: #f59e0b;      /* Kuning */
}
```

### Mengubah Logo
Edit `admin/login.php` dan `admin/index.php`:
```html
<div class="logo">
    <img src="path/to/logo.png" alt="Logo">
</div>
```

## 📝 Next Steps

File yang akan dibuat selanjutnya:
- [ ] `admin/destinations.php` - CRUD destinasi lengkap
- [ ] `admin/reviews.php` - Moderasi review
- [ ] `admin/tags.php` - Manage kategori
- [ ] `admin/settings.php` - Settings aplikasi
- [ ] `admin/profile.php` - Edit profile admin

## 💡 Tips

1. **Keamanan Password**: Gunakan password yang kuat untuk production
2. **Backup Database**: Backup rutin database Anda
3. **HTTPS**: Gunakan HTTPS untuk production
4. **Role-Based Access**: Tambahkan level akses (admin, editor, viewer)
5. **Activity Log**: Catat semua aktivitas admin

---

**Happy Managing! 🎉**
