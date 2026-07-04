-- =====================================================
-- DATABASE SAHAM - Struktur Database untuk Aplikasi Trading (OPTIMIZED)
-- =====================================================

-- Membuat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS db_saham;
USE db_saham;

-- =====================================================
-- TABEL USERS (Pengguna)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    id_card VARCHAR(20) UNIQUE,
    bank_account VARCHAR(50),
    bank_name VARCHAR(100),
    account_holder VARCHAR(100),
    balance DECIMAL(15,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL MARKET SECTORS
-- =====================================================
CREATE TABLE market_sectors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sector_name VARCHAR(100) UNIQUE NOT NULL,
    sector_code VARCHAR(10) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL INDUSTRIES
-- =====================================================
CREATE TABLE industries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sector_id INT NOT NULL,
    industry_name VARCHAR(100) NOT NULL,
    industry_code VARCHAR(10) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sector_id) REFERENCES market_sectors(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL STOCKS (Daftar Saham) - OPTIMIZED
-- =====================================================
CREATE TABLE stocks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    symbol VARCHAR(10) UNIQUE NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    sector_id INT,
    industry_id INT,
    market_cap BIGINT,
    current_price DECIMAL(10,2),
    previous_close DECIMAL(10,2),
    day_high DECIMAL(10,2),
    day_low DECIMAL(10,2),
    volume BIGINT DEFAULT 0,
    market VARCHAR(50) DEFAULT 'IDX',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sector_id) REFERENCES market_sectors(id) ON DELETE SET NULL,
    FOREIGN KEY (industry_id) REFERENCES industries(id) ON DELETE SET NULL
);

-- =====================================================
-- TABEL PORTFOLIO (Portfolio User)
-- =====================================================
CREATE TABLE portfolio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    average_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_investment DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    current_value DECIMAL(15,2) DEFAULT 0.00,
    unrealized_pnl DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_stock (user_id, stock_id)
);

-- =====================================================
-- TABEL TRANSACTIONS (Transaksi Trading)
-- =====================================================
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    transaction_type ENUM('BUY', 'SELL') NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    fees DECIMAL(10,2) DEFAULT 0.00,
    net_amount DECIMAL(15,2) NOT NULL,
    status ENUM('PENDING', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    order_type ENUM('MARKET', 'LIMIT', 'STOP_LOSS', 'TAKE_PROFIT') DEFAULT 'MARKET',
    limit_price DECIMAL(10,2),
    stop_price DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL WATCHLIST (Daftar Pantauan)
-- =====================================================
CREATE TABLE watchlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    target_price DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_stock_watchlist (user_id, stock_id)
);

-- =====================================================
-- TABEL PRICE_HISTORY (Riwayat Harga)
-- =====================================================
CREATE TABLE price_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    volume BIGINT DEFAULT 0,
    high DECIMAL(10,2),
    low DECIMAL(10,2),
    open_price DECIMAL(10,2),
    close_price DECIMAL(10,2),
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    INDEX idx_stock_date (stock_id, date)
);

-- =====================================================
-- TABEL BALANCE_HISTORY (Riwayat Saldo)
-- =====================================================
CREATE TABLE balance_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    transaction_type ENUM('DEPOSIT', 'WITHDRAWAL', 'TRADING', 'DIVIDEND', 'FEE') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_before DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description TEXT,
    reference_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL DIVIDENDS (Dividen)
-- =====================================================
CREATE TABLE dividends (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    dividend_per_share DECIMAL(10,4) NOT NULL,
    ex_date DATE NOT NULL,
    record_date DATE NOT NULL,
    payment_date DATE NOT NULL,
    dividend_type ENUM('CASH', 'STOCK') DEFAULT 'CASH',
    status ENUM('ANNOUNCED', 'EX_DIVIDEND', 'PAID') DEFAULT 'ANNOUNCED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL USER_DIVIDENDS (Dividen User)
-- =====================================================
CREATE TABLE user_dividends (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    dividend_id INT NOT NULL,
    shares_owned INT NOT NULL,
    dividend_amount DECIMAL(15,2) NOT NULL,
    status ENUM('PENDING', 'PAID') DEFAULT 'PENDING',
    paid_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dividend_id) REFERENCES dividends(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL NEWS (Berita Saham)
-- =====================================================
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(500) NOT NULL,
    content TEXT NOT NULL,
    summary TEXT,
    author VARCHAR(100),
    source VARCHAR(100),
    url VARCHAR(500),
    published_at TIMESTAMP,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL STOCK_NEWS (Berita per Saham)
-- =====================================================
CREATE TABLE stock_news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    news_id INT NOT NULL,
    relevance_score DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL ANALISIS TEKNIKAL
-- =====================================================
CREATE TABLE technical_indicators (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    date DATE NOT NULL,
    sma_5 DECIMAL(10,2),
    sma_10 DECIMAL(10,2),
    sma_20 DECIMAL(10,2),
    sma_50 DECIMAL(10,2),
    sma_200 DECIMAL(10,2),
    ema_12 DECIMAL(10,2),
    ema_26 DECIMAL(10,2),
    rsi_14 DECIMAL(5,2),
    macd DECIMAL(10,4),
    macd_signal DECIMAL(10,4),
    macd_histogram DECIMAL(10,4),
    bollinger_upper DECIMAL(10,2),
    bollinger_middle DECIMAL(10,2),
    bollinger_lower DECIMAL(10,2),
    stoch_k DECIMAL(5,2),
    stoch_d DECIMAL(5,2),
    williams_r DECIMAL(5,2),
    atr DECIMAL(10,2),
    adx DECIMAL(5,2),
    cci DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    INDEX idx_stock_date_tech (stock_id, date)
);

-- =====================================================
-- TABEL CHART PATTERNS
-- =====================================================
CREATE TABLE chart_patterns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    pattern_type ENUM('HEAD_SHOULDERS', 'DOUBLE_TOP', 'DOUBLE_BOTTOM', 'TRIANGLE', 'WEDGE', 'FLAG', 'PENNANT', 'CUP_HANDLE') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    confirmation_date DATE,
    pattern_strength DECIMAL(3,2) DEFAULT 0.00,
    target_price DECIMAL(10,2),
    stop_loss DECIMAL(10,2),
    status ENUM('FORMING', 'CONFIRMED', 'BROKEN', 'COMPLETED') DEFAULT 'FORMING',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL SUPPORT RESISTANCE
-- =====================================================
CREATE TABLE support_resistance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    level_type ENUM('SUPPORT', 'RESISTANCE') NOT NULL,
    price_level DECIMAL(10,2) NOT NULL,
    strength INT DEFAULT 1,
    touches_count INT DEFAULT 1,
    first_touch_date DATE NOT NULL,
    last_touch_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    INDEX idx_stock_level (stock_id, price_level)
);

-- =====================================================
-- TABEL SENTIMENT ANALYSIS
-- =====================================================
CREATE TABLE sentiment_analysis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    news_id INT,
    sentiment_score DECIMAL(3,2) NOT NULL, -- -1.00 to 1.00
    sentiment_label ENUM('VERY_NEGATIVE', 'NEGATIVE', 'NEUTRAL', 'POSITIVE', 'VERY_POSITIVE') NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL, -- 0.00 to 1.00
    source_type ENUM('NEWS', 'SOCIAL_MEDIA', 'ANALYST', 'USER_COMMENT') NOT NULL,
    source_url VARCHAR(500),
    analysis_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE SET NULL
);

