-- MyWisata Application - Seed Data
-- Database: mywisata
-- This file contains initial data for the application
-- Created: 2026-06-30

-- ============================================
-- ADMIN USER
-- ============================================
-- Default admin user
-- Email: admin@mywisata.com
-- Password: admin123 (bcrypt hash)
INSERT INTO users (name, email, password, role, status, email_verified)
VALUES ('Administrator', 'admin@mywisata.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', 'active', 1);

-- Admin profile
INSERT INTO user_profiles (user_id, first_name, last_name, city, province, country)
VALUES (1, 'System', 'Administrator', 'Jakarta', 'DKI Jakarta', 'Indonesia');

-- ============================================
-- DESTINATION CATEGORIES
-- ============================================
INSERT INTO destination_categories (name, slug, icon, description) VALUES
('Alam', 'alam', 'fa-mountain', 'Destinasi wisata alam seperti gunung, pantai, dan air terjun'),
('Budaya', 'budaya', 'fa-landmark', 'Destinasi wisata budaya dan tradisional'),
('Sejarah', 'sejarah', 'fa-monument', 'Monumen dan situs bersejarah'),
('Pantai', 'pantai', 'fa-water', 'Wisata pantai dan laut'),
('Gunung', 'gunung', 'fa-mountain', 'Wisata pendakian gunung'),
('Taman Nasional', 'taman-nasional', 'fa-tree', 'Taman nasional dan konservasi alam'),
('Museum', 'museum', 'fa-building', 'Museum dan galeri seni'),
('Kuliner', 'kuliner', 'fa-utensils', 'Wisata kuliner lokal'),
('Religi', 'religi', 'fa-place-of-worship', 'Tempat ibadah dan wisata religi'),
('Hiburan', 'hiburan', 'fa-ticket-alt', 'Tempat hiburan dan rekreasi');

-- ============================================
-- SAMPLE DESTINATIONS
-- ============================================
INSERT INTO destinations (category_id, name, slug, description, short_desc, address, city, province, latitude, longitude, entry_fee, opening_time, closing_time, is_active, is_featured) VALUES
(1, 'Borobudur Temple', 'borobudur-temple', 'Candi Buddha terbesar di dunia, situs warisan UNESCO.', 'Candi Buddha terbesar di dunia', 'Jl. Badrawati, Borobudur', 'Magelang', 'Jawa Tengah', -7.6079, 110.2038, 50000, '06:00:00', '17:00:00', 1, 1),
(1, 'Prambanan Temple', 'prambanan-temple', 'Candi Hindu terbesar di Indonesia, situs warisan UNESCO.', 'Candi Hindu terbesar di Indonesia', 'Jl. Raya Solo-Prambanan', 'Sleman', 'DI Yogyakarta', -7.7520, 110.4910, 50000, '06:00:00', '17:00:00', 1, 1),
(4, 'Kuta Beach', 'kuta-beach', 'Pantai terkenal di Bali dengan matahari terbenam yang indah.', 'Pantai terkenal di Bali', 'Jl. Pantai Kuta', 'Kuta', 'Bali', -8.7185, 115.1686, 0, '00:00:00', '23:59:59', 1, 1),
(5, 'Mount Rinjani', 'mount-rinjani', 'Gunung berapi kedua tertinggi di Indonesia.', 'Gunung berapi kedua tertinggi', 'Taman Nasional Gunung Rinjani', 'Lombok Utara', 'Nusa Tenggara Barat', -8.4167, 116.4500, 150000, '06:00:00', '18:00:00', 1, 1),
(6, 'Komodo National Park', 'komodo-national-park', 'Taman nasional dengan habitat komodo asli.', 'Habitat komodo asli', 'Labuan Bajo', 'Manggarai Barat', 'Nusa Tenggara Timur', -8.5500, 119.8833, 100000, '06:00:00', '18:00:00', 1, 1),
(7, 'National Museum', 'national-museum', 'Museum terbesar di Indonesia dengan koleksi bersejarah.', 'Museum terbesar di Indonesia', 'Jl. Medan Merdeka Barat No.12', 'Jakarta Pusat', 'DKI Jakarta', -6.1754, 106.8272, 10000, '08:00:00', '16:00:00', 1, 0),
(2, 'Ubud Art Market', 'ubud-art-market', 'Pasar seni tradisional di Ubud, Bali.', 'Pasar seni tradisional', 'Jl. Raya Ubud', 'Ubud', 'Bali', -8.5069, 115.2625, 0, '08:00:00', '18:00:00', 1, 0),
(9, 'Istiqlal Mosque', 'istiqlal-mosque', 'Masjid terbesar di Asia Tenggara.', 'Masjid terbesar di Asia Tenggara', 'Jl. Taman Wijaya Kusuma', 'Jakarta Pusat', 'DKI Jakarta', -6.1754, 106.8272, 0, '04:00:00', '21:00:00', 1, 0);

