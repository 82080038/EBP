# MODUL 35 — ADMIN MANUAL

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Panduan penggunaan panel admin untuk mengelola aplikasi Tour Guide.

---

## 2. LOGIN ADMIN

### 2.1 Akses Panel Admin

1. Buka https://tourguide.com/admin
2. Masukkan email admin
3. Masukkan password
4. Klik "Masuk"
5. Dashboard admin akan ditampilkan

### 2.2 Keamanan

- Gunakan password yang kuat (minimal 12 karakter)
- Ganti password setiap 90 hari
- Gunakan 2FA jika tersedia
- Logout setelah selesai
- Jangan gunakan perangkat publik

---

## 3. DASHBOARD ADMIN

### 3.1 Ringkasan Dashboard

Dashboard menampilkan:
- **Total Users**: Jumlah pengguna terdaftar
- **Total Guides**: Jumlah tour guide
- **Total Bookings**: Jumlah booking
- **Total Revenue**: Total pendapatan
- **Pending Approvals**: Item yang perlu approval
- **Recent Activities**: Aktivitas terbaru

### 3.2 Grafik Statistik

- **Booking Trends**: Grafik booking per bulan
- **Revenue Trends**: Grafik pendapatan per bulan
- **User Growth**: Grafik pertumbuhan pengguna
- **Guide Performance**: Grafik performa guide

---

## 4. MANAJEMEN PENGGUNA

### 4.1 Daftar Pengguna

1. Klik menu "Manajemen Pengguna"
2. Lihat daftar semua pengguna
3. Filter berdasarkan:
   - Role (wisatawan, tour_guide, admin)
   - Status (active, banned)
   - Tanggal pendaftaran

### 4.2 Menambah Pengguna

1. Klik "Tambah Pengguna"
2. Isi formulir:
   - Nama
   - Email
   - Role
   - Password
3. Klik "Simpan"
4. User akan menerima email notifikasi

### 4.3 Edit Pengguna

1. Klik "Edit" pada user yang diinginkan
2. Update informasi:
   - Nama
   - Email
   - Role
   - Status
3. Klik "Simpan"

### 4.4 Ban/Unban Pengguna

1. Klik "Ban" pada user yang diinginkan
2. Pilih alasan pemblokiran
3. Klik "Konfirmasi"
4. User tidak akan bisa login
5. Untuk unban, klik "Unban"

---

## 5. MANAJEMEN TOUR GUIDE

### 5.1 Daftar Tour Guide

1. Klik menu "Tour Guide"
2. Lihat daftar semua tour guide
3. Filter berdasarkan:
   - Status (pending, approved, rejected, banned)
   - Lokasi
   - Rating

### 5.2 Approval Guide

1. Klik menu "Tour Guide"
2. Lihat tab "Pending Approval"
3. Klik guide yang perlu di-approve
4. Review dokumen verifikasi:
   - KTP
   - Sertifikat
   - Foto profil
5. Klik "Approve" atau "Reject"
6. Jika reject, berikan alasan

### 5.3 Edit Profil Guide

1. Klik "Edit" pada guide yang diinginkan
2. Update informasi:
   - Nama
   - Lokasi
   - Bahasa
   - Spesialisasi
   - Harga
   - Deskripsi
3. Klik "Simpan"

### 5.4 Ban Guide

1. Klik "Ban" pada guide yang diinginkan
2. Pilih alasan pemblokiran
3. Klik "Konfirmasi"
4. Guide tidak akan muncul di pencarian

---

## 6. MANAJEMEN DESTINASI

### 6.1 Daftar Destinasi

1. Klik menu "Destinasi Wisata"
2. Lihat daftar semua destinasi
3. Filter berdasarkan:
   - Kategori
   - Lokasi
   - Status (active, inactive)

### 6.2 Tambah Destinasi

1. Klik "Tambah Destinasi"
2. Isi formulir:
   - Nama destinasi
   - Deskripsi
   - Kategori
   - Lokasi (latitude, longitude)
   - Harga tiket
   - Jam operasional
   - Fasilitas
   - Foto
3. Klik "Simpan"

### 6.3 Edit Destinasi

1. Klik "Edit" pada destinasi yang diinginkan
2. Update informasi
3. Klik "Simpan"

