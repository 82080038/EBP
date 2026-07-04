# MODUL 05 — DESAIN DATABASE MYSQL & ERD

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.1  
> **Tanggal:** 2026-06-30  
> **DBMS:** MySQL 8.0+  
> **Charset:** utf8mb4, collate utf8mb4_unicode_ci  
> **Last Updated:** 2026-06-30

---

## 1. DAFTAR TABEL DATABASE

| No | Nama Tabel | Deskripsi | Estimasi Record |
|----|-----------|-----------|-----------------|
| 1 | `users` | Data semua pengguna (3 role) | 10.000 |
| 2 | `user_profiles` | Profil tambahan pengguna | 10.000 |
| 3 | `tour_guides` | Data khusus tour guide | 500 |
| 4 | `guide_languages` | Bahasa yang dikuasai guide | 1.500 |
| 5 | `guide_specializations` | Spesialisasi guide | 1.000 |
| 6 | `guide_schedules` | Ketersediaan jadwal guide | 50.000 |
| 7 | `guide_documents` | Dokumen verifikasi guide | 1.000 |
| 8 | `destinations` | Destinasi wisata | 1.000 |
| 9 | `destination_categories` | Kategori destinasi | 20 |
| 10 | `destination_images` | Foto destinasi | 5.000 |
| 11 | `tickets` | Tiket destinasi | 5.000 |
| 12 | `ticket_orders` | Pembelian tiket | 50.000 |
| 13 | `ticket_order_items` | Detail item tiket | 100.000 |
| 14 | `hotels` | Hotel & homestay | 500 |
| 15 | `hotel_rooms` | Kamar hotel | 2.000 |
| 16 | `hotel_bookings` | Booking hotel | 20.000 |
| 17 | `restaurants` | Restoran & UMKM | 1.000 |
| 18 | `menu_items` | Menu restoran | 10.000 |
| 19 | `restaurant_orders` | Pesanan restoran | 30.000 |
| 20 | `restaurant_order_items` | Detail pesanan | 60.000 |
| 21 | `events` | Event & budaya | 2.000 |
| 22 | `event_registrations` | Pendaftaran event | 20.000 |
| 23 | `bookings` | Booking tour guide | 50.000 |
| 24 | `transactions` | Transaksi pembayaran | 100.000 |
| 25 | `transaction_items` | Detail item transaksi | 200.000 |
| 26 | `reviews` | Rating & review | 30.000 |
| 27 | `audio_guides` | File audio per destinasi | 3.000 |
| 28 | `notifications` | Notifikasi pengguna | 500.000 |
| 29 | `audit_logs` | Log audit sistem | 1.000.000 |
| 30 | `settings` | Pengaturan aplikasi | 100 |
| 31 | `chat_sessions` | Sesi chat AI | 10.000 |
| 32 | `chat_messages` | Pesan chat AI | 100.000 |
| 33 | `rate_limits` | Log rate limiting API | 500.000 |

**Total: 33 tabel**

---

## 2. ERD (ENTITY RELATIONSHIP DIAGRAM)

### 2.1 Diagram Tekstual

