# MODUL 36 — TROUBLESHOOTING GUIDE

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Panduan troubleshooting untuk masalah umum yang mungkin terjadi pada aplikasi Tour Guide.

---

## 2. MASALAH LOGIN & AUTHENTICATION

### 2.1 Tidak Bisa Login

**Gejala:**
- Pesan error "Invalid credentials"
- Halaman refresh setelah login
- Tidak diarahkan ke dashboard

**Solusi:**
1. Cek email dan password
2. Pastikan Caps Lock tidak aktif
3. Reset password jika lupa
4. Cek apakah account dibanned
5. Clear browser cache dan cookies
6. Coba browser lain
7. Hubungi admin jika masalah berlanjut

### 2.2 Session Expired

**Gejala:**
- Logout otomatis
- Pesan "Session expired"

**Solusi:**
1. Login kembali
2. Cek pengaturan session timeout di admin panel
3. Pastikan cookie diizinkan di browser
4. Cek koneksi internet

### 2.3 Email Verifikasi Tidak Diterima

**Gejala:**
- Email verifikasi tidak masuk
- Tidak bisa verifikasi account

**Solusi:**
1. Cek folder spam
2. Request ulang email verifikasi
3. Cek konfigurasi SMTP di admin panel
4. Pastikan email address valid
5. Hubungi admin untuk manual verification

---

## 3. MASALAH BOOKING

### 3.1 Booking Gagal

**Gejala:**
- Pesan error saat booking
- Booking tidak tersimpan

**Solusi:**
1. Cek koneksi internet
2. Pastikan semua field terisi
3. Cek ketersediaan guide
4. Cek saldo/payment method
5. Coba booking ulang
6. Hubungi support jika masalah berlanjut

### 3.2 Pembayaran Gagal

**Gejala:**
- Pesan error pembayaran
- Status booking tetap pending

**Solusi:**
1. Cek saldo kartu/e-wallet
2. Pastikan payment method valid
3. Cek status payment gateway
4. Coba metode pembayaran lain
5. Hubungi bank jika kartu ditolak
6. Hubungi support untuk manual verification

### 3.3 Guide Tidak Muncul di Pencarian

**Gejala:**
- Guide tidak muncul di hasil pencarian
- Guide yang dicari tidak ditemukan

**Solusi:**
1. Cek status guide (approved/rejected/banned)
2. Cek filter pencarian
3. Pastikan lokasi dan bahasa sesuai
4. Refresh halaman
5. Hubungi admin jika guide seharusnya muncul

---

## 4. MASALAH TIKET

### 4.1 QR Code Tidak Muncul

**Gejala:**
- QR code tidak ditampilkan setelah pembelian
- QR code error/broken

**Solusi:**
1. Refresh halaman
2. Cek koneksi internet
3. Buka dari menu "Tiket Saya"
4. Clear browser cache
5. Hubungi support untuk re-generate QR code

### 4.2 Tiket Tidak Bisa Diverifikasi

**Gejala:**
- QR code tidak bisa di-scan
- Pesan "Invalid ticket"

**Solusi:**
1. Pastikan tiket belum used
2. Cek tanggal kunjungan
3. Pastikan QR code tidak rusak
4. Cek koneksi internet petugas
5. Hubungi support untuk manual verification

### 4.3 Tiket Expired

**Gejala:**
- Tiket ditandai sebagai expired
- Tidak bisa digunakan

**Solusi:**
1. Cek tanggal kunjungan di tiket
2. Tiket hanya valid pada tanggal yang dipilih
3. Beli tiket baru jika tanggal berubah
4. Hubungi support untuk refund jika ada kesalahan sistem

---

## 5. MASALAH PAYMENT GATEWAY

### 5.1 Payment Gateway Down

**Gejala:**
- Pesan error payment gateway
- Tidak bisa memilih metode pembayaran

**Solusi:**
1. Cek status payment gateway di admin panel
2. Tunggu beberapa menit dan coba lagi
3. Gunakan metode pembayaran lain
4. Hubungi support untuk info status

### 5.2 Payment Timeout

**Gejala:**
- Pembayaran timeout
- Status tidak jelas

**Solusi:**
1. Cek status transaksi di menu "Booking Saya"
2. Jika pending, tunggu 5-10 menit
3. Jika masih pending, hubungi support
4. Jangan lakukan pembayaran ganda

### 5.3 Refund Tidak Diterima

**Gejala:**
- Refund tidak masuk ke account
- Status refund tidak jelas

**Solusi:**
1. Cek status refund di admin panel
2. Tunggu 3-5 hari kerja untuk proses bank
3. Cek statement bank/e-wallet
4. Hubungi support jika refund tidak diterima setelah 7 hari

---

## 6. MASALAH NOTIFIKASI

### 6.1 Notifikasi Tidak Diterima

**Gejala:**
- Tidak menerima email notifikasi
- Tidak menerima SMS notifikasi