### 6.4 Hapus Destinasi

1. Klik "Hapus" pada destinasi yang diinginkan
2. Konfirmasi penghapusan
3. Destinasi akan dihapus dari sistem

---

## 7. MANAJEMEN KATEGORI

### 7.1 Daftar Kategori

1. Klik menu "Kategori Destinasi"
2. Lihat daftar semua kategori

### 7.2 Tambah Kategori

1. Klik "Tambah Kategori"
2. Masukkan nama kategori
3. Upload icon (opsional)
4. Klik "Simpan"

### 7.3 Edit Kategori

1. Klik "Edit" pada kategori yang diinginkan
2. Update nama dan icon
3. Klik "Simpan"

---

## 8. MANAJEMEN TIKET

### 8.1 Daftar Tiket

1. Klik menu "Kelola Tiket"
2. Lihat daftar semua tiket yang terjual
3. Filter berdasarkan:
   - Destinasi
   - Tanggal pembelian
   - Status (pending, used, expired)

### 8.2 Verifikasi Tiket

1. Klik menu "Verifikasi Tiket"
2. Scan QR code tiket
3. Sistem akan menampilkan:
   - Nama destinasi
   - Tanggal kunjungan
   - Jumlah tiket
   - Status tiket
4. Klik "Verifikasi" untuk menandai sebagai used

### 8.3 Refund Tiket

1. Klik "Refund" pada tiket yang diinginkan
2. Pilih alasan refund
3. Klik "Proses Refund"
4. Refund akan diproses ke metode pembayaran asli

---

## 9. MANAJEMEN HOTEL

### 9.1 Daftar Hotel

1. Klik menu "Hotel & Homestay"
2. Lihat daftar semua hotel
3. Filter berdasarkan:
   - Status (pending, approved, rejected)
   - Lokasi
   - Tipe

### 9.2 Approval Hotel

1. Lihat tab "Pending Approval"
2. Klik hotel yang perlu di-approve
3. Review informasi hotel
4. Klik "Approve" atau "Reject"

### 9.3 Edit Hotel

1. Klik "Edit" pada hotel yang diinginkan
2. Update informasi hotel
3. Klik "Simpan"

---

## 10. MANAJEMEN RESTORAN

### 10.1 Daftar Restoran

1. Klik menu "Restoran & UMKM"
2. Lihat daftar semua restoran
3. Filter berdasarkan:
   - Status (pending, approved, rejected)
   - Lokasi
   - Jenis makanan

### 10.2 Approval Restoran

1. Lihat tab "Pending Approval"
2. Klik restoran yang perlu di-approve
3. Review informasi restoran
4. Klik "Approve" atau "Reject"

### 10.3 Edit Restoran

1. Klik "Edit" pada restoran yang diinginkan
2. Update informasi restoran
3. Klik "Simpan"

---

## 11. MANAJEMEN EVENT

### 11.1 Daftar Event

1. Klik menu "Event & Budaya"
2. Lihat daftar semua event
3. Filter berdasarkan:
   - Status (upcoming, ongoing, completed)
   - Kategori
   - Tanggal

### 11.2 Tambah Event

1. Klik "Tambah Event"
2. Isi formulir:
   - Nama event
   - Deskripsi
   - Kategori
   - Tanggal dan waktu
   - Lokasi
   - Kuota peserta
   - Harga (jika berbayar)
   - Poster
3. Klik "Simpan"

### 11.3 Edit Event

1. Klik "Edit" pada event yang diinginkan
2. Update informasi event
3. Klik "Simpan"

---

## 12. MANAJEMEN AUDIO GUIDE

### 12.1 Daftar Audio Guide

1. Klik menu "Audio Guide"
2. Lihat daftar semua audio guide
3. Filter berdasarkan:
   - Destinasi
   - Bahasa

### 12.2 Upload Audio

1. Klik "Upload Audio"
2. Pilih destinasi
3. Pilih bahasa
4. Upload file audio (MP3, WAV)
5. Masukkan transkrip (opsional)
6. Klik "Simpan"

### 12.3 Edit Audio

1. Klik "Edit" pada audio yang diinginkan
2. Update audio atau transkrip
3. Klik "Simpan"

### 12.4 Hapus Audio