-- =====================================================
-- TABEL RATIO KEUANGAN
-- =====================================================
CREATE TABLE financial_ratios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    period_year INT NOT NULL,
    period_quarter INT,
    pe_ratio DECIMAL(8,2),
    pb_ratio DECIMAL(8,2),
    ps_ratio DECIMAL(8,2),
    peg_ratio DECIMAL(8,2),
    roe DECIMAL(8,2),
    roa DECIMAL(8,2),
    roic DECIMAL(8,2),
    gross_margin DECIMAL(8,2),
    operating_margin DECIMAL(8,2),
    net_margin DECIMAL(8,2),
    debt_to_equity DECIMAL(8,2),
    current_ratio DECIMAL(8,2),
    quick_ratio DECIMAL(8,2),
    interest_coverage DECIMAL(8,2),
    dividend_yield DECIMAL(8,2),
    payout_ratio DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stock_period (stock_id, period_year, period_quarter)
);

-- =====================================================
-- TABEL ALERTS & NOTIFICATIONS
-- =====================================================
CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    alert_type ENUM('PRICE_ABOVE', 'PRICE_BELOW', 'VOLUME_SPIKE', 'TECHNICAL_SIGNAL', 'NEWS_ALERT', 'DIVIDEND_ALERT') NOT NULL,
    condition_value DECIMAL(10,2),
    condition_text TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    is_triggered BOOLEAN DEFAULT FALSE,
    triggered_at TIMESTAMP NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL TRADING STRATEGIES
-- =====================================================
CREATE TABLE trading_strategies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    strategy_name VARCHAR(100) NOT NULL,
    strategy_type ENUM('SCALPING', 'DAY_TRADING', 'SWING_TRADING', 'POSITION_TRADING', 'ARBITRAGE', 'MOMENTUM', 'MEAN_REVERSION') NOT NULL,
    description TEXT,
    entry_rules TEXT,
    exit_rules TEXT,
    risk_management TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    performance_score DECIMAL(5,2) DEFAULT 0.00,
    total_trades INT DEFAULT 0,
    winning_trades INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL STRATEGY EXECUTIONS
-- =====================================================
CREATE TABLE strategy_executions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    strategy_id INT NOT NULL,
    stock_id INT NOT NULL,
    transaction_id INT,
    execution_type ENUM('ENTRY', 'EXIT', 'STOP_LOSS', 'TAKE_PROFIT') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    execution_reason TEXT,
    pnl DECIMAL(15,2) DEFAULT 0.00,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (strategy_id) REFERENCES trading_strategies(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL
);

-- =====================================================
-- TABEL SETTINGS (Pengaturan Aplikasi)
-- =====================================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL USER_SETTINGS (Pengaturan User)
-- =====================================================
CREATE TABLE user_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_setting (user_id, setting_key)
);

-- =====================================================
-- INSERT DATA SAMPLE
-- =====================================================

-- Insert sample sectors
INSERT INTO market_sectors (sector_name, sector_code, description) VALUES
('Financial Services', 'FIN', 'Banking, insurance, and financial services'),
('Technology', 'TECH', 'Software, hardware, and technology companies'),
('Consumer Staples', 'CONS', 'Food, beverages, and household products'),
('Consumer Discretionary', 'DISC', 'Automotive, retail, and entertainment'),
('Healthcare', 'HEALTH', 'Pharmaceuticals, medical devices, and healthcare services'),
('Energy', 'ENERGY', 'Oil, gas, and renewable energy companies'),
('Materials', 'MAT', 'Mining, chemicals, and construction materials'),
('Telecommunications', 'TELECOM', 'Telecom services and infrastructure'),
('Utilities', 'UTIL', 'Electric, gas, and water utilities'),
('Real Estate', 'RE', 'Real estate investment and development');

-- Insert sample industries
INSERT INTO industries (sector_id, industry_name, industry_code, description) VALUES
(1, 'Banks', 'BANK', 'Commercial and investment banks'),
(1, 'Insurance', 'INS', 'Life and general insurance companies'),
(2, 'Software', 'SOFT', 'Software development and services'),
(2, 'Hardware', 'HARD', 'Computer hardware and electronics'),
(3, 'Food Products', 'FOOD', 'Food manufacturing and processing'),
(3, 'Beverages', 'BEV', 'Beverage manufacturing and distribution'),
(4, 'Automotive', 'AUTO', 'Vehicle manufacturing and parts'),
(4, 'Retail', 'RETAIL', 'Consumer retail and e-commerce'),
(5, 'Pharmaceuticals', 'PHARMA', 'Drug development and manufacturing'),
(5, 'Medical Devices', 'MEDDEV', 'Medical equipment and devices'),
(6, 'Oil & Gas', 'OILGAS', 'Oil and gas exploration and production'),
(6, 'Renewable Energy', 'RENEW', 'Solar, wind, and clean energy'),
(7, 'Metals & Mining', 'MINING', 'Metal mining and processing'),
(7, 'Chemicals', 'CHEM', 'Chemical manufacturing and distribution'),
(8, 'Telecom Services', 'TELECOM', 'Telecommunications services'),
(8, 'Telecom Equipment', 'TELEQ', 'Telecommunications equipment'),
(9, 'Electric Utilities', 'ELEC', 'Electric power generation and distribution'),
(9, 'Gas Utilities', 'GAS', 'Natural gas distribution'),
(10, 'REITs', 'REIT', 'Real Estate Investment Trusts'),
(10, 'Real Estate Development', 'REDEV', 'Property development and management');

