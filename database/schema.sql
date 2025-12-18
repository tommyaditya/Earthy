-- ====================================
-- TOURISM MAP DATABASE SCHEMA
-- ====================================

-- Create Database
CREATE DATABASE IF NOT EXISTS tourism_map_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tourism_map_db;

-- ====================================
-- Table: destinations
-- Menyimpan data destinasi wisata
-- ====================================
CREATE TABLE IF NOT EXISTS destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    long_description TEXT,
    address TEXT,
    location VARCHAR(255),
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    category VARCHAR(50) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0,
    opening_hours VARCHAR(255),
    ticket_price VARCHAR(100),
    contact VARCHAR(50),
    website VARCHAR(255),
    facilities JSON,
    accessibility JSON,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_rating (rating),
    INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: destination_images
-- Menyimpan gambar destinasi (relasi 1-to-many)
-- ====================================
CREATE TABLE IF NOT EXISTS destination_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    caption VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX idx_destination (destination_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: tags
-- Menyimpan kategori/tag untuk destinasi
-- ====================================
CREATE TABLE IF NOT EXISTS tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    icon VARCHAR(50),
    color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: destination_tags
-- Tabel pivot untuk relasi many-to-many
-- ====================================
CREATE TABLE IF NOT EXISTS destination_tags (
    destination_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (destination_id, tag_id),
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_destination (destination_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: reviews
-- Menyimpan ulasan/review destinasi
-- ====================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(150),
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX idx_destination (destination_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Table: admin_users
-- Menyimpan data admin untuk manage content
-- ====================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role VARCHAR(20) DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- INSERT SAMPLE DATA
-- ====================================

-- Insert Tags
INSERT INTO tags (name, slug, description, icon, color) VALUES
('Alam', 'alam', 'Destinasi wisata alam', 'fa-tree', '#22c55e'),
('Budaya', 'budaya', 'Destinasi wisata budaya', 'fa-landmark', '#f59e0b'),
('Kuliner', 'kuliner', 'Destinasi kuliner', 'fa-utensils', '#ef4444'),
('Sejarah', 'sejarah', 'Destinasi sejarah', 'fa-monument', '#8b5cf6'),
('Pantai', 'pantai', 'Pantai dan laut', 'fa-umbrella-beach', '#06b6d4'),
('Gunung', 'gunung', 'Gunung dan pendakian', 'fa-mountain', '#059669'),
('Museum', 'museum', 'Museum dan galeri', 'fa-building-columns', '#dc2626'),
('Belanja', 'belanja', 'Tempat belanja', 'fa-shopping-bag', '#ec4899');

-- Insert Sample Destinations
INSERT INTO destinations (name, description, long_description, address, location, latitude, longitude, category, rating, opening_hours, ticket_price, facilities, accessibility) VALUES
('Pantai Kuta Bali', 'Pantai terkenal dengan sunset yang indah', 'Pantai Kuta adalah salah satu pantai paling terkenal di Bali dengan pasir putih yang lembut dan ombak yang cocok untuk berselancar. Pantai ini menawarkan pemandangan matahari terbenam yang spektakuler.', 'Jl. Pantai Kuta, Kuta, Badung', 'Kuta, Bali', -8.718521, 115.168671, 'alam', 4.5, '24 Jam', 'Gratis', '["Parkir", "Toilet", "Restoran", "Penyewaan Surfboard"]', '["Wheelchair Accessible", "Parking"]'),
('Borobudur Temple', 'Candi Buddha terbesar di dunia', 'Candi Borobudur adalah candi Buddha terbesar di dunia yang dibangun pada abad ke-9. Situs warisan dunia UNESCO ini memiliki arsitektur yang menakjubkan dengan lebih dari 2.000 relief dan 504 stupa.', 'Jl. Badrawati, Borobudur, Magelang', 'Magelang, Jawa Tengah', -7.607874, 110.203751, 'budaya', 4.8, '06:00 - 17:00', 'Rp 50.000', '["Parkir", "Toilet", "Mushola", "Souvenir Shop", "Guide"]', '["Limited Wheelchair Access", "Parking"]'),
('Raja Ampat', 'Surga bawah laut Indonesia', 'Raja Ampat adalah destinasi diving terbaik di dunia dengan keanekaragaman hayati laut yang luar biasa. Terdiri dari 1.500 pulau kecil dengan pemandangan yang sangat indah.', 'Kepulauan Raja Ampat', 'Papua Barat', -0.239730, 130.518343, 'alam', 5.0, '24 Jam', 'Bervariasi', '["Diving Center", "Homestay", "Boat Rental"]', '["Boat Access Only"]'),
('Malioboro Yogyakarta', 'Pusat belanja dan kuliner Jogja', 'Jalan Malioboro adalah jantung kota Yogyakarta yang menawarkan berbagai oleh-oleh khas, batik, kuliner tradisional, dan atmosfer kota yang unik. Tempat yang wajib dikunjungi saat ke Yogyakarta.', 'Jl. Malioboro', 'Yogyakarta', -7.792931, 110.365165, 'kuliner', 4.3, '24 Jam', 'Gratis', '["Parkir", "ATM", "Toilet", "Restoran"]', '["Wheelchair Accessible", "Parking"]'),
('Taman Mini Indonesia Indah', 'Miniatur kebudayaan Indonesia', 'TMII menampilkan miniatur rumah adat dari 34 provinsi di Indonesia, museum, anjungan daerah, dan berbagai wahana rekreasi yang edukatif untuk keluarga.', 'Jl. Raya TMII, Jakarta Timur', 'Jakarta', -6.302414, 106.895221, 'budaya', 4.2, '07:00 - 22:00', 'Rp 25.000', '["Parkir", "Toilet", "Restoran", "Mushola", "Cable Car"]', '["Wheelchair Accessible", "Parking", "Disabled Toilet"]');

-- Insert Sample Images
INSERT INTO destination_images (destination_id, image_url, caption, is_primary, display_order) VALUES
(1, 'assets/images/kuta-beach-1.jpg', 'Sunset di Pantai Kuta', 1, 1),
(1, 'assets/images/kuta-beach-2.jpg', 'Surfing di Kuta', 0, 2),
(2, 'assets/images/borobudur-1.jpg', 'Candi Borobudur', 1, 1),
(2, 'assets/images/borobudur-2.jpg', 'Relief Borobudur', 0, 2),
(3, 'assets/images/raja-ampat-1.jpg', 'Pulau Raja Ampat', 1, 1),
(3, 'assets/images/raja-ampat-2.jpg', 'Underwater Raja Ampat', 0, 2),
(4, 'assets/images/malioboro-1.jpg', 'Jalan Malioboro', 1, 1),
(5, 'assets/images/tmii-1.jpg', 'Taman Mini Indonesia', 1, 1);

-- Link Destinations with Tags
INSERT INTO destination_tags (destination_id, tag_id) VALUES
(1, 1), (1, 5), -- Kuta: Alam, Pantai
(2, 2), (2, 4), -- Borobudur: Budaya, Sejarah
(3, 1), (3, 5), -- Raja Ampat: Alam, Pantai
(4, 3), (4, 8), -- Malioboro: Kuliner, Belanja
(5, 2), (5, 7); -- TMII: Budaya, Museum

-- Insert Sample Reviews
INSERT INTO reviews (destination_id, user_name, rating, comment) VALUES
(1, 'Ahmad Rizki', 5, 'Pantai yang sangat indah! Sunset-nya luar biasa!'),
(1, 'Siti Nurhaliza', 4, 'Ramai tapi menyenangkan. Cocok untuk surfing.'),
(2, 'Budi Santoso', 5, 'Candi yang megah! Wajib dikunjungi saat ke Jogja.'),
(3, 'Dewi Lestari', 5, 'Surga dunia! Diving terbaik yang pernah saya alami.'),
(4, 'Rudi Hartono', 4, 'Tempat belanja oleh-oleh terlengkap!');

-- Insert Sample Admin User (password: admin123)
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@tourismmap.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
