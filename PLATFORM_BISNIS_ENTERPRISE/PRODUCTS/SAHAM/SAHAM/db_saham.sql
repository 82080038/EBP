-- =====================================================
-- DATABASE SAHAM - Struktur Database untuk Aplikasi Trading
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
-- TABEL STOCKS (Daftar Saham)
-- =====================================================
CREATE TABLE stocks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    symbol VARCHAR(10) UNIQUE NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    sector VARCHAR(100),
    industry VARCHAR(100),
    market_cap BIGINT,
    current_price DECIMAL(10,2),
    previous_close DECIMAL(10,2),
    day_high DECIMAL(10,2),
    day_low DECIMAL(10,2),
    volume BIGINT DEFAULT 0,
    market VARCHAR(50) DEFAULT 'IDX',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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

-- Insert sample stocks
INSERT INTO stocks (symbol, company_name, sector, industry, current_price, previous_close, market) VALUES
('BBCA', 'Bank Central Asia Tbk', 'Financial Services', 'Banks', 9500.00, 9450.00, 'IDX'),
('BBRI', 'Bank Rakyat Indonesia Tbk', 'Financial Services', 'Banks', 4800.00, 4750.00, 'IDX'),
('BMRI', 'Bank Mandiri Tbk', 'Financial Services', 'Banks', 6200.00, 6150.00, 'IDX'),
('TLKM', 'Telkom Indonesia Tbk', 'Telecommunications', 'Telecom Services', 3500.00, 3480.00, 'IDX'),
('ASII', 'Astra International Tbk', 'Consumer Discretionary', 'Automotive', 7200.00, 7150.00, 'IDX'),
('UNVR', 'Unilever Indonesia Tbk', 'Consumer Staples', 'Household Products', 2800.00, 2750.00, 'IDX'),
('ICBP', 'Indofood CBP Sukses Makmur Tbk', 'Consumer Staples', 'Food Products', 12000.00, 11950.00, 'IDX'),
('INDF', 'Indofood Sukses Makmur Tbk', 'Consumer Staples', 'Food Products', 6500.00, 6450.00, 'IDX'),
('ANTM', 'Aneka Tambang Tbk', 'Materials', 'Metals & Mining', 1200.00, 1180.00, 'IDX'),
('PGAS', 'Perusahaan Gas Negara Tbk', 'Energy', 'Oil & Gas', 1800.00, 1750.00, 'IDX');

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
-- END OF DATABASE STRUCTURE
-- =====================================================