-- Insert 100 saham populer IDX dengan kategorisasi lengkap
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
-- FINANCIAL SERVICES (10 saham)
('BBCA', 'Bank Central Asia Tbk', 1, 1, 9500.00, 9450.00, 500000000000000, 'IDX'),
('BBRI', 'Bank Rakyat Indonesia Tbk', 1, 1, 4800.00, 4750.00, 200000000000000, 'IDX'),
('BMRI', 'Bank Mandiri Tbk', 1, 1, 6200.00, 6150.00, 300000000000000, 'IDX'),
('BNGA', 'Bank CIMB Niaga Tbk', 1, 1, 1800.00, 1750.00, 50000000000000, 'IDX'),
('BBNI', 'Bank Negara Indonesia Tbk', 1, 1, 5200.00, 5150.00, 150000000000000, 'IDX'),
('BJTM', 'Bank Pembangunan Daerah Jawa Timur Tbk', 1, 1, 1200.00, 1180.00, 15000000000000, 'IDX'),
('BJBR', 'Bank Pembangunan Daerah Jawa Barat Tbk', 1, 1, 1100.00, 1080.00, 12000000000000, 'IDX'),
('BTPN', 'Bank BTPN Tbk', 1, 1, 2400.00, 2350.00, 25000000000000, 'IDX'),
('MEGA', 'Bank Mega Tbk', 1, 1, 800.00, 780.00, 8000000000000, 'IDX'),
('PNBN', 'Bank Panin Tbk', 1, 1, 600.00, 580.00, 5000000000000, 'IDX'),

-- TECHNOLOGY (10 saham)
('TLKM', 'Telkom Indonesia Tbk', 8, 15, 3500.00, 3480.00, 400000000000000, 'IDX'),
('EXCL', 'XL Axiata Tbk', 8, 15, 2800.00, 2750.00, 80000000000000, 'IDX'),
('ISAT', 'Indosat Ooredoo Hutchison Tbk', 8, 15, 3200.00, 3150.00, 90000000000000, 'IDX'),
('FREN', 'Smartfren Telecom Tbk', 8, 15, 150.00, 145.00, 5000000000000, 'IDX'),
('GOTO', 'GoTo Gojek Tokopedia Tbk', 2, 3, 120.00, 115.00, 15000000000000, 'IDX'),
('EMTK', 'Elang Mahkota Teknologi Tbk', 2, 3, 800.00, 780.00, 20000000000000, 'IDX'),
('BUKA', 'Bukalapak.com Tbk', 2, 3, 200.00, 195.00, 8000000000000, 'IDX'),
('ARTO', 'Bank Jago Tbk', 1, 1, 3000.00, 2950.00, 25000000000000, 'IDX'),
('DMMX', 'Digital Mediatama Maxima Tbk', 2, 3, 50.00, 48.00, 1000000000000, 'IDX'),
('TECH', 'Indo Kordsa Tbk', 2, 4, 1200.00, 1180.00, 8000000000000, 'IDX'),

-- CONSUMER STAPLES (10 saham)
('UNVR', 'Unilever Indonesia Tbk', 3, 5, 2800.00, 2750.00, 120000000000000, 'IDX'),
('ICBP', 'Indofood CBP Sukses Makmur Tbk', 3, 5, 12000.00, 11950.00, 150000000000000, 'IDX'),
('INDF', 'Indofood Sukses Makmur Tbk', 3, 5, 6500.00, 6450.00, 80000000000000, 'IDX'),
('INCI', 'Inti Agri Resources Tbk', 3, 5, 500.00, 480.00, 3000000000000, 'IDX'),
('AISA', 'FKS Food Sejahtera Tbk', 3, 5, 200.00, 195.00, 2000000000000, 'IDX'),
('MLBI', 'Multi Bintang Indonesia Tbk', 3, 6, 1500.00, 1480.00, 5000000000000, 'IDX'),
('ULTJ', 'Ultra Jaya Milk Industry Tbk', 3, 6, 1800.00, 1750.00, 4000000000000, 'IDX'),
('CLEO', 'Sariguna Primatirta Tbk', 3, 6, 300.00, 295.00, 1500000000000, 'IDX'),
('ADES', 'Akasha Wira International Tbk', 3, 6, 800.00, 780.00, 2000000000000, 'IDX'),
('SIDO', 'Sido Muncul Tbk', 3, 5, 1200.00, 1180.00, 3000000000000, 'IDX'),

-- CONSUMER DISCRETIONARY (10 saham)
('ASII', 'Astra International Tbk', 4, 7, 7200.00, 7150.00, 200000000000000, 'IDX'),
('AUTO', 'Astra Otoparts Tbk', 4, 7, 1200.00, 1180.00, 15000000000000, 'IDX'),
('ADRO', 'Adaro Energy Tbk', 6, 11, 3500.00, 3450.00, 80000000000000, 'IDX'),
('AKRA', 'AKR Corporindo Tbk', 4, 8, 800.00, 780.00, 5000000000000, 'IDX'),
('BIRD', 'Blue Bird Tbk', 4, 7, 1200.00, 1180.00, 8000000000000, 'IDX'),
('BRIS', 'Bank Syariah Indonesia Tbk', 1, 1, 1800.00, 1750.00, 40000000000000, 'IDX'),
('CARS', 'Industri dan Perdagangan Bintraco Dharma Tbk', 4, 7, 200.00, 195.00, 2000000000000, 'IDX'),
('CTRA', 'Ciputra Development Tbk', 10, 19, 1200.00, 1180.00, 15000000000000, 'IDX'),
('DART', 'Duta Anggada Realty Tbk', 10, 19, 100.00, 95.00, 1000000000000, 'IDX'),
('ERAA', 'Erajaya Swasembada Tbk', 4, 8, 800.00, 780.00, 5000000000000, 'IDX'),