**Solusi:**
1. Cek folder spam email
2. Pastikan nomor telepon valid
3. Cek pengaturan notifikasi di profil
4. Cek konfigurasi email/SMS di admin panel
5. Hubungi support untuk test notifikasi

### 6.2 Notifikasi Terlambat

**Gejala:**
- Notifikasi diterima terlambat
- Notifikasi tidak real-time

**Solusi:**
1. Cek koneksi internet
2. Refresh halaman untuk in-app notifikasi
3. Cek queue system di server
4. Hubungi support jika delay berlebihan

---

## 7. MASALAH MAP & GPS

### 7.1 Peta Tidak Muncul

**Gejala:**
- Peta kosong atau error
- Marker tidak muncul

**Solusi:**
1. Cek koneksi internet
2. Refresh halaman
3. Cek API key OpenStreetMap
4. Cek browser compatibility
5. Coba browser lain

### 7.2 Lokasi GPS Tidak Akurat

**Gejala:**
- Lokasi user tidak akurat
- GPS tidak mendeteksi lokasi

**Solusi:**
1. Izinkan akses lokasi di browser
2. Cek GPS di device
3. Gunakan WiFi untuk akurasi lebih baik
4. Refresh halaman
5. Coba di lokasi dengan sinyal GPS kuat

---

## 8. MASALAH AUDIO GUIDE

### 8.1 Audio Tidak Bisa Diputar

**Gejala:**
- Audio tidak berbunyi
- Player error

**Solusi:**
1. Cek koneksi internet
2. Cek volume device
3. Cek browser compatibility
4. Coba browser lain
5. Clear browser cache
6. Hubungi support jika file audio corrupt

### 8.2 Audio Buffering

**Gejala:**
- Audio sering buffering
- Audio putus-putus

**Solusi:**
1. Cek koneksi internet
2. Gunakan koneksi yang lebih stabil
3. Cek bandwidth
4. Hubungi support untuk cek server audio

---

## 9. MASALAH AI ASSISTANT

### 9.1 AI Tidak Merespon

**Gejala:**
- AI tidak memberikan jawaban
- Pesan error

**Solusi:**
1. Cek koneksi internet
2. Refresh halaman
3. Cek server status
4. Coba pertanyaan lain
5. Hubungi support jika AI down

### 9.2 Jawaban AI Tidak Relevan

**Gejala:**
- AI memberikan jawaban yang salah
- AI tidak mengerti pertanyaan

**Solusi:**
1. Gunakan pertanyaan yang lebih spesifik
2. Cek spelling
3. Coba gunakan kata kunci yang berbeda
4. Hubungi support untuk laporan jawaban salah

---

## 10. MASALAH UPLOAD FILE

### 10.1 Upload Gagal

**Gejala:**
- File tidak bisa diupload
- Pesan error upload

**Solusi:**
1. Cek ukuran file (max 5MB)
2. Cek format file (JPG, PNG, MP3, PDF)
3. Cek koneksi internet
4. Clear browser cache
5. Coba file lain
6. Hubungi support jika masalah berlanjut

### 10.2 File Corrupt Setelah Upload

**Gejala:**
- File tidak bisa dibuka setelah upload
- File error

**Solusi:**
1. Upload ulang file
2. Cek file sebelum upload
3. Cek storage server
4. Hubungi support untuk cek file system

---

## 11. MASALAH PERFORMANCE

### 11.1 Aplikasi Lambat

**Gejala:**
- Halaman loading lama
- Respons lambat

**Solusi:**
1. Cek koneksi internet
2. Clear browser cache
3. Close tab browser lain
4. Cek device resources (CPU, RAM)
5. Hubungi support untuk cek server performance

### 11.2 Timeout Error

**Gejala:**
- Pesan "Request timeout"
- Halaman tidak load

**Solusi:**
1. Refresh halaman
2. Cek koneksi internet
3. Coba lagi setelah beberapa menit
4. Hubungi support untuk cek server load

---

## 12. MASALAH MOBILE

### 12.1 Tampilan Mobile Rusak

**Gejala:**
- Layout tidak responsive
- Elemen tidak tampil dengan benar

**Solusi:**
1. Refresh halaman
2. Clear browser cache
3. Update browser ke versi terbaru
4. Coba browser lain
5. Hubungi support untuk laporan bug

### 12.2 Touchscreen Tidak Responsif

**Gejala:**
- Tombol tidak bisa diklik
- Scroll tidak berfungsi

**Solusi:**
1. Refresh halaman
2. Clear browser cache
3. Restart device
4. Cek touchscreen device
5. Hubungi support untuk laporan bug

---

## 13. MASALAH DATABASE

### 13.1 Data Tidak Tersimpan

**Gejala:**
- Form tidak menyimpan data
- Data hilang setelah refresh

**Solusi:**
1. Cek koneksi internet
2. Cek apakah ada error message
3. Cek form validation
4. Hubungi support untuk cek database

### 13.2 Data Tidak Muncul

