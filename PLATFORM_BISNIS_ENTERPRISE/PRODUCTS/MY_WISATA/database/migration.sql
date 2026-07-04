-- MyWisata Application - Database Migration
-- Database: mywisata
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- MySQL Version: 8.0+
-- Total Tables: 33
-- Created: 2026-06-30

-- Disable foreign key checks during migration
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist (for clean migration)
DROP TABLE IF EXISTS rate_limits;
DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS chat_sessions;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS audio_guides;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS transaction_items;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS event_registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS restaurant_order_items;
DROP TABLE IF EXISTS restaurant_orders;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS restaurants;
DROP TABLE IF EXISTS hotel_bookings;
DROP TABLE IF EXISTS hotel_rooms;
DROP TABLE IF EXISTS hotels;
DROP TABLE IF EXISTS ticket_order_items;
DROP TABLE IF EXISTS ticket_orders;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS destination_images;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS destination_categories;
DROP TABLE IF EXISTS guide_documents;
DROP TABLE IF EXISTS guide_schedules;
DROP TABLE IF EXISTS guide_specializations;
DROP TABLE IF EXISTS guide_languages;
DROP TABLE IF EXISTS tour_guides;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS users;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- TABLE 1: users
-- ============================================
CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    phone           VARCHAR(20),
    role            ENUM('admin','wisatawan','tour_guide') NOT NULL DEFAULT 'wisatawan',
    avatar          VARCHAR(255),
    status          ENUM('active','inactive','banned','pending') NOT NULL DEFAULT 'active',
    email_verified  TINYINT(1) NOT NULL DEFAULT 0,
    remember_token  VARCHAR(255),
    last_login      DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 2: user_profiles