-- HEALTHCARE (10 saham)
('KLBF', 'Kalbe Farma Tbk', 5, 9, 1500.00, 1480.00, 50000000000000, 'IDX'),
('SILO', 'Siloam International Hospitals Tbk', 5, 10, 1200.00, 1180.00, 15000000000000, 'IDX'),
('MERK', 'Merck Tbk', 5, 9, 800.00, 780.00, 2000000000000, 'IDX'),
('TSPC', 'Tempo Scan Pacific Tbk', 5, 9, 400.00, 395.00, 1500000000000, 'IDX'),
('PRDA', 'Prodia Widyahusada Tbk', 5, 10, 2000.00, 1950.00, 3000000000000, 'IDX'),
('HEAL', 'Medikaloka Hermina Tbk', 5, 10, 800.00, 780.00, 2000000000000, 'IDX'),
('RSGK', 'Lippo Karawaci Tbk', 10, 19, 200.00, 195.00, 5000000000000, 'IDX'),
('HDFA', 'Radiance Medan Multifinance Tbk', 1, 1, 100.00, 95.00, 500000000000, 'IDX'),
('HEXA', 'Hexindo Adiperkasa Tbk', 4, 7, 500.00, 480.00, 1000000000000, 'IDX'),
('DVLA', 'Darya Varia Laboratoria Tbk', 5, 9, 300.00, 295.00, 800000000000, 'IDX'),

-- ENERGY (10 saham)
('PGAS', 'Perusahaan Gas Negara Tbk', 6, 11, 1800.00, 1750.00, 40000000000000, 'IDX'),
('PERT', 'Pertamina (Persero) Tbk', 6, 11, 1500.00, 1480.00, 300000000000000, 'IDX'),
('PTBA', 'Bukit Asam Tbk', 6, 11, 2500.00, 2450.00, 50000000000000, 'IDX'),
('ITMG', 'Indo Tambangraya Megah Tbk', 6, 11, 4500.00, 4450.00, 60000000000000, 'IDX'),
('BUMI', 'Bumi Resources Tbk', 6, 11, 800.00, 780.00, 15000000000000, 'IDX'),
('MEDC', 'Medco Energi Internasional Tbk', 6, 11, 1200.00, 1180.00, 20000000000000, 'IDX'),
('TOBA', 'Toba Pulp Lestari Tbk', 6, 12, 500.00, 480.00, 3000000000000, 'IDX'),
('BSSR', 'Baramulti Suksessarana Tbk', 6, 11, 400.00, 395.00, 2000000000000, 'IDX'),
('GEMS', 'Golden Energy Mines Tbk', 6, 11, 600.00, 580.00, 4000000000000, 'IDX'),
('HRUM', 'Harum Energy Tbk', 6, 11, 700.00, 680.00, 3000000000000, 'IDX'),

-- MATERIALS (10 saham)
('ANTM', 'Aneka Tambang Tbk', 7, 13, 1200.00, 1180.00, 30000000000000, 'IDX'),
('INCO', 'Vale Indonesia Tbk', 7, 13, 4500.00, 4450.00, 40000000000000, 'IDX'),
('SMGR', 'Semen Indonesia Tbk', 7, 14, 3500.00, 3450.00, 50000000000000, 'IDX'),
('INTP', 'Indocement Tunggal Prakarsa Tbk', 7, 14, 1800.00, 1750.00, 20000000000000, 'IDX'),
('SMCB', 'Holcim Indonesia Tbk', 7, 14, 1200.00, 1180.00, 15000000000000, 'IDX'),
('TPIA', 'Chandra Asri Pacific Tbk', 7, 14, 800.00, 780.00, 8000000000000, 'IDX'),
('TKIM', 'Pabrik Kertas Tjiwi Kimia Tbk', 7, 14, 600.00, 580.00, 5000000000000, 'IDX'),
('INKP', 'Indah Kiat Pulp & Paper Tbk', 7, 14, 400.00, 395.00, 3000000000000, 'IDX'),
('WIKA', 'Wijaya Karya Tbk', 7, 14, 200.00, 195.00, 2000000000000, 'IDX'),
('ADHI', 'Adhi Karya Tbk', 7, 14, 300.00, 295.00, 1500000000000, 'IDX'),

-- TELECOMMUNICATIONS (10 saham)
('TELK', 'Telkomsel Tbk', 8, 15, 2000.00, 1950.00, 100000000000000, 'IDX'),
('HITS', 'Humpuss Intermoda Transportasi Tbk', 8, 15, 100.00, 95.00, 500000000000, 'IDX'),
('TINS', 'Timah Tbk', 7, 13, 800.00, 780.00, 3000000000000, 'IDX'),
('UNTR', 'United Tractors Tbk', 4, 7, 2500.00, 2450.00, 30000000000000, 'IDX'),
('TOWR', 'Sarana Menara Nusantara Tbk', 8, 16, 500.00, 480.00, 2000000000000, 'IDX'),
('TOPS', 'Totalindo Eka Persada Tbk', 8, 16, 200.00, 195.00, 1000000000000, 'IDX'),
('TCPI', 'Transcoal Pacific Tbk', 8, 15, 150.00, 145.00, 800000000000, 'IDX'),
('TURI', 'Tunas Ridean Tbk', 4, 7, 300.00, 295.00, 1200000000000, 'IDX'),
('TOTL', 'Total Bangun Persada Tbk', 7, 14, 400.00, 395.00, 1000000000000, 'IDX'),
('TOTO', 'Surya Toto Indonesia Tbk', 7, 14, 600.00, 580.00, 2000000000000, 'IDX'),