1. Klik "Hapus" pada audio yang diinginkan
2. Konfirmasi penghapusan
3. Audio akan dihapus dari sistem

---

## 13. MANAJEMEN TRANSAKSI

### 13.1 Daftar Transaksi

1. Klik menu "Transaksi"
2. Lihat daftar semua transaksi
3. Filter berdasarkan:
   - Tipe (booking, tiket, hotel, restoran)
   - Status (pending, paid, failed, refunded)
   - Tanggal

### 13.2 Verifikasi Pembayaran

1. Klik "Verifikasi" pada transaksi pending
2. Review bukti pembayaran
3. Klik "Approve" atau "Reject"
4. Jika approve, status akan berubah ke "paid"

### 13.3 Refund Transaksi

1. Klik "Refund" pada transaksi yang diinginkan
2. Pilih alasan refund
3. Masukkan jumlah refund
4. Klik "Proses Refund"

---

## 14. LAPORAN & ANALITIK

### 14.1 Laporan Booking

1. Klik menu "Laporan"
2. Pilih "Laporan Booking"
3. Filter berdasarkan:
   - Tanggal range
   - Status booking
4. Klik "Generate"
5. Laporan akan ditampilkan
6. Klik "Export CSV" untuk download

### 14.2 Laporan Pendapatan

1. Klik menu "Laporan"
2. Pilih "Laporan Pendapatan"
3. Filter berdasarkan:
   - Tanggal range
   - Sumber pendapatan
4. Klik "Generate"
5. Laporan akan ditampilkan
6. Klik "Export CSV" untuk download

### 14.3 Laporan Guide

1. Klik menu "Laporan"
2. Pilih "Laporan Guide"
3. Lihat performa setiap guide:
   - Jumlah booking
   - Total pendapatan
   - Rating rata-rata
   - Completion rate

### 14.4 Laporan Destinasi

1. Klik menu "Laporan"
2. Pilih "Laporan Destinasi"
3. Lihat statistik destinasi:
   - Jumlah kunjungan
   - Tiket terjual
   - Pendapatan
   - Rating rata-rata

---

## 15. MANAJEMEN NOTIFIKASI

### 15.1 Broadcast Notifikasi

1. Klik menu "Notifikasi"
2. Klik "Broadcast"
3. Pilih target:
   - Semua user
   - Wisatawan saja
   - Tour guide saja
4. Masukkan judul dan pesan
5. Pilih jenis notifikasi:
   - Email
   - In-app
   - SMS
6. Klik "Kirim"

### 15.2 Riwayat Notifikasi

1. Lihat daftar notifikasi yang dikirim
2. Filter berdasarkan:
   - Tanggal
   - Jenis notifikasi
   - Target

---

## 16. MANAJEMEN ULASAN

### 16.1 Daftar Ulasan

1. Klik menu "Ulasan"
2. Lihat daftar semua ulasan
3. Filter berdasarkan:
   - Target (guide, destinasi, hotel, restoran)
   - Rating
   - Status (pending, approved, rejected)

### 16.2 Moderasi Ulasan

1. Klik ulasan yang perlu dimoderasi
2. Review konten ulasan
3. Klik "Approve" atau "Reject"
4. Jika reject, berikan alasan

### 16.3 Hapus Ulasan

1. Klik "Hapus" pada ulasan yang melanggar kebijakan
2. Konfirmasi penghapusan
3. Ulasan akan dihapus

---

## 17. PENGATURAN SISTEM

### 17.1 Pengaturan Umum

1. Klik menu "Pengaturan"
2. Update pengaturan:
   - Nama aplikasi
   - Email kontak
   - Nomor telepon
   - Logo
3. Klik "Simpan"

### 17.2 Pengaturan Pembayaran

1. Klik menu "Pengaturan"
2. Pilih "Pembayaran"
3. Konfigurasi payment gateway:
   - Midtrans
   - Xendit
   - Stripe
4. Masukkan API keys
5. Klik "Simpan"

### 17.3 Pengaturan Email

1. Klik menu "Pengaturan"
2. Pilih "Email"
3. Konfigurasi SMTP:
   - Host
   - Port
   - Username
   - Password
4. Klik "Test Email" untuk test
5. Klik "Simpan"

### 17.4 Pengaturan SMS