```
┌──────────┐     ┌──────────────┐     ┌──────────────┐
│  users   │────►│ tour_guides  │────►│guide_languages│
│ (1)      │  1:1│ (0..1)       │ 1:N │(N)            │
└────┬─────┘     └──────┬───────┘     └──────────────┘
     │                  │
     │ 1:1              │ 1:N
     ▼                  ▼
┌──────────────┐  ┌──────────────────┐
│user_profiles │  │guide_specializ.  │
│(1)           │  │(N)               │
└──────────────┘  └──────────────────┘
     │                  │
     │                  │ 1:N
     │                  ▼
     │            ┌──────────────────┐
     │            │ guide_schedules  │
     │            │(N)               │
     │            └──────────────────┘
     │
     │ 1:N        ┌──────────┐
     ├───────────►│ bookings │◄──── tour_guides (1:N)
     │            │(N)       │
     │            └────┬─────┘
     │                 │ N:1
     │                 ▼
     │            ┌──────────────┐
     │            │ transactions │
     │            │(N)           │
     │            └──────┬───────┘
     │                   │ 1:N
     │                   ▼
     │            ┌────────────────────┐
     │            │transaction_items   │
     │            │(N)                 │
     │            └────────────────────┘
     │
     │ 1:N        ┌──────────────┐
     ├───────────►│ ticket_orders│────► ticket_order_items (1:N)
     │            │(N)           │
     │            └──────────────┘
     │
     │ 1:N        ┌──────────────┐
     ├───────────►│ hotel_bookings│
     │            │(N)           │
     │            └──────────────┘
     │
     │ 1:N        ┌──────────────────┐
     ├───────────►│ restaurant_orders│────► order_items (1:N)
     │            │(N)               │
     │            └──────────────────┘
     │
     │ 1:N        ┌──────────────────┐
     ├───────────►│event_registrations│
     │            │(N)               │
     │            └──────────────────┘
     │
     │ 1:N        ┌──────────────┐
     ├───────────►│   reviews    │
     │            │(N)           │
     │            └──────────────┘
     │
     │ 1:N        ┌────────────────┐
     └───────────►│ notifications  │
                  │(N)             │
                  └────────────────┘

┌────────────────────┐     ┌──────────────────────┐
│destination_categ.  │────►│   destinations       │
│(1)                 │ 1:N │(N)                   │
└────────────────────┘     └──────┬───────────────┘
                                  │ 1:N
                           ┌──────┴──────┐
                           │             │ 1:N
                           ▼             ▼
                   ┌────────────┐ ┌──────────────┐
                   │tickets     │ │audio_guides  │
                   │(N)         │ │(N)           │
                   └────────────┘ └──────────────┘
                           │ 1:N
                           ▼
                   ┌──────────────────┐
                   │destination_images│
                   │(N)               │
                   └──────────────────┘

┌──────────┐     ┌──────────────┐
│ hotels   │────►│ hotel_rooms  │
│(1)       │ 1:N │(N)           │
└──────────┘     └──────────────┘

┌──────────────┐     ┌──────────────┐
│ restaurants  │────►│  menu_items  │
│(1)           │ 1:N │(N)           │
└──────────────┘     └──────────────┘

┌──────────┐     ┌──────────────────────┐
│  events  │────►│event_registrations   │
│(1)       │ 1:N │(N)                   │
└──────────┘     └──────────────────────┘

┌────────────────┐     ┌──────────────┐
│ chat_sessions  │────►│ chat_messages│
│(1)             │ 1:N │(N)           │
└────────────────┘     └──────────────┘
```

---

## 2.1 MYSQL 8.0 OPTIMIZATION STRATEGIES

### Three-Star Indexing System

Based on MySQL 8.0 best practices, use the Three-Star System for optimal index design:

| Star | Criteria | Benefit |
|------|----------|----------|
| ⭐ First Star | Index groups relevant WHERE rows together (equality columns first) | Minimize scan range |
| ⭐⭐ Second Star | Index covers ORDER BY columns (after equality columns) | Avoid filesort |
| ⭐⭐⭐ Third Star | Index covers SELECT columns (covering index) | No bookmark lookup |

### MySQL 8.0 Specific Features

| Feature | Description | Use Case |
|---------|-------------|----------|
| **Descending Indexes** | True DESC storage for mixed-direction ORDER BY | ORDER BY col1 ASC, col2 DESC |
| **Invisible Indexes** | Test dropping indexes safely | Index optimization testing |
| **Functional Indexes** | Index expressions | WHERE UPPER(name) = 'TEST' |
| **Histogram Statistics** | Better cost estimation without indexes | Large table analytics |
| **Hash Join** | Dramatically faster indexless JOINs | Large table joins |
| **Index Skip Scan** | Skip leftmost column in certain scenarios | Index on (col1, col2), query on col2 only |

### Index Strategy for Key Tables

#### bookings table
```sql
-- Composite index for guide availability check
CREATE INDEX idx_guide_date_status ON bookings(guide_id, booking_date, status);

-- Index for user booking history
CREATE INDEX idx_user_date ON bookings(user_id, booking_date DESC);

-- Index for status filtering
CREATE INDEX idx_status_date ON bookings(status, created_at DESC);
```

#### transactions table
```sql
-- Index for payment status lookup
CREATE INDEX idx_payment_status ON transactions(payment_status, created_at DESC);

-- Index for user transaction history
CREATE INDEX idx_user_type ON transactions(user_id, type, created_at DESC);

-- Unique index for transaction codes
CREATE UNIQUE INDEX idx_code ON transactions(transaction_code);
```

#### destinations table
```sql
-- Composite index for location-based search
CREATE INDEX idx_city_active ON destinations(city, is_active, rating_avg DESC);

-- Index for coordinate-based queries
CREATE INDEX idx_coords ON destinations(latitude, longitude);

-- Index for category filtering
CREATE INDEX idx_category_active ON destinations(category_id, is_active);
```

### Query Optimization Guidelines

1. **Use EXPLAIN** to analyze query execution plans
2. **Avoid SELECT \*** — only select needed columns
3. **Use LIMIT** for pagination to reduce data transfer
4. **Optimize JOINs** — ensure indexed columns are used for joins
5. **Use appropriate data types** — use INT instead of VARCHAR for IDs
6. **Normalize vs Denormalize** — balance between read performance and write overhead