-- UTILITIES (10 saham)
('PLN', 'Perusahaan Listrik Negara Tbk', 9, 17, 1000.00, 980.00, 200000000000000, 'IDX'),
('POWR', 'Cikarang Listrindo Tbk', 9, 17, 800.00, 780.00, 5000000000000, 'IDX'),
('PAMG', 'Pam Mineral Tbk', 9, 17, 200.00, 195.00, 1000000000000, 'IDX'),
('PTPP', 'PP Tbk', 7, 14, 300.00, 295.00, 2000000000000, 'IDX'),
('JSMR', 'Jasa Marga Tbk', 4, 7, 1200.00, 1180.00, 15000000000000, 'IDX'),
('JAST', 'Jasnita Telekomindo Tbk', 8, 15, 100.00, 95.00, 500000000000, 'IDX'),
('GGRM', 'Gudang Garam Tbk', 3, 5, 50000.00, 49500.00, 100000000000000, 'IDX'),
('HMSP', 'H.M. Sampoerna Tbk', 3, 5, 800.00, 780.00, 20000000000000, 'IDX'),
('WIKA', 'Wijaya Karya Tbk', 7, 14, 200.00, 195.00, 2000000000000, 'IDX'),
('ADHI', 'Adhi Karya Tbk', 7, 14, 300.00, 295.00, 1500000000000, 'IDX'),

-- REAL ESTATE (10 saham)
('ASRI', 'Alam Sutera Realty Tbk', 10, 19, 300.00, 295.00, 2000000000000, 'IDX'),
('BSDE', 'Bumi Serpong Damai Tbk', 10, 19, 400.00, 395.00, 3000000000000, 'IDX'),
('LPCK', 'Lippo Cikarang Tbk', 10, 19, 150.00, 145.00, 800000000000, 'IDX'),
('SMRA', 'Summarecon Agung Tbk', 10, 19, 500.00, 480.00, 2500000000000, 'IDX'),
('KIJA', 'Kawasan Industri Jababeka Tbk', 10, 20, 200.00, 195.00, 1000000000000, 'IDX'),
('PWON', 'Pakuwon Jati Tbk', 10, 19, 600.00, 580.00, 2000000000000, 'IDX'),
('BSSR', 'Baramulti Suksessarana Tbk', 6, 11, 400.00, 395.00, 2000000000000, 'IDX'),
('LPPF', 'Lippo Cikarang Tbk', 10, 19, 120.00, 115.00, 600000000000, 'IDX'),
('META', 'Nusantara Infrastructure Tbk', 10, 19, 300.00, 295.00, 1000000000000, 'IDX'),
('POWR', 'Cikarang Listrindo Tbk', 9, 17, 800.00, 780.00, 5000000000000, 'IDX');

-- =====================================================
-- TAMBAHKAN KOLOM MARKET_CAP_CATEGORY
-- =====================================================
ALTER TABLE stocks ADD COLUMN IF NOT EXISTS market_cap_category VARCHAR(20);

-- =====================================================
-- UPDATE MARKET CAP CATEGORIES
-- =====================================================
UPDATE stocks SET 
    market_cap_category = CASE 
        WHEN market_cap >= 100000000000000 THEN 'Mega Cap'
        WHEN market_cap >= 10000000000000 THEN 'Large Cap'
        WHEN market_cap >= 2000000000000 THEN 'Mid Cap'
        ELSE 'Small Cap'
    END;

-- =====================================================
-- INSERT SAMPLE IPO DATA
-- =====================================================
CREATE TABLE IF NOT EXISTS ipo_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    ipo_date DATE NOT NULL,
    ipo_price DECIMAL(10,2) NOT NULL,
    listing_date DATE NOT NULL,
    underwriter VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE
);

-- Insert IPO data untuk 20 saham utama
INSERT INTO ipo_data (stock_id, ipo_date, ipo_price, listing_date, underwriter) VALUES
(1, '2000-05-15', 500.00, '2000-05-15', 'Mandiri Sekuritas'),
(2, '2003-11-10', 200.00, '2003-11-10', 'Danareksa Sekuritas'),
(3, '2003-07-15', 300.00, '2003-07-15', 'Mandiri Sekuritas'),
(4, '2006-12-18', 100.00, '2006-12-18', 'CIMB Sekuritas'),
(5, '2010-05-25', 250.00, '2010-05-25', 'Danareksa Sekuritas'),
(6, '2011-03-15', 50.00, '2011-03-15', 'Mandiri Sekuritas'),
(7, '2012-08-20', 60.00, '2012-08-20', 'Danareksa Sekuritas'),
(8, '2014-11-10', 120.00, '2014-11-10', 'CIMB Sekuritas'),
(9, '2015-06-15', 40.00, '2015-06-15', 'Mandiri Sekuritas'),
(10, '2016-09-20', 30.00, '2016-09-20', 'Danareksa Sekuritas'),
(11, '2017-03-10', 350.00, '2017-03-10', 'Mandiri Sekuritas'),
(12, '2018-06-15', 280.00, '2018-06-15', 'Danareksa Sekuritas'),
(13, '2019-09-20', 320.00, '2019-09-20', 'CIMB Sekuritas'),
(14, '2020-12-05', 150.00, '2020-12-05', 'Mandiri Sekuritas'),
(15, '2021-04-10', 120.00, '2021-04-10', 'Danareksa Sekuritas'),
(16, '2022-07-15', 200.00, '2022-07-15', 'CIMB Sekuritas'),
(17, '2023-01-20', 180.00, '2023-01-20', 'Mandiri Sekuritas'),
(18, '2023-08-10', 300.00, '2023-08-10', 'Danareksa Sekuritas'),
(19, '2024-02-15', 50.00, '2024-02-15', 'CIMB Sekuritas'),
(20, '2024-05-20', 120.00, '2024-05-20', 'Mandiri Sekuritas');

