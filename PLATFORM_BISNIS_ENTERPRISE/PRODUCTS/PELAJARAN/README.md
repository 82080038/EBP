# 🎒 EBP Education Platform - Pelajaran (Kurikulum Merdeka SD Kelas 2 & 3)

Aplikasi berbasis web responsif yang dirancang khusus untuk memfasilitasi anak-anak SD Kelas 2 (Fase A) dan Kelas 3 (Fase B) belajar secara mandiri. Konten aplikasi disadur dari Buku Teks Utama resmi Kemendikbudristek (SIBI) dengan visualisasi interaktif, ramah anak, dan navigasi minim cetak (*scroll-free*).

---

## 🚀 1. Arsitektur & Tech Stack
Aplikasi ini dibangun menggunakan arsitektur web tradisional yang ringan, aman, dan mudah di-deploy:
- **Frontend:** HTML5, CSS3 (Custom Font: *Fredoka One* & *Quicksand*), JavaScript (Vanilla ES6), Bootstrap 5.3
- **Fitur Spesial UI/UX:** Web Speech API (`speechSynthesis`) untuk fitur Audio Text-to-Speech otomatis bagi anak yang belum lancar membaca.
- **Backend:** PHP 8.x / Node.js (sebagai REST API penyedia data JSON)
- **Database:** MySQL 8.x

---

## 🗄️ 2. Struktur Database (MySQL Blueprint)

Gunakan skema database berikut sebagai fondasi penyimpanan data modul dan progres siswa.

```sql
CREATE DATABASE db_merdeka_belajar;
USE db_merdeka_belajar;

-- 1. Tabel Siswa (Untuk Manajemen Profil & Avatar)
CREATE TABLE siswa (
    id_siswa INT AUTO_INCREMENT PRIMARY KEY,
    nama_siswa VARCHAR(100) NOT NULL,
    kelas ENUM('2', '3') NOT NULL,
    avatar VARCHAR(50) DEFAULT 'avatar_default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Mata Pelajaran
CREATE TABLE mapel (
    id_mapel INT AUTO_INCREMENT PRIMARY KEY,
    nama_mapel VARCHAR(50) NOT NULL, -- Matematika, Bahasa Indonesia, IPAS, Pancasila
    fase ENUM('A', 'B') NOT NULL    -- Kelas 2 = Fase A, Kelas 3 = Fase B
);

-- 3. Tabel Konten Modul Interaktif (Sistem Slide)
CREATE TABLE modul_konten (
    id_konten INT AUTO_INCREMENT PRIMARY KEY,
    id_mapel INT NOT NULL,
    kelas ENUM('2', '3') NOT NULL,
    judul_bab VARCHAR(100) NOT NULL, -- Contoh: "Mengenal Nilai Tempat Ratusan"
    urutan_slide INT NOT NULL,       -- Menentukan urutan tampil slide (1, 2, 3...)
    teks_materi TEXT NOT NULL,       -- Narasi pendek untuk anak
    url_gambar VARCHAR(255) NULL,    -- Path file ilustrasi (/assets/img/...)
    FOREIGN KEY (id_mapel) REFERENCES mapel(id_mapel) ON DELETE CASCADE
);

-- 4. Tabel Kuis Formatif (Evaluasi Akhir Modul)
CREATE TABLE kuis (
    id_soal INT AUTO_INCREMENT PRIMARY KEY,
    id_mapel INT NOT NULL,
    kelas ENUM('2', '3') NOT NULL,
    judul_bab VARCHAR(100) NOT NULL,
    pertanyaan TEXT NOT NULL,
    opsi_a VARCHAR(255) NOT NULL,
    opsi_b VARCHAR(255) NOT NULL,
    opsi_c VARCHAR(255) NOT NULL, -- Anak SD kelas rendah umumnya 3 pilihan (A, B, C)
    jawaban_benar ENUM('A', 'B', 'C') NOT NULL,
    FOREIGN KEY (id_mapel) REFERENCES mapel(id_mapel) ON DELETE CASCADE
);
```

---

## 📁 3. Struktur Direktori Proyek (Folder Architecture)

Pastikan susunan folder Anda rapi agar pemanggilan aset gambar dan skrip tidak pecah.

