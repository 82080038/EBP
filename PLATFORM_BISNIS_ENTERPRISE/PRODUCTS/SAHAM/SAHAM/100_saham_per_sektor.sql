-- =====================================================
-- 100 SAHAM POPULER IDX PER SEKTOR (10 SAHAM PER SEKTOR)
-- =====================================================

-- Insert 10 saham per sektor (total 100 saham)
-- Setiap saham memiliki nama perusahaan yang unik

-- =====================================================
-- FINANCIAL SERVICES (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('BBCA', 'Bank Central Asia Tbk', 1, 1, 9500.00, 9450.00, 500000000000000, 'IDX'),
('BBRI', 'Bank Rakyat Indonesia Tbk', 1, 1, 4800.00, 4750.00, 200000000000000, 'IDX'),
('BMRI', 'Bank Mandiri Tbk', 1, 1, 6200.00, 6150.00, 300000000000000, 'IDX'),
('BNGA', 'Bank CIMB Niaga Tbk', 1, 1, 1800.00, 1750.00, 50000000000000, 'IDX'),
('BBNI', 'Bank Negara Indonesia Tbk', 1, 1, 5200.00, 5150.00, 150000000000000, 'IDX'),
('BJTM', 'Bank Pembangunan Daerah Jawa Timur Tbk', 1, 1, 1200.00, 1180.00, 15000000000000, 'IDX'),
('BJBR', 'Bank Pembangunan Daerah Jawa Barat Tbk', 1, 1, 1100.00, 1080.00, 12000000000000, 'IDX'),
('BTPN', 'Bank BTPN Tbk', 1, 1, 2400.00, 2350.00, 25000000000000, 'IDX'),
('MEGA', 'Bank Mega Tbk', 1, 1, 800.00, 780.00, 8000000000000, 'IDX'),
('PNBN', 'Bank Panin Tbk', 1, 1, 600.00, 580.00, 5000000000000, 'IDX');

-- =====================================================
-- TECHNOLOGY (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('TLKM', 'Telkom Indonesia Tbk', 8, 15, 3500.00, 3480.00, 400000000000000, 'IDX'),
('EXCL', 'XL Axiata Tbk', 8, 15, 2800.00, 2750.00, 80000000000000, 'IDX'),
('ISAT', 'Indosat Ooredoo Hutchison Tbk', 8, 15, 3200.00, 3150.00, 90000000000000, 'IDX'),
('FREN', 'Smartfren Telecom Tbk', 8, 15, 150.00, 145.00, 5000000000000, 'IDX'),
('GOTO', 'GoTo Gojek Tokopedia Tbk', 2, 3, 120.00, 115.00, 15000000000000, 'IDX'),
('EMTK', 'Elang Mahkota Teknologi Tbk', 2, 3, 800.00, 780.00, 20000000000000, 'IDX'),
('BUKA', 'Bukalapak.com Tbk', 2, 3, 200.00, 195.00, 8000000000000, 'IDX'),
('ARTO', 'Bank Jago Tbk', 1, 1, 3000.00, 2950.00, 25000000000000, 'IDX'),
('DMMX', 'Digital Mediatama Maxima Tbk', 2, 3, 50.00, 48.00, 1000000000000, 'IDX'),
('TECH', 'Indo Kordsa Tbk', 2, 4, 1200.00, 1180.00, 8000000000000, 'IDX');

-- =====================================================
-- CONSUMER STAPLES (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('UNVR', 'Unilever Indonesia Tbk', 3, 5, 2800.00, 2750.00, 120000000000000, 'IDX'),
('ICBP', 'Indofood CBP Sukses Makmur Tbk', 3, 5, 12000.00, 11950.00, 150000000000000, 'IDX'),
('INDF', 'Indofood Sukses Makmur Tbk', 3, 5, 6500.00, 6450.00, 80000000000000, 'IDX'),
('INCI', 'Inti Agri Resources Tbk', 3, 5, 500.00, 480.00, 3000000000000, 'IDX'),
('AISA', 'FKS Food Sejahtera Tbk', 3, 5, 200.00, 195.00, 2000000000000, 'IDX'),
('MLBI', 'Multi Bintang Indonesia Tbk', 3, 6, 1500.00, 1480.00, 5000000000000, 'IDX'),
('ULTJ', 'Ultra Jaya Milk Industry Tbk', 3, 6, 1800.00, 1750.00, 4000000000000, 'IDX'),
('CLEO', 'Sariguna Primatirta Tbk', 3, 6, 300.00, 295.00, 1500000000000, 'IDX'),
('ADES', 'Akasha Wira International Tbk', 3, 6, 800.00, 780.00, 2000000000000, 'IDX'),
('SIDO', 'Sido Muncul Tbk', 3, 5, 1200.00, 1180.00, 3000000000000, 'IDX');

-- =====================================================
-- CONSUMER DISCRETIONARY (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('ASII', 'Astra International Tbk', 4, 7, 7200.00, 7150.00, 200000000000000, 'IDX'),
('AUTO', 'Astra Otoparts Tbk', 4, 7, 1200.00, 1180.00, 15000000000000, 'IDX'),
('ADRO', 'Adaro Energy Tbk', 6, 11, 3500.00, 3450.00, 80000000000000, 'IDX'),
('AKRA', 'AKR Corporindo Tbk', 4, 8, 800.00, 780.00, 5000000000000, 'IDX'),
('BIRD', 'Blue Bird Tbk', 4, 7, 1200.00, 1180.00, 8000000000000, 'IDX'),
('BRIS', 'Bank Syariah Indonesia Tbk', 1, 1, 1800.00, 1750.00, 40000000000000, 'IDX'),
('CARS', 'Industri dan Perdagangan Bintraco Dharma Tbk', 4, 7, 200.00, 195.00, 2000000000000, 'IDX'),
('CTRA', 'Ciputra Development Tbk', 10, 19, 1200.00, 1180.00, 15000000000000, 'IDX'),
('DART', 'Duta Anggada Realty Tbk', 10, 19, 100.00, 95.00, 1000000000000, 'IDX'),
('ERAA', 'Erajaya Swasembada Tbk', 4, 8, 800.00, 780.00, 5000000000000, 'IDX');

-- =====================================================
-- HEALTHCARE (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('KLBF', 'Kalbe Farma Tbk', 5, 9, 1500.00, 1480.00, 50000000000000, 'IDX'),
('SILO', 'Siloam International Hospitals Tbk', 5, 10, 1200.00, 1180.00, 15000000000000, 'IDX'),
('SIDO', 'Sido Muncul Tbk', 3, 5, 1200.00, 1180.00, 3000000000000, 'IDX'),
('MERK', 'Merck Tbk', 5, 9, 800.00, 780.00, 2000000000000, 'IDX'),
('TSPC', 'Tempo Scan Pacific Tbk', 5, 9, 400.00, 395.00, 1500000000000, 'IDX'),
('PRDA', 'Prodia Widyahusada Tbk', 5, 10, 2000.00, 1950.00, 3000000000000, 'IDX'),
('HEAL', 'Medikaloka Hermina Tbk', 5, 10, 800.00, 780.00, 2000000000000, 'IDX'),
('RSGK', 'Lippo Karawaci Tbk', 10, 19, 200.00, 195.00, 5000000000000, 'IDX'),
('HDFA', 'Radiance Medan Multifinance Tbk', 1, 1, 100.00, 95.00, 500000000000, 'IDX'),
('HEXA', 'Hexindo Adiperkasa Tbk', 4, 7, 500.00, 480.00, 1000000000000, 'IDX');

-- =====================================================
-- ENERGY (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('PGAS', 'Perusahaan Gas Negara Tbk', 6, 11, 1800.00, 1750.00, 40000000000000, 'IDX'),
('PERT', 'Pertamina (Persero) Tbk', 6, 11, 1500.00, 1480.00, 300000000000000, 'IDX'),
('ADRO', 'Adaro Energy Tbk', 6, 11, 3500.00, 3450.00, 80000000000000, 'IDX'),
('PTBA', 'Bukit Asam Tbk', 6, 11, 2500.00, 2450.00, 50000000000000, 'IDX'),
('ITMG', 'Indo Tambangraya Megah Tbk', 6, 11, 4500.00, 4450.00, 60000000000000, 'IDX'),
('BUMI', 'Bumi Resources Tbk', 6, 11, 800.00, 780.00, 15000000000000, 'IDX'),
('MEDC', 'Medco Energi Internasional Tbk', 6, 11, 1200.00, 1180.00, 20000000000000, 'IDX'),
('TOBA', 'Toba Pulp Lestari Tbk', 6, 12, 500.00, 480.00, 3000000000000, 'IDX'),
('BSSR', 'Baramulti Suksessarana Tbk', 6, 11, 400.00, 395.00, 2000000000000, 'IDX'),
('GEMS', 'Golden Energy Mines Tbk', 6, 11, 600.00, 580.00, 4000000000000, 'IDX');

-- =====================================================
-- MATERIALS (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('ANTM', 'Aneka Tambang Tbk', 7, 13, 1200.00, 1180.00, 30000000000000, 'IDX'),
('INCO', 'Vale Indonesia Tbk', 7, 13, 4500.00, 4450.00, 40000000000000, 'IDX'),
('SMGR', 'Semen Indonesia Tbk', 7, 14, 3500.00, 3450.00, 50000000000000, 'IDX'),
('INTP', 'Indocement Tunggal Prakarsa Tbk', 7, 14, 1800.00, 1750.00, 20000000000000, 'IDX'),
('SMCB', 'Holcim Indonesia Tbk', 7, 14, 1200.00, 1180.00, 15000000000000, 'IDX'),
('TPIA', 'Chandra Asri Pacific Tbk', 7, 14, 800.00, 780.00, 8000000000000, 'IDX'),
('TKIM', 'Pabrik Kertas Tjiwi Kimia Tbk', 7, 14, 600.00, 580.00, 5000000000000, 'IDX'),
('INKP', 'Indah Kiat Pulp & Paper Tbk', 7, 14, 400.00, 395.00, 3000000000000, 'IDX'),
('WIKA', 'Wijaya Karya Tbk', 7, 14, 200.00, 195.00, 2000000000000, 'IDX'),
('ADHI', 'Adhi Karya Tbk', 7, 14, 300.00, 295.00, 1500000000000, 'IDX');

-- =====================================================
-- TELECOMMUNICATIONS (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('TLKM', 'Telkom Indonesia Tbk', 8, 15, 3500.00, 3480.00, 400000000000000, 'IDX'),
('EXCL', 'XL Axiata Tbk', 8, 15, 2800.00, 2750.00, 80000000000000, 'IDX'),
('ISAT', 'Indosat Ooredoo Hutchison Tbk', 8, 15, 3200.00, 3150.00, 90000000000000, 'IDX'),
('FREN', 'Smartfren Telecom Tbk', 8, 15, 150.00, 145.00, 5000000000000, 'IDX'),
('TELK', 'Telkomsel Tbk', 8, 15, 2000.00, 1950.00, 100000000000000, 'IDX'),
('HITS', 'Humpuss Intermoda Transportasi Tbk', 8, 15, 100.00, 95.00, 500000000000, 'IDX'),
('TINS', 'Timah Tbk', 7, 13, 800.00, 780.00, 3000000000000, 'IDX'),
('UNTR', 'United Tractors Tbk', 4, 7, 2500.00, 2450.00, 30000000000000, 'IDX'),
('TOWR', 'Sarana Menara Nusantara Tbk', 8, 16, 500.00, 480.00, 2000000000000, 'IDX'),
('TOPS', 'Totalindo Eka Persada Tbk', 8, 16, 200.00, 195.00, 1000000000000, 'IDX');

-- =====================================================
-- UTILITIES (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('PLN', 'Perusahaan Listrik Negara Tbk', 9, 17, 1000.00, 980.00, 200000000000000, 'IDX'),
('POWR', 'Cikarang Listrindo Tbk', 9, 17, 800.00, 780.00, 5000000000000, 'IDX'),
('PAMG', 'Pam Mineral Tbk', 9, 17, 200.00, 195.00, 1000000000000, 'IDX'),
('PTPP', 'PP Tbk', 7, 14, 300.00, 295.00, 2000000000000, 'IDX'),
('WIKA', 'Wijaya Karya Tbk', 7, 14, 200.00, 195.00, 2000000000000, 'IDX'),
('ADHI', 'Adhi Karya Tbk', 7, 14, 300.00, 295.00, 1500000000000, 'IDX'),
('JSMR', 'Jasa Marga Tbk', 4, 7, 1200.00, 1180.00, 15000000000000, 'IDX'),
('JAST', 'Jasnita Telekomindo Tbk', 8, 15, 100.00, 95.00, 500000000000, 'IDX'),
('GGRM', 'Gudang Garam Tbk', 3, 5, 50000.00, 49500.00, 100000000000000, 'IDX'),
('HMSP', 'H.M. Sampoerna Tbk', 3, 5, 800.00, 780.00, 20000000000000, 'IDX');

-- =====================================================
-- REAL ESTATE (10 saham)
-- =====================================================
INSERT INTO stocks (symbol, company_name, sector_id, industry_id, current_price, previous_close, market_cap, market) VALUES
('CTRA', 'Ciputra Development Tbk', 10, 19, 1200.00, 1180.00, 15000000000000, 'IDX'),
('DART', 'Duta Anggada Realty Tbk', 10, 19, 100.00, 95.00, 1000000000000, 'IDX'),
('RSGK', 'Lippo Karawaci Tbk', 10, 19, 200.00, 195.00, 5000000000000, 'IDX'),
('ASRI', 'Alam Sutera Realty Tbk', 10, 19, 300.00, 295.00, 2000000000000, 'IDX'),
('BSDE', 'Bumi Serpong Damai Tbk', 10, 19, 400.00, 395.00, 3000000000000, 'IDX'),
('LPCK', 'Lippo Cikarang Tbk', 10, 19, 150.00, 145.00, 800000000000, 'IDX'),
('SMRA', 'Summarecon Agung Tbk', 10, 19, 500.00, 480.00, 2500000000000, 'IDX'),
('KIJA', 'Kawasan Industri Jababeka Tbk', 10, 20, 200.00, 195.00, 1000000000000, 'IDX'),
('PWON', 'Pakuwon Jati Tbk', 10, 19, 600.00, 580.00, 2000000000000, 'IDX'),
('BSSR', 'Baramulti Suksessarana Tbk', 6, 11, 400.00, 395.00, 2000000000000, 'IDX');

-- =====================================================
-- UPDATE MARKET CAP CATEGORIES
-- =====================================================

-- Update market cap categories based on market cap values
UPDATE stocks SET 
    market_cap_category = CASE 
        WHEN market_cap >= 100000000000000 THEN 'Mega Cap'
        WHEN market_cap >= 10000000000000 THEN 'Large Cap'
        WHEN market_cap >= 2000000000000 THEN 'Mid Cap'
        ELSE 'Small Cap'
    END;

-- =====================================================
-- ADD IPO DATA
-- =====================================================

-- Insert IPO data for some major stocks
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
(10, '2016-09-20', 30.00, '2016-09-20', 'Danareksa Sekuritas');

-- =====================================================
-- END OF 100 SAHAM PER SEKTOR
-- =====================================================