-- ============================================
CREATE TABLE user_profiles (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    first_name      VARCHAR(50),
    last_name       VARCHAR(50),
    birth_date      DATE,
    gender          ENUM('male','female','other'),
    nationality     VARCHAR(50),
    address         TEXT,
    city            VARCHAR(100),
    province        VARCHAR(100),
    postal_code     VARCHAR(10),
    country         VARCHAR(100) DEFAULT 'Indonesia',
    bio             TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 3: tour_guides
-- ============================================
CREATE TABLE tour_guides (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL UNIQUE,
    license_number  VARCHAR(50),
    experience_years INT DEFAULT 0,
    hourly_rate     DECIMAL(10,2) DEFAULT 0,
    daily_rate      DECIMAL(10,2) DEFAULT 0,
    rating_avg      DECIMAL(2,1) DEFAULT 0.0,
    total_reviews   INT DEFAULT 0,
    total_tours     INT DEFAULT 0,
    is_verified     TINYINT(1) NOT NULL DEFAULT 0,
    verified_at     DATETIME,
    verified_by     BIGINT UNSIGNED,
    is_available    TINYINT(1) NOT NULL DEFAULT 1,
    latitude        DECIMAL(10,7),
    longitude       DECIMAL(10,7),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_verified (is_verified),
    INDEX idx_available (is_available),
    INDEX idx_rating (rating_avg)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 4: guide_languages
-- ============================================
CREATE TABLE guide_languages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id        BIGINT UNSIGNED NOT NULL,
    language        VARCHAR(50) NOT NULL,
    proficiency     ENUM('basic','intermediate','fluent','native') NOT NULL DEFAULT 'fluent',
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_guide (guide_id),
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 5: guide_specializations
-- ============================================
CREATE TABLE guide_specializations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id        BIGINT UNSIGNED NOT NULL,
    specialization  VARCHAR(100) NOT NULL,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_guide (guide_id),
    INDEX idx_spec (specialization)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 6: guide_schedules
-- ============================================
CREATE TABLE guide_schedules (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id        BIGINT UNSIGNED NOT NULL,
    available_date  DATE NOT NULL,
    start_time      TIME DEFAULT '08:00:00',
    end_time        TIME DEFAULT '17:00:00',
    is_booked       TINYINT(1) NOT NULL DEFAULT 0,
    notes           VARCHAR(255),
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_guide_date (guide_id, available_date),
    INDEX idx_available (available_date, is_booked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 7: guide_documents
-- ============================================
CREATE TABLE guide_documents (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id        BIGINT UNSIGNED NOT NULL,
    document_type   ENUM('ktp','sertifikat','lisensi','other') NOT NULL,
    file_path       VARCHAR(255) NOT NULL,
    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 8: destination_categories
-- ============================================
CREATE TABLE destination_categories (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    icon            VARCHAR(50),
    description     TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 9: destinations
-- ============================================
CREATE TABLE destinations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     BIGINT UNSIGNED,
    name            VARCHAR(200) NOT NULL,
    slug            VARCHAR(200) NOT NULL UNIQUE,
    description     TEXT,
    short_desc      VARCHAR(500),
    address         TEXT,
    city            VARCHAR(100),
    province        VARCHAR(100),
    latitude        DECIMAL(10,7) NOT NULL,
    longitude       DECIMAL(10,7) NOT NULL,
    entry_fee       DECIMAL(10,2) DEFAULT 0,
    opening_time    TIME,
    closing_time    TIME,
    rating_avg      DECIMAL(2,1) DEFAULT 0.0,
    total_reviews   INT DEFAULT 0,
    total_visitors  INT DEFAULT 0,
    daily_quota     INT,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    main_image      VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES destination_categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_city (city),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_coords (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 10: destination_images
-- ============================================
CREATE TABLE destination_images (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id  BIGINT UNSIGNED NOT NULL,
    file_path       VARCHAR(255) NOT NULL,
    caption         VARCHAR(255),
    sort_order      INT DEFAULT 0,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX idx_dest (destination_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 11: tickets
-- ============================================
CREATE TABLE tickets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id  BIGINT UNSIGNED NOT NULL,
    ticket_type     ENUM('regular','child','senior','group','foreigner') NOT NULL DEFAULT 'regular',
    price           DECIMAL(10,2) NOT NULL,
    description     VARCHAR(255),
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX idx_dest (destination_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 12: ticket_orders
-- ============================================
CREATE TABLE ticket_orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code      VARCHAR(30) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    transaction_id  BIGINT UNSIGNED,
    visit_date      DATE NOT NULL,
    total_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('pending','paid','confirmed','used','cancelled','refunded') NOT NULL DEFAULT 'pending',
    qr_code_path    VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_code (order_code),
    INDEX idx_status (status),
    INDEX idx_visit_date (visit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 13: ticket_order_items
-- ============================================
CREATE TABLE ticket_order_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    ticket_id       BIGINT UNSIGNED NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    subtotal        DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES ticket_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 14: hotels
-- ============================================
CREATE TABLE hotels (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id        BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    type            ENUM('hotel','homestay','villa','guesthouse') NOT NULL DEFAULT 'hotel',
    description     TEXT,
    address         TEXT,
    city            VARCHAR(100),
    province        VARCHAR(100),
    latitude        DECIMAL(10,7),
    longitude       DECIMAL(10,7),
    phone           VARCHAR(20),
    email           VARCHAR(150),
    rating_avg      DECIMAL(2,1) DEFAULT 0.0,
    total_reviews   INT DEFAULT 0,
    main_image      VARCHAR(255),
    is_approved     TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_city (city),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 15: hotel_rooms
-- ============================================
CREATE TABLE hotel_rooms (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id        BIGINT UNSIGNED NOT NULL,
    room_type       VARCHAR(100) NOT NULL,
    description     TEXT,
    capacity        INT DEFAULT 2,
    price_per_night DECIMAL(10,2) NOT NULL,
    total_rooms     INT NOT NULL DEFAULT 1,
    available_rooms INT NOT NULL DEFAULT 1,
    amenities       JSON,
    image           VARCHAR(255),
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_hotel (hotel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 16: hotel_bookings
-- ============================================
CREATE TABLE hotel_bookings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_code    VARCHAR(30) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    hotel_id        BIGINT UNSIGNED NOT NULL,
    room_id         BIGINT UNSIGNED NOT NULL,
    transaction_id  BIGINT UNSIGNED,
    check_in        DATE NOT NULL,
    check_out       DATE NOT NULL,
    num_rooms       INT NOT NULL DEFAULT 1,
    num_nights      INT NOT NULL DEFAULT 1,
    total_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('pending','confirmed','checked_in','checked_out','cancelled') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES hotel_rooms(id) ON DELETE RESTRICT,
    INDEX idx_user (user_id),
    INDEX idx_hotel (hotel_id),
    INDEX idx_dates (check_in, check_out)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 17: restaurants
-- ============================================
CREATE TABLE restaurants (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id        BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    type            ENUM('restoran','warung','kafe','umkm','street_food') NOT NULL DEFAULT 'restoran',
    cuisine_type    VARCHAR(100),
    description     TEXT,
    address         TEXT,
    city            VARCHAR(100),
    province        VARCHAR(100),
    latitude        DECIMAL(10,7),
    longitude       DECIMAL(10,7),
    phone           VARCHAR(20),
    email           VARCHAR(150),
    opening_time    TIME,
    closing_time    TIME,
    rating_avg      DECIMAL(2,1) DEFAULT 0.0,
    total_reviews   INT DEFAULT 0,
    main_image      VARCHAR(255),
    is_approved     TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_city (city),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 18: menu_items
-- ============================================
CREATE TABLE menu_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id   BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    description     TEXT,
    price           DECIMAL(10,2) NOT NULL,
    category        VARCHAR(50),
    image           VARCHAR(255),
    is_available    TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_available (is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 19: restaurant_orders
-- ============================================
CREATE TABLE restaurant_orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code      VARCHAR(30) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    restaurant_id   BIGINT UNSIGNED NOT NULL,
    transaction_id  BIGINT UNSIGNED,
    order_type      ENUM('dine_in','pickup','delivery') NOT NULL DEFAULT 'dine_in',
    total_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('pending','confirmed','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 20: restaurant_order_items
-- ============================================
CREATE TABLE restaurant_order_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    menu_item_id    BIGINT UNSIGNED NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    subtotal        DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES restaurant_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 21: events
-- ============================================
CREATE TABLE events (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id    BIGINT UNSIGNED NOT NULL,
    title           VARCHAR(200) NOT NULL,
    slug            VARCHAR(200) NOT NULL UNIQUE,
    description     TEXT,
    category        ENUM('festival','seni','kuliner','olahraga','budaya','religi','other') NOT NULL DEFAULT 'budaya',
    start_date      DATETIME NOT NULL,
    end_date        DATETIME NOT NULL,
    location_name   VARCHAR(200),
    address         TEXT,
    latitude        DECIMAL(10,7),
    longitude       DECIMAL(10,7),
    price           DECIMAL(10,2) DEFAULT 0,
    max_participants INT,
    registered_count INT DEFAULT 0,
    main_image      VARCHAR(255),
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dates (start_date, end_date),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 22: event_registrations
-- ============================================
CREATE TABLE event_registrations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_code VARCHAR(30) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    event_id        BIGINT UNSIGNED NOT NULL,
    transaction_id  BIGINT UNSIGNED,
    num_tickets     INT NOT NULL DEFAULT 1,
    total_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('registered','attended','cancelled') NOT NULL DEFAULT 'registered',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 23: bookings (Tour Guide Booking)
-- ============================================
CREATE TABLE bookings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_code    VARCHAR(30) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    guide_id        BIGINT UNSIGNED NOT NULL,
    transaction_id  BIGINT UNSIGNED,
    booking_date    DATE NOT NULL,
    start_time      TIME NOT NULL,
    duration_hours  DECIMAL(4,1) NOT NULL,
    num_participants INT NOT NULL DEFAULT 1,
    destination_id  BIGINT UNSIGNED,
    total_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('pending','confirmed','completed','cancelled','rejected') NOT NULL DEFAULT 'pending',
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_guide (guide_id),
    INDEX idx_date (booking_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 24: transactions
-- ============================================
CREATE TABLE transactions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(30) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    type            ENUM('booking_guide','ticket','hotel','restaurant','event','refund') NOT NULL,
    reference_id    BIGINT UNSIGNED,
    gross_amount    DECIMAL(12,2) NOT NULL,
    discount        DECIMAL(12,2) DEFAULT 0,
    net_amount      DECIMAL(12,2) NOT NULL,
    payment_method  ENUM('transfer','cash','e_wallet','other') NOT NULL DEFAULT 'transfer',
    payment_status  ENUM('pending','paid','failed','refunded','expired') NOT NULL DEFAULT 'pending',
    paid_at         DATETIME,
    payment_proof   VARCHAR(255),
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_payment_status (payment_status),
    INDEX idx_code (transaction_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 25: transaction_items
-- ============================================
CREATE TABLE transaction_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id  BIGINT UNSIGNED NOT NULL,
    item_type       VARCHAR(50) NOT NULL,
    item_id         BIGINT UNSIGNED NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    unit_price      DECIMAL(12,2) NOT NULL,
    subtotal        DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 26: reviews
-- ============================================
CREATE TABLE reviews (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    reviewable_type ENUM('guide','destination','hotel','restaurant','event') NOT NULL,
    reviewable_id   BIGINT UNSIGNED NOT NULL,
    rating          TINYINT NOT NULL,
    comment         TEXT,
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reviewable (reviewable_type, reviewable_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 27: audio_guides
-- ============================================
CREATE TABLE audio_guides (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id  BIGINT UNSIGNED NOT NULL,
    language        VARCHAR(10) NOT NULL DEFAULT 'id',
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    file_path       VARCHAR(255) NOT NULL,
    duration_seconds INT,
    transcript      TEXT,
    play_count      INT DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX idx_dest_lang (destination_id, language),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 28: notifications
-- ============================================
CREATE TABLE notifications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    type            ENUM('booking','payment','event','reminder','system','broadcast') NOT NULL,
    title           VARCHAR(200) NOT NULL,
    message         TEXT NOT NULL,
    link            VARCHAR(255),
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    is_email_sent   TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 29: audit_logs
-- ============================================
CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED,
    action          VARCHAR(50) NOT NULL,
    module          VARCHAR(50) NOT NULL,
    description     TEXT,
    ip_address      VARCHAR(45),
    user_agent      VARCHAR(255),
    old_data        JSON,
    new_data        JSON,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_module (module),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 30: settings
-- ============================================
CREATE TABLE settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name        VARCHAR(100) NOT NULL UNIQUE,
    value           TEXT,
    type            ENUM('text','number','boolean','json','image') NOT NULL DEFAULT 'text',
    description     VARCHAR(255),
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 31: chat_sessions
-- ============================================
CREATE TABLE chat_sessions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    session_token   VARCHAR(64) NOT NULL UNIQUE,
    context         JSON,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 32: chat_messages
-- ============================================
CREATE TABLE chat_messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id      BIGINT UNSIGNED NOT NULL,
    role            ENUM('user','assistant') NOT NULL,
    message         TEXT NOT NULL,
    metadata        JSON,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE 33: rate_limits
-- ============================================
CREATE TABLE rate_limits (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key         VARCHAR(128) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_key (api_key),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration Complete
-- ============================================
-- Total tables created: 33
-- Database: mywisata
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