-- =====================================================
-- INSERT SAMPLE PRICE HISTORY
-- =====================================================
-- Insert sample price history untuk 20 saham pertama
INSERT INTO price_history (stock_id, price, volume, high, low, open_price, close_price, date) VALUES
(1, 9500.00, 1000000, 9600.00, 9400.00, 9450.00, 9500.00, CURDATE()),
(2, 4800.00, 2000000, 4850.00, 4750.00, 4750.00, 4800.00, CURDATE()),
(3, 6200.00, 1500000, 6250.00, 6150.00, 6150.00, 6200.00, CURDATE()),
(4, 1800.00, 500000, 1850.00, 1750.00, 1750.00, 1800.00, CURDATE()),
(5, 5200.00, 800000, 5250.00, 5150.00, 5150.00, 5200.00, CURDATE()),
(6, 1200.00, 300000, 1250.00, 1180.00, 1180.00, 1200.00, CURDATE()),
(7, 1100.00, 400000, 1150.00, 1080.00, 1080.00, 1100.00, CURDATE()),
(8, 2400.00, 600000, 2450.00, 2350.00, 2350.00, 2400.00, CURDATE()),
(9, 800.00, 200000, 850.00, 780.00, 780.00, 800.00, CURDATE()),
(10, 600.00, 150000, 650.00, 580.00, 580.00, 600.00, CURDATE()),
(11, 3500.00, 1200000, 3550.00, 3480.00, 3480.00, 3500.00, CURDATE()),
(12, 2800.00, 900000, 2850.00, 2750.00, 2750.00, 2800.00, CURDATE()),
(13, 3200.00, 1100000, 3250.00, 3150.00, 3150.00, 3200.00, CURDATE()),
(14, 150.00, 200000, 160.00, 145.00, 145.00, 150.00, CURDATE()),
(15, 120.00, 300000, 125.00, 115.00, 115.00, 120.00, CURDATE()),
(16, 200.00, 400000, 210.00, 195.00, 195.00, 200.00, CURDATE()),
(17, 3000.00, 500000, 3050.00, 2950.00, 2950.00, 3000.00, CURDATE()),
(18, 50.00, 100000, 55.00, 48.00, 48.00, 50.00, CURDATE()),
(19, 1200.00, 300000, 1250.00, 1180.00, 1180.00, 1200.00, CURDATE()),
(20, 2800.00, 800000, 2850.00, 2750.00, 2750.00, 2800.00, CURDATE());

-- =====================================================
-- INSERT SAMPLE TECHNICAL INDICATORS
-- =====================================================
-- Insert sample technical indicators untuk 20 saham pertama
INSERT INTO technical_indicators (stock_id, date, sma_5, sma_20, rsi_14, macd, bollinger_upper, bollinger_lower, created_at)
SELECT 
    s.id,
    CURDATE(),
    s.current_price * (1 + (RAND() - 0.5) * 0.1),
    s.current_price * (1 + (RAND() - 0.5) * 0.15),
    RAND() * 60 + 20,
    (RAND() - 0.5) * 2,
    s.current_price * 1.1,
    s.current_price * 0.9,
    NOW()
FROM stocks s
WHERE s.id <= 20
ON DUPLICATE KEY UPDATE
    sma_5 = VALUES(sma_5),
    sma_20 = VALUES(sma_20),
    rsi_14 = VALUES(rsi_14),
    macd = VALUES(macd),
    bollinger_upper = VALUES(bollinger_upper),
    bollinger_lower = VALUES(bollinger_lower),
    updated_at = NOW();

-- =====================================================
-- INSERT SAMPLE FINANCIAL RATIOS
-- =====================================================
-- Insert sample financial ratios untuk 20 saham pertama
INSERT INTO financial_ratios (stock_id, period_year, period_quarter, pe_ratio, pb_ratio, roe, roa, debt_to_equity, current_ratio, dividend_yield, created_at)
SELECT 
    s.id,
    YEAR(CURDATE()),
    CEIL(MONTH(CURDATE()) / 3),
    RAND() * 45 + 5,
    RAND() * 9 + 1,
    RAND() * 20 + 5,
    RAND() * 13 + 2,
    RAND() * 2,
    RAND() * 2 + 1,
    RAND() * 5,
    NOW()
FROM stocks s
WHERE s.id <= 20
ON DUPLICATE KEY UPDATE
    pe_ratio = VALUES(pe_ratio),
    pb_ratio = VALUES(pb_ratio),
    roe = VALUES(roe),
    roa = VALUES(roa),
    debt_to_equity = VALUES(debt_to_equity),
    current_ratio = VALUES(current_ratio),
    dividend_yield = VALUES(dividend_yield),
    updated_at = NOW();

-- =====================================================
-- INSERT SAMPLE SENTIMENT ANALYSIS
-- =====================================================
-- Insert sample sentiment analysis untuk 20 saham pertama
INSERT INTO sentiment_analysis (stock_id, sentiment_score, sentiment_label, confidence_score, source_type, analysis_date, created_at)
SELECT 
    s.id,
    (RAND() - 0.5) * 2,
    CASE 
        WHEN RAND() < 0.2 THEN 'VERY_NEGATIVE'
        WHEN RAND() < 0.4 THEN 'NEGATIVE'
        WHEN RAND() < 0.6 THEN 'NEUTRAL'
        WHEN RAND() < 0.8 THEN 'POSITIVE'
        ELSE 'VERY_POSITIVE'
    END,
    RAND() * 0.5 + 0.5,
    CASE 
        WHEN RAND() < 0.3 THEN 'NEWS'
        WHEN RAND() < 0.6 THEN 'SOCIAL_MEDIA'
        WHEN RAND() < 0.8 THEN 'ANALYST'
        ELSE 'USER_COMMENT'
    END,
    NOW(),
    NOW()
FROM stocks s
WHERE s.id <= 20;

-- Insert sample settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('trading_fee_percentage', '0.25', 'Trading fee dalam persen'),
('min_trading_amount', '100000', 'Minimum amount untuk trading'),
('max_daily_trading', '1000000000', 'Maximum daily trading limit'),
('market_hours_start', '09:00:00', 'Jam buka pasar'),
('market_hours_end', '16:00:00', 'Jam tutup pasar'),
('maintenance_mode', 'false', 'Mode maintenance aplikasi'),
('app_version', '1.0.0', 'Versi aplikasi'),
('api_rate_limit', '1000', 'Rate limit API per jam');

-- =====================================================
-- INDEXES untuk Performance
-- =====================================================

-- Index untuk transactions
CREATE INDEX idx_transactions_user_date ON transactions(user_id, created_at);
CREATE INDEX idx_transactions_stock_date ON transactions(stock_id, created_at);
CREATE INDEX idx_transactions_status ON transactions(status);

-- Index untuk price_history
CREATE INDEX idx_price_history_stock_date ON price_history(stock_id, date);

-- Index untuk balance_history
CREATE INDEX idx_balance_history_user_date ON balance_history(user_id, created_at);

-- Index untuk news
CREATE INDEX idx_news_published ON news(published_at);
CREATE INDEX idx_news_featured ON news(is_featured, is_active);

