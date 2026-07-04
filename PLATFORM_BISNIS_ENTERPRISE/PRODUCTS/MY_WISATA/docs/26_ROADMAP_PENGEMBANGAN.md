# MODUL 26 — ROADMAP PENGEMBANGAN

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 (update: referensi fitur dari aplikasi serupa)

---

## 1. RINGKASAN

Roadmap pengembangan Tour Guide Application dibagi menjadi 4 fase,
dari MVP hingga fitur lanjutan.

---

## 2. TIMELINE

```
Fase 1: MVP (Minggu 1-6)
├── Minggu 1-2: Setup project + Auth + User Management
├── Minggu 3-4: Tour Guide + Map + Booking
└── Minggu 5-6: Tiket + Destinasi + Testing

Fase 2: Core Features (Minggu 7-12)
├── Minggu 7-8: Hotel + Restoran
├── Minggu 9-10: Event + Notifikasi
└── Minggu 11-12: Report + Dashboard Admin

Fase 3: Advanced (Minggu 13-18)
├── Minggu 13-14: Audio Guide
├── Minggu 15-16: AI Tour Guide (chatbot)
└── Minggu 17-18: Review system + Optimization

Fase 4: Production (Minggu 19-22)
├── Minggu 19-20: Security hardening + Testing
├── Minggu 21: Deployment + SSL
└── Minggu 22: Go Live + Monitoring
```

---

## 3. FASE 1 — MVP (Minimum Viable Product)

### Minggu 1-2: Foundation

| Task | Modul | Output |
|------|-------|--------|
| Setup struktur folder | 04 | Struktur MVC siap |
| Config database & app | 03, 05 | Koneksi DB aktif |
| Core classes (App, Controller, Model, View) | 03 | Framework dasar |
| Auth (login, register, logout) | 07, 08 | 3 role bisa login |
| User management (admin) | 07 | Admin kelola user |
| Layout (header, footer, sidebar, navbar) | 03 | UI base |

### Minggu 3-4: Tour Guide + Map + Booking

| Task | Modul | Output |
|------|-------|--------|
| Tour Guide profil & skills | 09 | Guide kelola profil |
| Guide schedule | 09 | Kalender ketersediaan |
| Guide approval (admin) | 07 | Admin approve guide |
| Peta OpenStreetMap | 10 | Peta + marker destinasi |
| Cari guide (wisatawan) | 08 | Search + filter |
| Booking create + transaction | 11 | Booking flow lengkap |
| Guide accept/reject | 09 | Guide kelola booking |

### Minggu 5-6: Tiket + Destinasi + Testing

| Task | Modul | Output |
|------|-------|--------|
| CRUD destinasi (admin) | 07 | Admin kelola destinasi |
| Beli tiket + QR code | 12 | E-ticket dengan QR |
| Tiket saya (wisatawan) | 08 | List tiket wisatawan |
| Verifikasi tiket | 12 | Scan/input kode |
| Testing fase 1 | 24 | Bug fix MVP |

---

## 4. FASE 2 — CORE FEATURES

### Minggu 7-8: Hotel & Restoran

| Task | Modul |
|------|-------|
| Hotel register + approval | 13 |
| Hotel search + booking | 13 |
| Restoran register + approval | 14 |
| Restoran menu + order | 14 |
| Restoran keranjang + checkout | 14 |

### Minggu 9-10: Event & Notifikasi

| Task | Modul |
|------|-------|
| Event CRUD + kalender | 15 |
| Event registration | 15 |
| Notifikasi in-app | 18 |
| Notifikasi email | 18 |
| Badge notifikasi real-time | 18 |

### Minggu 11-12: Report & Dashboard

| Task | Modul |
|------|-------|
| Dashboard admin statistik | 19 |
| Grafik Chart.js | 19 |
| Laporan transaksi + filter | 19 |
| Export CSV | 19 |
| Pendapatan guide | 19 |

---

## 5. FASE 3 — ADVANCED

### Minggu 13-14: Audio Guide

| Task | Modul |
|------|-------|
| Upload audio (admin) | 16 |
| Audio player multibahasa | 16 |
| Transkrip teks | 16 |

### Minggu 15-16: AI Tour Guide

| Task | Modul |
|------|-------|
| Chatbot rule-based | 17 |
| Intent detection | 17 |
| Rekomendasi destinasi | 17 |
| Generate itinerary | 17 |
| Chat UI | 17 |

### Minggu 17-18: Review & Optimization

| Task | Modul |
|------|-------|
| Rating & review system | 08 |
| Review per entitas | 08 |
| Database query optimization | - |
| AJAX caching | - |
| Image lazy loading | - |

---

## 6. FASE 4 — PRODUCTION

### Minggu 19-20: Security & Testing

| Task | Modul |
|------|-------|
| Security hardening | 20 |
| CSRF, XSS, SQL injection test | 20, 24 |
| Rate limiting | 20 |
| Audit log | 20 |
| Full test suite | 24 |
| Performance test | 24 |

### Minggu 21: Deployment

| Task | Modul |
|------|-------|
| VPS setup | 25 |
| Apache/Nginx config | 25 |
| SSL Let's Encrypt | 25 |
| Cron jobs | 23, 25 |
| Backup automation | 23 |

### Minggu 22: Go Live

| Task | Modul |
|------|-------|
| DNS pointing | 25 |
| Final smoke test | 24 |
| Monitoring setup | 25 |
| User acceptance test | - |
| **GO LIVE** | - |

---

## 7. POST-LAUNCH ROADMAP

### Q1 2027 — Enhancement & Monetisasi