```text
merdeka-belajar-sd/
│
├── config/
│   └── database.php          # Koneksi ke MySQL
│
├── api/                      # Backend (Mengembalikan respon JSON)
│   ├── get_modul.php         # Mengambil data slide berdasarkan mapel & kelas
│   └── get_kuis.php          # Mengambil soal kuis formatif
│
├── assets/
│   ├── css/
│   │   └── custom-style.css  # Override warna & font Bootstrap agar ramah anak
│   ├── js/
│   │   └── app.js            # Logika audio speech, navigasi, & scoring kuis
│   ├── img/
│   │   ├── avatars/          # Karakter pilihan untuk profil anak
│   │   └── materi/           # Hasil crop ilustrasi dari PDF Kemendikbud
│
├── views/                    # Halaman Tampilan Utama
│   ├── index.html            # Halaman Pilih Profil / Login Siswa
│   ├── dashboard.html        # Menu Pilihan Mapel (Matematika, IPAS, dll)
│   ├── modul_view.html       # Halaman Belajar (Tampilan Slide + Audio)
│   └── kuis_view.html        # Halaman Evaluasi (Pilihan Ganda Interaktif)
│
└── README.md                 # Acuan Dokumentasi Pengembangan
```

---

## 🎨 4. Prinsip UI/UX Khusus Anak-Anak (Aturan Coding)

Saat menulis kode HTML dan CSS, wajib mematuhi aturan psikologi anak usia 7-9 tahun berikut:
1. **Gamification Color Palette:** Gunakan warna cerah namun lembut di mata. Dominasi warna Biru Muda (`#E3F2FD`), Kuning/Oranye Hangat (`#FF9800`) untuk tombol aksi, dan Hijau Pastel untuk sukses.
2. **Besar Ukuran Font (Typography):** Teks materi minimal berukuran `fs-4` atau `fs-3` (Bootstrap) agar terbaca jelas pada layar ponsel mini sekalipun. Gunakan font bulat tanpa sudut tajam.
3. **No Scrolling Policy:** Materi disajikan dalam bentuk *Card Carousel* (per kartu). Anak cukup menekan tombol "Lanjut" atau mengusap (*swipe*), bukan melakukan *scrolling* ke bawah yang rawan memecah konsentrasi.
4. **Instant Reward Audio:** Ketika anak memilih jawaban benar pada kuis, berikan umpan balik visual instan (bintang berkedip atau animasi piala) dan efek suara gembira.

---

## 🛠️ 5. Checklist Tahapan Pengembangan (Roadmap Coding)

Gunakan daftar ini sebagai target mingguan Anda saat melakukan *coding*:

- [ ] **Fase 1: Setup & Database**
  - [ ] Membuat database MySQL berdasarkan skema di atas.
  - [ ] Mengunduh materi PDF resmi Kelas 2 & 3 dari SIBI Kemendikbud.
  - [ ] Melakukan ekstraksi teks esensial & *cropping* gambar ilustrasi dari PDF.
  - [ ] Mengisi data sampel (*seed data*) ke tabel `mapel`, `modul_konten`, dan `kuis`.

- [ ] **Fase 2: Pembuatan API & Backend**
  - [ ] Membuat file koneksi database aman.
  - [ ] Membuat endpoint API untuk mengambil materi modul dalam format JSON berdasarkan parameter kelas dan mapel.
  - [ ] Membuat endpoint API untuk mengambil kuis acak.

- [ ] **Fase 3: Slicing Frontend UI (HTML & Bootstrap)**
  - [ ] Mendesain halaman Dashboard/Pemilihan Kelas dengan visual berbasis kartu besar.
  - [ ] Membuat halaman modul belajar menggunakan modifikasi Bootstrap Carousel.
  - [ ] Integrasi JavaScript `SpeechSynthesis` untuk membaca teks materi saat ikon speaker diklik.

- [ ] **Fase 4: Logika Kuis & Integrasi Data**
  - [ ] Mengambil data konten asli dari MySQL menggunakan JavaScript `fetch()` ke endpoint API.
  - [ ] Membuat mekanisme pemeriksaan jawaban kuis (A/B/C) langsung di frontend menggunakan JavaScript.
  - [ ] Menampilkan modal pop-up "Selamat! Kamu Mendapat 3 Bintang ⭐" di akhir kuis.

---

## 🚨 Atribusi & Hak Cipta Materi
Konten teks, materi, dan ilustrasi di dalam aplikasi ini disadur dari **Buku Teks Kurikulum Merdeka** yang diterbitkan secara resmi oleh Pusat Perbukuan Badan Standar, Kurikulum, dan Asesmen Pendidikan Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi Indonesia. Digunakan sepenuhnya untuk tujuan edukasi non-komersial.