-- Index untuk technical analysis
CREATE INDEX idx_technical_indicators_stock_date ON technical_indicators(stock_id, date);
CREATE INDEX idx_chart_patterns_stock ON chart_patterns(stock_id, pattern_type);
CREATE INDEX idx_sentiment_stock_date ON sentiment_analysis(stock_id, analysis_date);
CREATE INDEX idx_financial_ratios_stock_period ON financial_ratios(stock_id, period_year, period_quarter);
CREATE INDEX idx_alerts_user_active ON alerts(user_id, is_active);
CREATE INDEX idx_strategy_executions_strategy ON strategy_executions(strategy_id, executed_at);

-- =====================================================
-- VIEWS untuk Reporting
-- =====================================================

-- View untuk portfolio summary
CREATE VIEW portfolio_summary AS
SELECT 
    u.id as user_id,
    u.username,
    u.full_name,
    COUNT(p.id) as total_stocks,
    SUM(p.current_value) as total_portfolio_value,
    SUM(p.unrealized_pnl) as total_unrealized_pnl,
    u.balance as available_balance
FROM users u
LEFT JOIN portfolio p ON u.id = p.user_id
GROUP BY u.id, u.username, u.full_name, u.balance;

-- View untuk top gainers
CREATE VIEW top_gainers AS
SELECT 
    s.symbol,
    s.company_name,
    s.current_price,
    s.previous_close,
    ROUND(((s.current_price - s.previous_close) / s.previous_close) * 100, 2) as percentage_change
FROM stocks s
WHERE s.is_active = TRUE
ORDER BY percentage_change DESC;

-- View untuk top losers
CREATE VIEW top_losers AS
SELECT 
    s.symbol,
    s.company_name,
    s.current_price,
    s.previous_close,
    ROUND(((s.current_price - s.previous_close) / s.previous_close) * 100, 2) as percentage_change
FROM stocks s
WHERE s.is_active = TRUE
ORDER BY percentage_change ASC;

-- View untuk market summary
CREATE VIEW market_summary AS
SELECT 
    COUNT(DISTINCT s.id) as total_stocks,
    COUNT(DISTINCT t.id) as total_transactions,
    SUM(t.total_amount) as total_volume,
    AVG(s.current_price) as average_price,
    MAX(s.current_price) as highest_price,
    MIN(s.current_price) as lowest_price
FROM stocks s
LEFT JOIN transactions t ON s.id = t.stock_id
WHERE s.is_active = TRUE;

-- View untuk analisis teknikal summary
CREATE VIEW technical_analysis_summary AS
SELECT 
    s.symbol,
    s.company_name,
    ti.date,
    ti.sma_20,
    ti.rsi_14,
    ti.macd,
    ti.bollinger_upper,
    ti.bollinger_lower,
    CASE 
        WHEN ti.rsi_14 > 70 THEN 'OVERBOUGHT'
        WHEN ti.rsi_14 < 30 THEN 'OVERSOLD'
        ELSE 'NEUTRAL'
    END as rsi_signal,
    CASE 
        WHEN ti.macd > ti.macd_signal THEN 'BULLISH'
        WHEN ti.macd < ti.macd_signal THEN 'BEARISH'
        ELSE 'NEUTRAL'
    END as macd_signal
FROM stocks s
JOIN technical_indicators ti ON s.id = ti.stock_id
WHERE s.is_active = TRUE
ORDER BY s.symbol, ti.date DESC;

-- View untuk sentiment analysis summary
CREATE VIEW sentiment_summary AS
SELECT 
    s.symbol,
    s.company_name,
    AVG(sa.sentiment_score) as avg_sentiment,
    COUNT(sa.id) as sentiment_count,
    SUM(CASE WHEN sa.sentiment_label IN ('POSITIVE', 'VERY_POSITIVE') THEN 1 ELSE 0 END) as positive_count,
    SUM(CASE WHEN sa.sentiment_label IN ('NEGATIVE', 'VERY_NEGATIVE') THEN 1 ELSE 0 END) as negative_count,
    ROUND((SUM(CASE WHEN sa.sentiment_label IN ('POSITIVE', 'VERY_POSITIVE') THEN 1 ELSE 0 END) / COUNT(sa.id)) * 100, 2) as positive_percentage
FROM stocks s
JOIN sentiment_analysis sa ON s.id = sa.stock_id
WHERE s.is_active = TRUE
GROUP BY s.id, s.symbol, s.company_name;

-- View untuk financial health score
CREATE VIEW financial_health_score AS
SELECT 
    s.symbol,
    s.company_name,
    fr.period_year,
    fr.pe_ratio,
    fr.pb_ratio,
    fr.roe,
    fr.roa,
    fr.debt_to_equity,
    fr.current_ratio,
    CASE 
        WHEN fr.pe_ratio BETWEEN 10 AND 25 AND fr.roe > 15 AND fr.debt_to_equity < 0.5 THEN 'EXCELLENT'
        WHEN fr.pe_ratio BETWEEN 5 AND 30 AND fr.roe > 10 AND fr.debt_to_equity < 1.0 THEN 'GOOD'
        WHEN fr.pe_ratio BETWEEN 0 AND 50 AND fr.roe > 5 AND fr.debt_to_equity < 2.0 THEN 'FAIR'
        ELSE 'POOR'
    END as health_rating