- Payment gateway integrasi (Midtrans/Xendit)
- Promo & kode diskon (voucher system)
- Wishlist/favorit destinasi (simpan & kelola)
- Shopping cart multi-item (booking guide + tiket + hotel sekaligus)
- Blog/CMS module untuk artikel wisata (SEO)
- PWA (Progressive Web App) — installable
- Push notification (Web Push API)
- Multi-bahasa UI (i18n: ID, EN, JP)
- Search engine optimization (sitemap, meta tags, schema.org)

### Q2 2027 — Web Enhancement & Offline Features

- Responsive optimization untuk tablet & mobile web
- Offline mode untuk peta (Leaflet offline tiles + cached data)
- Bookmark/favorit destinasi
- Share destinasi ke social media (WhatsApp, Facebook, Instagram)
- Weather forecast API (OpenWeatherMap) per destinasi
- Currency converter (untuk wisatawan mancanegara)
- Itinerary builder drag & drop (rencana harian multi-destinasi)
- Voucher management (e-ticket, hotel voucher, PDF storage)

> **CATATAN PENTING:** Pengembangan mobile app (React Native/Flutter) **DITUNDA** hingga setelah Q4 2027. Fokus pada optimasi web responsive dan PWA (Progressive Web App) terlebih dahulu.

### Q3 2027 — AI Enhancement & Gamification

- AI berbasis OpenAI API (opsional, berbayar) — chatbot lebih cerdas
- Image recognition untuk identifikasi destinasi (AI Vision)
- Sentiment analysis review (auto-detect review positif/negatif)
- Dynamic pricing rekomendasi (berdasarkan demand & season)
- Gamification: badge, points, explorer ranking
- Smart packing list (rekomendasi barang berdasarkan tujuan)
- Phrasebook lokal (frasa bahasa daerah/negara tujuan)
- Collaborative itinerary (co-editing dengan travel companion)

### Q4 2027 — Scale Up & Ekspansi

- Redis caching untuk session & query
- Load balancer untuk multi-server
- CDN untuk static assets (Cloudflare)
- Elasticsearch untuk full-text search
- ~~Mobile app (React Native / Flutter)~~ **DITUNDA** - fokus pada PWA
- Car rental module (sewa kendaraan)
- Flight booking module (integrasi Tiket.com/Duffel API)
- Newsletter module (email marketing campaign)
- Multi-city planning (itinerary lintas kota)
- Expense tracking (catat pengeluaran dengan cached FX offline)

---

## 8. METRIKS KESUKSESAN

| Metrik | Target Q1 | Target Q1 2027 |
|--------|-----------|----------------|
| Pengguna aktif | 1.000 | 10.000 |
| Tour guide terdaftar | 50 | 500 |
| Destinasi terdaftar | 100 | 1.000 |
| Booking/bulan | 500 | 5.000 |
| Pendapatan/bulan | Rp 50jt | Rp 500jt |
| Uptime | 99% | 99.5% |
| Rating aplikasi | 4.0 | 4.5 |

---

## 9. RISIKO & MITIGASI

| Risiko | Mitigasi |
|--------|----------|
| Adopsi user lambat | Marketing digital, partnership guide |
| Guide tidak aktif | Incentive program, training |
| Server overload | Monitoring, scale up VPS |
| Bug production | Staging environment, thorough testing |
| Kompetitor baru | Continuous improvement, fitur unik |
| Biaya AI API tinggi | Rule-based sebagai fallback, AI opsional |
| Data kurang (cold start) | Seed data, partnership Dinas Pariwisata |
| Payment gateway downtime | Multi-gateway (Midtrans + manual transfer) |
| Offline mode kompleks | Prioritas: cached tiles dulu, full offline later |

---

## 10. REFERENSI APLIKASI SERUPA

### Open Source (Stack Mirip)

| Project | URL | Stack | Fitur Referensi |
|---------|-----|-------|----------------|
| DoniTrip | github.com/donisettt/donitrip-travel-booking | PHP 8 Native MVC + MySQL + Bootstrap 5 | Shopping cart, REST API JSON, checkout |
| MakeATour | github.com/BhoirSujit/MakeATour | PHP + MySQL | Payment gateway, package comparison |
| TourStacks | github.com/Rupeshs11/Tourstack | PHP + MySQL + Nginx + Docker | Docker deploy, hotel reservation |
| PHPTRAVELS | phptravels.com | PHP (komersial, open source) | Flight, car rental, offers, blog, newsletter |
| Travela | github.com/dienakdz/travela | Laravel + MySQL | Personalized recommendations, review |

### Aplikasi Komersial (Referensi UX/Fitur)

| App | URL | Fitur Referensi |
|-----|-----|----------------|
| TouriX | tourix.app | AI tour generation, AI vision, audio guide, gamification, offline mode |
| Tripstitch | tripstitch.app | Budget tracking, weather, currency converter, phrasebook, packing list |
| Tripop | tripop.app | Voucher management, expense tracking, collaborative editing, offline everything |
| EVORI | evori.app | AI itinerary, multi-city planning, budget optimization, trip sharing |

---

## 11. KESIMPULAN

Tour Guide Application dirancang dengan pendekatan **pragmatis** menggunakan
PHP Native MVC yang ringan, biaya operasional rendah (OpenStreetMap gratis),
dapat dikembangkan bertahap dari MVP hingga platform pariwisata lengkap.

Dengan referensi dari aplikasi serupa (DoniTrip, TouriX, Tripstitch, Tripop,
PHPTRAVELS), roadmap diperkaya dengan fitur: wishlist, shopping cart multi-item,
promo/diskon, itinerary builder, weather, currency converter, gamification,
offline mode, voucher management, car rental, flight booking, dan AI enhancement.

**Total estimasi waktu pengembangan:** 22 minggu (5.5 bulan) — MVP hingga Go Live
**Post-launch enhancement:** Q1–Q4 2027

---

> **Dokumen selesai.** Semua 26 modul telah didefinisikan.
