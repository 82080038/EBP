# Setup API Keys

API keys sudah dikonfigurasi di `.env.example`. Untuk mengaktifkannya:

## Langkah 1: Copy .env.example ke .env

```bash
# Windows PowerShell
Copy-Item .env.example .env

# Linux/Mac
cp .env.example .env
```

## Langkah 2: Verifikasi API Keys

File `.env` sekarang berisi:
```
ALPHAVANTAGE_API_KEY=ZHOQ4KT2UF37JDEQ
FINNHUB_API_KEY=d8ukr31r01qrt65sh7rgd8ukr31r01qrt65sh7s0
NEWSAPI_API_KEY=778ff8fbbb8442e7a5c0bbef66d06ef3
```

## Langkah 3: Test API Keys

```bash
.venv\Scripts\python.exe -m pytest tests/test_api_keys.py -v
```

## API Keys yang Tersedia

### ✅ Alpha Vantage
- **Key:** ZHOQ4KT2UF37JDEQ
- **Limit:** 500 requests/day
- **Usage:** Global stocks, forex, crypto data

### ✅ Finnhub
- **Key:** d8ukr31r01qrt65sh7rgd8ukr31r01qrt65sh7s0
- **Limit:** 60 requests/minute
- **Usage:** Real-time quotes, news, fundamentals

### ✅ NewsAPI
- **Key:** 778ff8fbbb8442e7a5c0bbef66d06ef3
- **Limit:** 100 requests/day
- **Usage:** Real-time news articles

## Opsional: Reddit API (Gratis)

Untuk social media Reddit, Anda perlu setup app sendiri:

1. Buka https://www.reddit.com/prefs/apps
2. Create app (script type)
3. Copy client ID dan secret
4. Tambahkan ke `.env`:
```
REDDIT_CLIENT_ID=your_client_id
REDDIT_CLIENT_SECRET=your_client_secret
REDDIT_USER_AGENT=SahamApp/1.0
```

## Catatan Keamanan

- File `.env` di-ignore oleh git (tidak akan di-commit)
- Jangan share API keys secara publik
- API keys di atas adalah gratis tier, tidak berbayar