-- ============================================
-- SAMPLE TICKETS
-- ============================================
INSERT INTO tickets (destination_id, ticket_type, price, description, is_active) VALUES
(1, 'regular', 50000, 'Tiket masuk reguler dewasa', 1),
(1, 'child', 25000, 'Tiket masuk anak (3-12 tahun)', 1),
(1, 'foreigner', 300000, 'Tiket masuk turis asing', 1),
(2, 'regular', 50000, 'Tiket masuk reguler dewasa', 1),
(2, 'child', 25000, 'Tiket masuk anak (3-12 tahun)', 1),
(2, 'foreigner', 300000, 'Tiket masuk turis asing', 1),
(6, 'regular', 100000, 'Tiket masuk taman nasional', 1),
(6, 'foreigner', 200000, 'Tiket masuk taman nasional turis asing', 1);

-- ============================================
-- SAMPLE HOTELS
-- ============================================
INSERT INTO users (name, email, password, phone, role, status, email_verified)
VALUES 
('Hotel Owner 1', 'hotel1@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'wisatawan', 'active', 1),
('Hotel Owner 2', 'hotel2@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', 'wisatawan', 'active', 1);

INSERT INTO hotels (owner_id, name, type, description, address, city, province, latitude, longitude, phone, email, rating_avg, total_reviews, is_approved, is_active) VALUES
(2, 'Grand Hotel Yogyakarta', 'hotel', 'Hotel bintang 4 di pusat kota Yogyakarta', 'Jl. Malioboro No.123', 'Yogyakarta', 'DI Yogyakarta', -7.7926, 110.3658, '0274-123456', 'info@grandhotel.com', 4.5, 120, 1, 1),
(3, 'Bali Beach Resort', 'hotel', 'Resort pantai mewah di Kuta, Bali', 'Jl. Pantai Kuta No.456', 'Kuta', 'Bali', -8.7185, 115.1686, '0361-789012', 'info@baliresort.com', 4.7, 250, 1, 1);

INSERT INTO hotel_rooms (hotel_id, room_type, description, capacity, price_per_night, total_rooms, available_rooms, amenities, is_active) VALUES
(1, 'Deluxe Room', 'Kamar deluxe dengan view kota', 2, 750000, 20, 18, '["AC","TV","WiFi","Breakfast"]', 1),
(1, 'Suite Room', 'Kamar suite dengan ruang tamu', 3, 1500000, 5, 4, '["AC","TV","WiFi","Breakfast","Mini Bar"]', 1),
(2, 'Standard Room', 'Kamar standar dengan view pantai', 2, 1000000, 30, 25, '["AC","TV","WiFi","Breakfast"]', 1),
(2, 'Ocean View Suite', 'Suite dengan view laut', 4, 2500000, 10, 8, '["AC","TV","WiFi","Breakfast","Mini Bar","Balcony"]', 1);

-- ============================================
-- SAMPLE RESTAURANTS
-- ============================================
INSERT INTO users (name, email, password, phone, role, status, email_verified)
VALUES 
('Restaurant Owner 1', 'resto1@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567892', 'wisatawan', 'active', 1),
('Restaurant Owner 2', 'resto2@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567893', 'wisatawan', 'active', 1);

INSERT INTO restaurants (owner_id, name, type, cuisine_type, description, address, city, province, latitude, longitude, phone, email, opening_time, closing_time, rating_avg, total_reviews, is_approved, is_active) VALUES
(4, 'Warung Gudeg Yu Djum', 'warung', 'Jawa', 'Warung gudeg legendaris di Yogyakarta', 'Jl. Wijilan No.123', 'Yogyakarta', 'DI Yogyakarta', -7.7926, 110.3658, '0274-234567', 'info@gudegyudjum.com', '08:00:00', '21:00:00', 4.8, 500, 1, 1),
(5, 'Beach Club Bali', 'kafe', 'International', 'Kafe pantai dengan makanan internasional', 'Jl. Pantai Kuta No.789', 'Kuta', 'Bali', -8.7185, 115.1686, '0361-890123', 'info@beachclub.com', '10:00:00', '23:00:00', 4.6, 300, 1, 1);

INSERT INTO menu_items (restaurant_id, name, description, price, category, is_available) VALUES
(1, 'Gudeg Nangka', 'Gudeg dengan nangka muda', 25000, 'Main Course', 1),
(1, 'Ayam Goreng', 'Ayam goreng dengan sambal', 30000, 'Main Course', 1),
(1, 'Es Teh Manis', 'Teh manis dingin', 5000, 'Beverage', 1),
(2, 'Nasi Goreng Spesial', 'Nasi goreng dengan seafood', 75000, 'Main Course', 1),
(2, 'Grilled Fish', 'Ikan bakar dengan sambal', 120000, 'Main Course', 1),
(2, 'Coconut Cocktail', 'Koktail kelapa segar', 45000, 'Beverage', 1);

-- ============================================
-- SAMPLE EVENTS
-- ============================================
INSERT INTO users (name, email, password, phone, role, status, email_verified)
VALUES 
('Event Organizer 1', 'event1@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567894', 'wisatawan', 'active', 1);

INSERT INTO events (organizer_id, title, slug, description, category, start_date, end_date, location_name, address, latitude, longitude, price, max_participants, registered_count, is_active) VALUES
(6, 'Bali Arts Festival', 'bali-arts-festival', 'Festival seni tahunan terbesar di Bali', 'budaya', '2026-07-15 09:00:00', '2026-07-30 21:00:00', 'Taman Budaya Denpasar', 'Jl. Nusa Indah No.123', -8.6705, 115.2126, 0, 50000, 0, 1),
(6, 'Jakarta Food Festival', 'jakarta-food-festival', 'Festival kuliner internasional di Jakarta', 'kuliner', '2026-08-01 10:00:00', '2026-08-10 22:00:00', 'JIExpo Kemayoran', 'Jl. Benyamin Sueb', -6.1525, 106.8588, 50000, 100000, 0, 1);

-- ============================================
-- SAMPLE TOUR GUIDES
-- ============================================
INSERT INTO users (name, email, password, phone, role, status, email_verified)
VALUES 
('Budi Santoso', 'guide1@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567895', 'tour_guide', 'active', 1),
('Siti Rahayu', 'guide2@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567896', 'tour_guide', 'active', 1),
('Made Wijaya', 'guide3@mywisata.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567897', 'tour_guide', 'active', 1);

INSERT INTO user_profiles (user_id, first_name, last_name, city, province, country, bio) VALUES
(7, 'Budi', 'Santoso', 'Yogyakarta', 'DI Yogyakarta', 'Indonesia', 'Pemandu wisata berpengalaman 10 tahun di Yogyakarta'),
(8, 'Siti', 'Rahayu', 'Bali', 'Bali', 'Indonesia', 'Pemandu wisata spesialis budaya Bali'),
(9, 'Made', 'Wijaya', 'Denpasar', 'Bali', 'Indonesia', 'Pemandu wisata bahasa Inggris dan Jepang');

INSERT INTO tour_guides (user_id, license_number, experience_years, hourly_rate, daily_rate, rating_avg, total_reviews, total_tours, is_verified, is_available, latitude, longitude) VALUES
(7, 'LIC-2026-001', 10, 150000, 1000000, 4.8, 120, 350, 1, 1, -7.7926, 110.3658),
(8, 'LIC-2026-002', 8, 200000, 1500000, 4.9, 200, 450, 1, 1, -8.6705, 115.2126),
(9, 'LIC-2026-003', 5, 250000, 2000000, 4.7, 80, 200, 1, 1, -8.6705, 115.2126);

INSERT INTO guide_languages (guide_id, language, proficiency) VALUES
(1, 'Indonesia', 'native'),
(1, 'English', 'fluent'),
(1, 'Japanese', 'intermediate'),
(2, 'Indonesia', 'native'),
(2, 'English', 'fluent'),
(2, 'French', 'intermediate'),
(3, 'Indonesia', 'native'),
(3, 'English', 'fluent'),
(3, 'Japanese', 'fluent');

INSERT INTO guide_specializations (guide_id, specialization) VALUES
(1, 'Budaya Jawa'),
(1, 'Sejarah Candi'),
(2, 'Budaya Bali'),
(2, 'Seni Tradisional'),
(3, 'Adventure'),
(3, 'Diving');

-- ============================================
-- SETTINGS
-- ============================================
INSERT INTO settings (key_name, value, type, description) VALUES
('site_name', 'MyWisata Application', 'text', 'Nama aplikasi'),
('site_description', 'Platform marketplace untuk layanan pariwisata', 'text', 'Deskripsi aplikasi'),
('default_language', 'id', 'text', 'Bahasa default'),
('currency', 'IDR', 'text', 'Mata uang'),
('currency_symbol', 'Rp', 'text', 'Simbol mata uang'),
('contact_email', 'admin@mywisata.com', 'text', 'Email kontak'),
('contact_phone', '+62 812 3456 7890', 'text', 'Nomor telepon kontak'),
('max_upload_size', '5242880', 'number', 'Max upload size (bytes)'),
('enable_ai_chat', '1', 'boolean', 'Aktifkan AI chat'),
('enable_audio_guide', '1', 'boolean', 'Aktifkan audio guide'),
('enable_notifications', '1', 'boolean', 'Aktifkan notifikasi'),
('maintenance_mode', '0', 'boolean', 'Mode maintenance'),
('timezone', 'Asia/Jakarta', 'text', 'Zona waktu default'),
('date_format', 'd-m-Y', 'text', 'Format tanggal'),
('time_format', 'H:i', 'text', 'Format waktu'),
('items_per_page', '20', 'number', 'Jumlah item per halaman'),
('session_timeout', '1800', 'number', 'Session timeout (detik)'),
('rate_limit_per_minute', '60', 'number', 'Rate limit per menit per user');

-- ============================================
-- Seed Data Complete
-- ============================================
-- Total records inserted:
-- - Users: 7 (1 admin, 2 hotel owners, 2 restaurant owners, 1 event organizer, 3 tour guides)
-- - User Profiles: 4
-- - Tour Guides: 3
-- - Guide Languages: 9
-- - Guide Specializations: 6
-- - Destination Categories: 10
-- - Destinations: 8
-- - Tickets: 8
-- - Hotels: 2
-- - Hotel Rooms: 4
-- - Restaurants: 2
-- - Menu Items: 6
-- - Events: 2
-- - Settings: 17