FROM stocks s
JOIN financial_ratios fr ON s.id = fr.stock_id
WHERE s.is_active = TRUE
ORDER BY s.symbol, fr.period_year DESC;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure untuk update portfolio setelah transaksi
DELIMITER //
CREATE PROCEDURE UpdatePortfolioAfterTransaction(
    IN p_user_id INT,
    IN p_stock_id INT,
    IN p_transaction_type ENUM('BUY', 'SELL'),
    IN p_quantity INT,
    IN p_price DECIMAL(10,2)
)
BEGIN
    DECLARE current_quantity INT DEFAULT 0;
    DECLARE current_avg_price DECIMAL(10,2) DEFAULT 0;
    DECLARE current_investment DECIMAL(15,2) DEFAULT 0;
    
    -- Get current portfolio data
    SELECT quantity, average_price, total_investment 
    INTO current_quantity, current_avg_price, current_investment
    FROM portfolio 
    WHERE user_id = p_user_id AND stock_id = p_stock_id;
    
    IF p_transaction_type = 'BUY' THEN
        -- Update or insert portfolio for BUY transaction
        INSERT INTO portfolio (user_id, stock_id, quantity, average_price, total_investment)
        VALUES (p_user_id, p_stock_id, p_quantity, p_price, p_quantity * p_price)
        ON DUPLICATE KEY UPDATE
            quantity = quantity + p_quantity,
            total_investment = total_investment + (p_quantity * p_price),
            average_price = (total_investment + (p_quantity * p_price)) / (quantity + p_quantity);
    ELSE
        -- Update portfolio for SELL transaction
        UPDATE portfolio 
        SET quantity = quantity - p_quantity,
            total_investment = total_investment - (p_quantity * current_avg_price)
        WHERE user_id = p_user_id AND stock_id = p_stock_id;
        
        -- Remove portfolio entry if quantity becomes 0
        DELETE FROM portfolio 
        WHERE user_id = p_user_id AND stock_id = p_stock_id AND quantity <= 0;
    END IF;
END //
DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger untuk update balance setelah transaksi
DELIMITER //
CREATE TRIGGER update_user_balance_after_transaction
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.status = 'COMPLETED' THEN
        IF NEW.transaction_type = 'BUY' THEN
            UPDATE users 
            SET balance = balance - NEW.net_amount 
            WHERE id = NEW.user_id;
        ELSE
            UPDATE users 
            SET balance = balance + NEW.net_amount 
            WHERE id = NEW.user_id;
        END IF;
        
        -- Insert balance history
        INSERT INTO balance_history (user_id, transaction_type, amount, balance_before, balance_after, reference_id)
        SELECT 
            NEW.user_id,
            'TRADING',
            NEW.net_amount,
            balance + (CASE WHEN NEW.transaction_type = 'BUY' THEN NEW.net_amount ELSE -NEW.net_amount END),
            balance,
            NEW.id
        FROM users WHERE id = NEW.user_id;
    END IF;
END //
DELIMITER ;

-- =====================================================
-- CREATE SUMMARY VIEWS
-- =====================================================

-- View untuk stock summary dengan semua data
CREATE OR REPLACE VIEW stock_summary AS
SELECT 
    s.symbol,
    s.company_name,
    ms.sector_name,
    mi.industry_name,
    s.current_price,
    s.previous_close,
    ROUND(((s.current_price - s.previous_close) / s.previous_close) * 100, 2) as price_change_pct,
    s.market_cap,
    s.market_cap_category,
    s.volume,
    ti.rsi_14,
    ti.macd,
    fr.pe_ratio,
    fr.roe,
    sa.sentiment_label,
    s.is_active
FROM stocks s
LEFT JOIN market_sectors ms ON s.sector_id = ms.id
LEFT JOIN industries mi ON s.industry_id = mi.id
LEFT JOIN technical_indicators ti ON s.id = ti.stock_id AND ti.date = CURDATE()
LEFT JOIN financial_ratios fr ON s.id = fr.stock_id AND fr.period_year = YEAR(CURDATE())
LEFT JOIN sentiment_analysis sa ON s.id = sa.stock_id AND DATE(sa.analysis_date) = CURDATE()
WHERE s.is_active = 1;

-- View untuk top gainers
CREATE OR REPLACE VIEW top_gainers AS
SELECT 
    symbol,
    company_name,
    current_price,
    previous_close,
    price_change_pct
FROM stock_summary
ORDER BY price_change_pct DESC
LIMIT 20;

-- View untuk top losers
CREATE OR REPLACE VIEW top_losers AS
SELECT 
    symbol,
    company_name,
    current_price,
    previous_close,
    price_change_pct
FROM stock_summary
ORDER BY price_change_pct ASC
LIMIT 20;

-- View untuk market cap distribution
CREATE OR REPLACE VIEW market_cap_distribution AS
SELECT 
    market_cap_category,
    COUNT(*) as jumlah_saham,
    ROUND(AVG(current_price), 2) as avg_price,
    ROUND(SUM(market_cap), 0) as total_market_cap
FROM stock_summary
GROUP BY market_cap_category
ORDER BY 
    CASE market_cap_category
        WHEN 'Mega Cap' THEN 1
        WHEN 'Large Cap' THEN 2
        WHEN 'Mid Cap' THEN 3
        WHEN 'Small Cap' THEN 4
    END;

-- View untuk sector performance
CREATE OR REPLACE VIEW sector_performance AS
SELECT 
    sector_name,
    COUNT(*) as jumlah_saham,
    ROUND(AVG(current_price), 2) as avg_price,
    ROUND(AVG(price_change_pct), 2) as avg_change_pct,
    ROUND(SUM(market_cap), 0) as total_market_cap
FROM stock_summary
GROUP BY sector_name
ORDER BY avg_change_pct DESC;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Cek total saham
SELECT 'Total Saham' as info, COUNT(*) as jumlah FROM stocks;

-- Cek saham per sektor
SELECT 'Saham per Sektor' as info, COUNT(*) as total FROM (
    SELECT ms.sector_name, COUNT(s.id) as jumlah_saham
    FROM market_sectors ms
    LEFT JOIN stocks s ON ms.id = s.sector_id
    GROUP BY ms.id, ms.sector_name
) as sector_count;

-- Cek distribusi market cap
SELECT 'Market Cap Distribution' as info, COUNT(*) as total FROM market_cap_distribution;

-- Cek data yang telah diinsert
SELECT 'Data Summary' as info, 
    (SELECT COUNT(*) FROM stocks) as stocks,
    (SELECT COUNT(*) FROM price_history) as price_history,
    (SELECT COUNT(*) FROM technical_indicators) as technical_indicators,
    (SELECT COUNT(*) FROM financial_ratios) as financial_ratios,
    (SELECT COUNT(*) FROM sentiment_analysis) as sentiment_analysis,
    (SELECT COUNT(*) FROM ipo_data) as ipo_data;

-- =====================================================
-- SUCCESS MESSAGE
-- =====================================================
SELECT 'SUCCESS: Database db_saham_optimized berhasil dibuat dengan 100 saham populer IDX!' as message;

-- =====================================================
-- END OF OPTIMIZED DATABASE STRUCTURE
-- =====================================================