1. Klik menu "Pengaturan"
2. Pilih "SMS"
3. Konfigurasi SMS gateway:
   - Provider
   - API key
4. Klik "Test SMS" untuk test
5. Klik "Simpan"

---

## 18. BACKUP & RESTORE

### 18.1 Backup Database

1. Klik menu "Backup & Restore"
2. Klik "Backup Database"
3. Pilih jenis backup:
   - Full backup
   - Partial backup
4. Klik "Generate Backup"
5. Backup akan di-download

### 18.2 Restore Database

1. Klik menu "Backup & Restore"
2. Klik "Restore Database"
3. Upload file backup (.sql)
4. Klik "Restore"
5. Konfirmasi restore
6. Database akan di-restore

### 18.3 Jadwal Backup

1. Klik menu "Backup & Restore"
2. Pilih "Jadwal Backup"
3. Konfigurasi jadwal:
   - Harian (02:00)
   - Mingguan
   - Bulanan
4. Klik "Simpan"

---

## 19. LOG AUDIT

### 19.1 Lihat Log Audit

1. Klik menu "Log Audit"
2. Lihat semua aktivitas admin:
   - User yang melakukan aksi
   - Aksi yang dilakukan
   - Waktu aksi
   - IP address
3. Filter berdasarkan:
   - User
   - Aksi
   - Tanggal

### 19.2 Export Log

1. Klik "Export Log"
2. Pilih tanggal range
3. Klik "Export"
4. Log akan di-download sebagai CSV

---

## 20. KEAMANAN

### 20.1 2FA Setup

1. Klik menu "Profil"
2. Klik "Setup 2FA"
3. Scan QR code dengan authenticator app
4. Masukkan kode verifikasi
5. Klik "Aktifkan"

### 20.2 IP Whitelist

1. Klik menu "Pengaturan"
2. Pilih "Keamanan"
3. Masukkan IP address yang diizinkan
4. Klik "Tambah"
5. Hanya IP yang terdaftar yang bisa akses

### 20.3 Session Timeout

1. Klik menu "Pengaturan"
2. Pilih "Keamanan"
3. Set session timeout (menit)
4. Klik "Simpan"

---

## 21. TROUBLESHOOTING

### 21.1 Masalah Umum

**Login gagal:**
- Cek email dan password
- Reset password jika lupa
- Cek apakah account dibanned

**Data tidak muncul:**
- Refresh halaman
- Cek filter yang aktif
- Cek koneksi internet

**Transaksi pending:**
- Verifikasi pembayaran manual
- Cek status payment gateway
- Hubungi user untuk konfirmasi

### 21.2 Hubungi Technical Support

Jika masalah berlanjut:
- Email: admin-support@tourguide.com
- Telepon: +62 21 1234 5679
- Jam: 24/7 untuk critical issues

---

## 22. BEST PRACTICES

### 22.1 Approval Workflow

- Review dokumen dengan teliti
- Berikan alasan jika reject
- Respon dalam 24-48 jam
- Dokumentasikan keputusan

### 22.2 Data Management

- Backup database secara berkala
- Hapus data yang tidak perlu
- Validasi data sebelum input
- Gunakan filter untuk data besar

### 22.3 Security

- Ganti password secara berkala
- Aktifkan 2FA
- Monitor log audit
- Laporkan aktivitas mencurigakan

---

## 23. CHECKLIST HARIAN

- [ ] Cek pending approvals
- [ ] Verifikasi pembayaran pending
- [ ] Review ulasan baru
- [ ] Cek notifikasi system
- [ ] Monitor server performance
- [ ] Backup database (jika jadwal harian)

---

## 24. CHECKLIST MINGGUAN

- [ ] Review laporan mingguan
- [ ] Cek performa guide
- [ ] Update konten destinasi
- [ ] Review user feedback
- [ ] Update pengaturan jika perlu
- [ ] Backup database (jika jadwal mingguan)

---

## 25. CHECKLIST BULANAN

- [ ] Review laporan bulanan
- [ ] Audit security
- [ ] Update payment gateway
- [ ] Review dan update pricing
- [ ] Backup database (jika jadwal bulanan)
- [ ] Training admin baru (jika ada)

---

> **Modul Selanjutnya:** `36_TROUBLESHOOTING_GUIDE.md`
