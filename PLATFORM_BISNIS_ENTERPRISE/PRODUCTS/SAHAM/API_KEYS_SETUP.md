# API Keys Setup Guide

Panduan cara mendapatkan API keys untuk integrasi data eksternal.

## Gratis API Keys (Tier Free)

### 1. Alpha Vantage ✅ **TERSEDIA**
**Pendaftaran:** https://www.alphavantage.co/support/#api-key
- **Gratis:** 500 requests/hari, 25 requests/day
- **Premium:** $50/bulan (unlimited)
- **Status:** API key sudah tersedia di `.env.example`
- **Setup:**
  1. Buka link di atas
  2. Isi email dan nama
  3. API key akan dikirim ke email
  4. Set env var: `ALPHAVANTAGE_API_KEY=ZHOQ4KT2UF37JDEQ`

### 2. Finnhub ✅ **TERSEDIA**
**Pendaftaran:** https://finnhub.io/register
- **Gratis:** 60 requests/minute
- **Premium:** $19.99/bulan
- **Status:** API key sudah tersedia di `.env.example`
- **Setup:**
  1. Buka link di atas
  2. Sign up dengan email
  3. API key tersedia di dashboard
  4. Set env var: `FINNHUB_API_KEY=d8ukr31r01qrt65sh7rgd8ukr31r01qrt65sh7s0`

### 3. NewsAPI ✅ **TERSEDIA**
**Pendaftaran:** https://newsapi.org/register
- **Gratis:** 100 requests/day
- **Developer:** $449/bulan (10,000 requests/day)
- **Status:** API key sudah tersedia di `.env.example`
- **Setup:**
  1. Buka link di atas
  2. Sign up dengan email
  3. API key tersedia di dashboard
  4. Set env var: `NEWSAPI_API_KEY=778ff8fbbb8442e7a5c0bbef66d06ef3`

## Berbayar API Keys

### 4. Twitter/X API
**Pendaftaran:** https://developer.twitter.com/en/portal/dashboard
- **Free Tier:** Tidak tersedia untuk new developers (sejak 2023)
- **Basic:** $100/bulan (10,000 posts/month)
- **Pro:** $5,000/bulan (1 million posts/month)
- **Setup:**
  1. Buka Twitter Developer Portal
  2. Create App
  3. Generate Bearer Token
  4. Set env var: `TWITTER_BEARER_TOKEN=your_token_here`

### 5. Planet Satellite Imagery
**Pendaftaran:** https://www.planet.com/contact/
- **Enterprise:** Contact sales (custom pricing)
- **Setup:**
  1. Contact Planet sales team
  2. Negotiate contract
  3. Get API credentials
  4. Set env var: `PLANET_API_KEY=your_key_here`

### 6. MarineTraffic API
**Pendaftaran:** https://www.marinetraffic.com/api/
- **Starter:** €199/bulan
- **Professional:** €499/bulan
- **Setup:**
  1. Sign up di MarineTraffic
  2. Subscribe to API plan
  3. Get API key from dashboard
  4. Set env var: `MARINE_TRAFFIC_API_KEY=your_key_here`

## Alternatif Gratis (Tanpa API Key)

### 1. Foreign Flow (IDX Data)
**Web Scraping:** Gratis tapi perlu maintenance
- Source: https://www.idx.co.id
- Endpoint: `/data-market/stock-market/foreign-buy-sell`
- **Cara:**
  - Scrape halaman IDX secara berkala
  - Parse HTML untuk data foreign flow
  - Tidak perlu API key

### 2. Social Media (Reddit)
**PRAW Library:** Gratis dengan rate limit
- Source: https://www.reddit.com/dev/api/
- **Setup:**
  - Install: `pip install praw`
  - Create Reddit app di https://www.reddit.com/prefs/apps
  - Set env vars:
    - `REDDIT_CLIENT_ID=your_client_id`
    - `REDDIT_CLIENT_SECRET=your_client_secret`
    - `REDDIT_USER_AGENT=your_app_name`

### 3. Economic Calendar (Indonesia)
**Web Scraping:** Gratis
- Source: https://www.bi.go.id (Bank Indonesia)
- Source: https://www.bps.go.id (BPS)
- **Cara:**
  - Scrape halaman economic calendar
  - Parse data BI Rate, GDP, CPI
  - Tidak perlu API key

## Priority Setup

### Phase 1 (Gratis - Wajib)
1. **Alpha Vantage** - Gratis, mudah didapat
2. **Finnhub** - Gratis, mudah didapat
3. **NewsAPI** - Gratis, mudah didapat

### Phase 2 (Gratis - Optional)
4. **Reddit (PRAW)** - Gratis, perlu setup app
5. **IDX Web Scraping** - Gratis, perlu maintenance

### Phase 3 (Berbayar - Optional)
6. **Twitter/X** - $100/bulan, untuk social media real-time
7. **Planet/MarineTraffic** - Expensive, untuk alternative data

## .env.example Template

```bash
# Alpha Vantage (Gratis - 500 req/day)
ALPHAVANTAGE_API_KEY=your_alpha_vantage_key

# Finnhub (Gratis - 60 req/min)
FINNHUB_API_KEY=your_finnhub_key

# NewsAPI (Gratis - 100 req/day)
NEWSAPI_API_KEY=your_newsapi_key

# Twitter/X (Berbayar - $100/bulan)
TWITTER_BEARER_TOKEN=your_twitter_bearer_token

# Reddit (Gratis - perlu app setup)
REDDIT_CLIENT_ID=your_reddit_client_id
REDDIT_CLIENT_SECRET=your_reddit_client_secret
REDDIT_USER_AGENT=your_app_name/1.0

# Planet Satellite (Enterprise - contact sales)
PLANET_API_KEY=your_planet_key

# MarineTraffic (Berbayar - €199/bulan)
MARINE_TRAFFIC_API_KEY=your_marine_traffic_key
```

## Testing Tanpa API Key

Untuk development/testing, aplikasi sudah menggunakan:
- Mock data di `conftest.py`
- Graceful fallback ketika API key tidak tersedia
- Proxy estimation untuk data yang sulit diakses

Tidak perlu API key untuk:
- Development lokal
- Running tests
- Mock data demonstration

API key hanya diperlukan untuk:
- Production deployment
- Real-time data fetching
- Live trading