**Gejala:**
- Data yang baru diinput tidak muncul
- Data kosong

**Solusi:**
1. Refresh halaman
2. Cek filter yang aktif
3. Cek permission user
4. Hubungi support untuk cek database

---

## 14. MASALAH SERVER

### 14.1 Server Down

**Gejala:**
- Tidak bisa akses aplikasi
- Pesan "Server not reachable"

**Solusi:**
1. Cek koneksi internet
2. Cek status server di admin panel
3. Tunggu beberapa menit
4. Hubungi support untuk info status
5. Cek social media untuk update

### 14.2 Database Connection Error

**Gejala:**
- Pesan "Database connection failed"
- Tidak bisa akses data

**Solusi:**
1. Refresh halaman
2. Coba lagi setelah beberapa menit
3. Hubungi support untuk cek database server
4. Cek status di admin panel

---

## 15. MASALAH SECURITY

### 15.1 Account Hacked

**Gejala:**
- Aktivitas mencurigakan di account
- Password berubah sendiri
- Transaksi tidak dikenali

**Solusi:**
1. Segera ganti password
2. Hubungi support untuk lock account
3. Cek aktivitas terakhir
4. Aktivasi 2FA
5. Cek email untuk notifikasi keamanan

### 15.2 Phishing Attempt

**Gejala:**
- Email mencurigakan
- Link ke website palsu

**Solusi:**
1. Jangan klik link mencurigakan
2. Jangan masukkan password di website tidak resmi
3. Laporkan ke support
4. Hapus email phishing
5. Cek account untuk aktivitas mencurigakan

---

## 16. MASALAH BROWSER

### 16.1 Browser Tidak Support

**Gejala:**
- Fitur tidak berfungsi
- Tampilan rusak

**Solusi:**
1. Update browser ke versi terbaru
2. Gunakan browser yang direkomendasikan:
   - Chrome 90+
   - Firefox 88+
   - Safari 14+
   - Edge 90+
3. Aktifkan JavaScript
4. Aktifkan cookies

### 16.2 Cache Issue

**Gejala:**
- Data lama masih muncul
- Perubahan tidak terlihat

**Solusi:**
1. Clear browser cache
2. Hard refresh (Ctrl+F5)
3. Clear cookies
4. Buka di incognito/private mode
5. Coba browser lain

---

## 17. CONTACT SUPPORT

### 17.1 Kapan Menghubungi Support

Hubungi support jika:
- Masalah tidak terselesaikan setelah troubleshooting
- Error message tidak jelas
- Masalah security
- Masalah payment
- Masalah critical (server down, dll)

### 17.2 Informasi yang Diperlukan

Saat menghubungi support, siapkan:
- Nama dan email
- Deskripsi masalah
- Screenshot error (jika ada)
- Steps to reproduce
- Browser dan device yang digunakan
- Waktu terjadi masalah

### 17.3 Channel Support

- **Email**: support@tourguide.com
- **Telepon**: +62 21 1234 5678
- **WhatsApp**: +62 812 3456 7890
- **Live Chat**: Available di aplikasi
- **Jam Operasional**: Senin - Jumat, 09:00 - 17:00 (24/7 untuk critical issues)

---

## 18. FAQ UMUM

### Q: Bagaimana cara reset password?
A: Klik "Lupa Password" di halaman login, masukkan email, dan ikuti instruksi di email.

### Q: Berapa lama proses approval guide?
A: Biasanya 1-2 hari kerja. Hubungi support jika lebih dari 3 hari.

### Q: Bagaimana cara refund?
A: Hubungi support dengan alasan refund. Refund diproses dalam 3-5 hari kerja.

### Q: Apakah tiket bisa di-reschedule?
A: Tergantung kebijakan destinasi. Hubungi support untuk bantuan.

### Q: Bagaimana cara menghapus account?
A: Hubungi support untuk request penghapusan account. Data akan dihapus dalam 30 hari.

---

## 19. CHECKLIST TROUBLESHOOTING

Sebelum menghubungi support, cek:
- [ ] Koneksi internet stabil
- [ ] Browser diupdate ke versi terbaru
- [ ] Clear browser cache dan cookies
- [ ] Coba browser lain
- [ ] Coba device lain
- [ ] Screenshot error diambil
- [ ] Steps to reproduce dicatat
- [ ] Informasi account disiapkan

---

## 20. RESOURCES

### 20.1 Dokumentasi Lainnya

- User Manual: `34_USER_MANUAL.md`
- Admin Manual: `35_ADMIN_MANUAL.md`
- API Documentation: `33_API_DOCUMENTATION_SWAGGER.md`
- Installation Guide: `27_PANDUAN_INSTALASI_LOKAL.md`

### 20.2 External Resources

- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- Bootstrap Documentation: https://getbootstrap.com/docs/
- jQuery Documentation: https://api.jquery.com/

---

> **Modul Selanjutnya:** `37_PERFORMANCE_TUNING_GUIDE.md`
