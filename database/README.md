# Database Setup Guide

## Langkah-langkah Setup Database

### 1. Buka XAMPP Control Panel
- Start Apache
- Start MySQL

### 2. Buka phpMyAdmin
- Akses: http://localhost/phpmyadmin

### 3. Import Database
Ada 2 cara:

#### Cara 1: Menggunakan phpMyAdmin
1. Klik tab "SQL"
2. Copy paste seluruh isi file `schema.sql`
3. Klik "Go"

#### Cara 2: Menggunakan MySQL Command Line
```bash
mysql -u root -p < schema.sql
```

### 4. Verifikasi Database
Setelah import, pastikan database `tourism_map_db` terbuat dengan 6 tabel:
- ✅ destinations
- ✅ destination_images
- ✅ tags
- ✅ destination_tags
- ✅ reviews
- ✅ admin_users

### 5. Konfigurasi Database Connection
Edit file `config/database.php` jika perlu mengubah:
- Host (default: localhost)
- Database name (default: tourism_map_db)
- Username (default: root)
- Password (default: kosong)

## Struktur Database

### Tabel: destinations
Menyimpan data utama destinasi wisata
- id, name, description, long_description
- address, location, latitude, longitude
- category, rating, opening_hours, ticket_price
- contact, website, facilities, accessibility
- featured, created_at, updated_at

### Tabel: destination_images
Menyimpan gambar destinasi (relasi 1-to-many)
- id, destination_id, image_url
- caption, is_primary, display_order

### Tabel: tags
Menyimpan kategori/tag
- id, name, slug, description, icon, color

### Tabel: destination_tags
Tabel pivot untuk many-to-many relationship
- destination_id, tag_id

### Tabel: reviews
Menyimpan ulasan/review
- id, destination_id, user_name, user_email
- rating, comment, created_at, updated_at

### Tabel: admin_users
Menyimpan data admin
- id, username, email, password_hash
- full_name, role, is_active, last_login

## Sample Data
Database sudah include sample data:
- 5 destinasi wisata
- 8 tags/kategori
- Sample images
- Sample reviews
- 1 admin user (username: admin, password: admin123)

## Testing API
Setelah database setup, test API endpoints:

```bash
# Get all destinations
http://localhost/Maps/api/destinations.php

# Get single destination
http://localhost/Maps/api/destination.php?id=1

# Filter by category
http://localhost/Maps/api/destinations.php?category=alam

# Search
http://localhost/Maps/api/destinations.php?search=Pantai

# Filter by rating
http://localhost/Maps/api/destinations.php?rating=4
```

## Troubleshooting

### Error: Database connection failed
- Pastikan MySQL service running di XAMPP
- Check username/password di `config/database.php`
- Pastikan database `tourism_map_db` sudah dibuat

### Error: Table doesn't exist
- Import ulang file `schema.sql`
- Pastikan semua query di file SQL tereksekusi

### Error: Access denied
- Gunakan user `root` dengan password kosong (default XAMPP)
- Atau buat user baru dengan privileges penuh

## Backup Database

Untuk backup database:
```bash
mysqldump -u root tourism_map_db > backup.sql
```

Untuk restore:
```bash
mysql -u root tourism_map_db < backup.sql
```