---

## 3. SKEMA DATABASE — DDL SQL

### 3.1 Tabel `users`

```sql
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
```

### 3.2 Tabel `user_profiles`

```sql
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
```

### 3.3 Tabel `tour_guides`

```sql
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
```

### 3.4 Tabel `guide_languages`

```sql
CREATE TABLE guide_languages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id        BIGINT UNSIGNED NOT NULL,
    language        VARCHAR(50) NOT NULL,
    proficiency     ENUM('basic','intermediate','fluent','native') NOT NULL DEFAULT 'fluent',
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_guide (guide_id),
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.5 Tabel `guide_specializations`

```sql
CREATE TABLE guide_specializations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id        BIGINT UNSIGNED NOT NULL,
    specialization  VARCHAR(100) NOT NULL,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_guide (guide_id),
    INDEX idx_spec (specialization)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.6 Tabel `guide_schedules`

```sql
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
```

### 3.7 Tabel `guide_documents`

```sql
CREATE TABLE guide_documents (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id        BIGINT UNSIGNED NOT NULL,
    document_type   ENUM('ktp','sertifikat','lisensi','other') NOT NULL,
    file_path       VARCHAR(255) NOT NULL,
    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.8 Tabel `destination_categories`

```sql
CREATE TABLE destination_categories (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    icon            VARCHAR(50),
    description     TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.9 Tabel `destinations`

```sql
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
```

### 3.10 Tabel `destination_images`

```sql
CREATE TABLE destination_images (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id  BIGINT UNSIGNED NOT NULL,
    file_path       VARCHAR(255) NOT NULL,
    caption         VARCHAR(255),
    sort_order      INT DEFAULT 0,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX idx_dest (destination_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.11 Tabel `tickets`

```sql
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
```

### 3.12 Tabel `ticket_orders`

```sql
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
```

### 3.13 Tabel `ticket_order_items`

```sql
CREATE TABLE ticket_order_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    ticket_id       BIGINT UNSIGNED NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    subtotal        DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES ticket_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.14 Tabel `hotels`

```sql
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
```

### 3.15 Tabel `hotel_rooms`

```sql
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
```

### 3.16 Tabel `hotel_bookings`

```sql
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
```

### 3.17 Tabel `restaurants`

```sql
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
```

### 3.18 Tabel `menu_items`

```sql
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
```

### 3.19 Tabel `restaurant_orders`

```sql
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
```

### 3.20 Tabel `restaurant_order_items`

```sql
CREATE TABLE restaurant_order_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    menu_item_id    BIGINT UNSIGNED NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    subtotal        DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES restaurant_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.21 Tabel `events`

```sql
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
```

### 3.22 Tabel `event_registrations`

```sql
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
```

### 3.23 Tabel `bookings` (Tour Guide Booking)

```sql
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
```

### 3.24 Tabel `transactions`

```sql
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
```

### 3.25 Tabel `reviews`

```sql
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
```

### 3.26 Tabel `audio_guides`

```sql
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
```

### 3.27 Tabel `notifications`

```sql
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
```

### 3.28 Tabel `audit_logs`

```sql
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
```

### 3.29 Tabel `settings`

```sql
CREATE TABLE settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name        VARCHAR(100) NOT NULL UNIQUE,
    value           TEXT,
    type            ENUM('text','number','boolean','json','image') NOT NULL DEFAULT 'text',
    description     VARCHAR(255),
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.30 Tabel `chat_sessions`

```sql
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
```

### 3.31 Tabel `chat_messages`

```sql
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
```

### 3.33 Tabel `rate_limits`

```sql
CREATE TABLE rate_limits (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key         VARCHAR(128) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_key (api_key),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **Catatan:** Tabel ini digunakan oleh `RateLimiter` class (lihat Modul 20 — Security System).
> Data lama (>60 detik) sebaiknya dibersihkan secara berkala via cron job.

---

## 4. RELASI ANTAR TABEL (RINGKASAN)

| Dari | Ke | Tipe | FK |
|------|-----|------|-----|
| users | user_profiles | 1:1 | user_id |
| users | tour_guides | 1:0..1 | user_id |
| tour_guides | guide_languages | 1:N | guide_id |
| tour_guides | guide_specializations | 1:N | guide_id |
| tour_guides | guide_schedules | 1:N | guide_id |
| tour_guides | guide_documents | 1:N | guide_id |
| destination_categories | destinations | 1:N | category_id |
| destinations | destination_images | 1:N | destination_id |
| destinations | tickets | 1:N | destination_id |
| destinations | audio_guides | 1:N | destination_id |
| users | ticket_orders | 1:N | user_id |
| ticket_orders | ticket_order_items | 1:N | order_id |
| tickets | ticket_order_items | 1:N | ticket_id |
| users | hotels | 1:N | owner_id |
| hotels | hotel_rooms | 1:N | hotel_id |
| users | hotel_bookings | 1:N | user_id |
| hotels | hotel_bookings | 1:N | hotel_id |
| hotel_rooms | hotel_bookings | 1:N | room_id |
| users | restaurants | 1:N | owner_id |
| restaurants | menu_items | 1:N | restaurant_id |
| users | restaurant_orders | 1:N | user_id |
| restaurants | restaurant_orders | 1:N | restaurant_id |
| restaurant_orders | restaurant_order_items | 1:N | order_id |
| menu_items | restaurant_order_items | 1:N | menu_item_id |
| users | events | 1:N | organizer_id |
| users | event_registrations | 1:N | user_id |
| events | event_registrations | 1:N | event_id |
| users | bookings | 1:N | user_id |
| tour_guides | bookings | 1:N | guide_id |
| destinations | bookings | 1:N | destination_id |
| users | transactions | 1:N | user_id |
| users | reviews | 1:N | user_id |
| users | notifications | 1:N | user_id |
| users | chat_sessions | 1:N | user_id |
| chat_sessions | chat_messages | 1:N | session_id |

---

## 5. INDEX STRATEGY

| Tabel | Index | Tujuan |
|-------|-------|--------|
| users | idx_email | Login lookup |
| users | idx_role_status | Filter by role & status |
| tour_guides | idx_verified_available | Filter verified & available guides |
| tour_guides | idx_rating | Sort by rating |
| destinations | idx_coords | Map bounding box query |
| destinations | idx_city_active | Filter by city |
| bookings | idx_guide_date | Check guide availability |
| bookings | idx_user_status | User booking history |
| transactions | idx_payment_status | Payment tracking |
| notifications | idx_user_read | Unread badge count |
| audit_logs | idx_created | Log retention cleanup |
| rate_limits | idx_api_key | Rate limit lookup by key |
| rate_limits | idx_created | Cleanup old entries |

---

## 6. DATA AWAL (SEED DATA)

```sql
-- Admin default
INSERT INTO users (name, email, password, role, status, email_verified)
VALUES ('Admin', 'admin@tourguide.app',
        '$2y$10$YourBcryptHashHere', 'admin', 'active', 1);

-- Kategori destinasi
INSERT INTO destination_categories (name, slug, icon) VALUES
('Alam', 'alam', 'fa-mountain'),
('Budaya', 'budaya', 'fa-landmark'),
('Sejarah', 'sejarah', 'fa-monument'),
('Pantai', 'pantai', 'fa-water'),
('Gunung', 'gunung', 'fa-mountain'),
('Taman Nasional', 'taman-nasional', 'fa-tree'),
('Museum', 'museum', 'fa-building'),
('Kuliner', 'kuliner', 'fa-utensils');

-- Settings default
INSERT INTO settings (key_name, value, type, description) VALUES
('site_name', 'Tour Guide Application', 'text', 'Nama aplikasi'),
('default_language', 'id', 'text', 'Bahasa default'),
('currency', 'IDR', 'text', 'Mata uang'),
('contact_email', 'admin@tourguide.app', 'text', 'Email kontak'),
('max_upload_size', '5242880', 'number', 'Max upload size (bytes)'),
('enable_ai_chat', '1', 'boolean', 'Aktifkan AI chat'),
('enable_audio_guide', '1', 'boolean', 'Aktifkan audio guide');
```

---

## 7. CATATAN IMPLEMENTASI

- Gunakan `BIGINT UNSIGNED` untuk semua PK untuk antisipasi skala besar
- `utf8mb4` untuk support emoji dan karakter multibyte
- `ON DELETE CASCADE` untuk relasi parent-child (user → profile)
- `ON DELETE SET NULL` untuk relasi optional (destination → booking)
- `ON DELETE RESTRICT` untuk mencegah hapus data yang masih direferensi (ticket → order_item)
- Timestamp `created_at` dan `updated_at` wajib di semua tabel
- Kolom `is_active` / `is_approved` untuk soft delete / approval flow
- Kolom `latitude/longitude` dengan `DECIMAL(10,7)` untuk akurasi GPS

---

> **Modul Selanjutnya:** `06_KAMUS_DATA_DATABASE.md` — Kamus data lengkap untuk setiap tabel
