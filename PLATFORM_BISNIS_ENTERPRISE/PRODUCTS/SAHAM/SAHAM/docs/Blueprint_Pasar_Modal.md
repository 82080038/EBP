# BLUEPRINT SISTEM KUANTITATIF: DARI TEORI EKOSISTEM HINGGA ARSITEKTUR KODE TAHAN MANIPULASI
*Panduan Komprehensif Pembangunan Aplikasi Prediksi Pasar Modal Berbasis Python*

---

## BAB 1: ANATOMI DAN MEKANISME PASAR MODAL INDONESIA

### 1.1 Definisi dan Filosofi Dasar
Pasar modal adalah sebuah ekosistem yang mempertemukan pihak yang membutuhkan modal jangka panjang (Emiten/Perusahaan) dengan pihak yang memiliki kelebihan likuiditas (Investor/Trader). Secara filosofis, pasar modal bertindak sebagai mesin diskonto masa depan (*forward-discounting machine*), di mana harga saham saat ini mencerminkan ekspektasi kolektif pasar terhadap arus kas perusahaan di masa depan.

### 1.2 Struktur Regulasi dan Lembaga Utama (IDX)
Aplikasi Anda harus memahami bahwa setiap transaksi tunduk pada aturan tiga lembaga utama:
*   **OJK (Otoritas Jasa Keuangan)**: Regulator tertinggi yang mengawasi kepatuhan hukum dan perlindungan konsumen.
*   **BEI / IDX (Bursa Efek Indonesia)**: Penyelenggara fasilitas perdagangan tempat pesanan (*orders*) dicocokkan.
*   **KPEI & KSEI**: Lembaga yang mengurus kliring (penjaminan) dan kustodian (penyimpanan aset digital).

### 1.3 Mekanisme Pembentukan Harga: Order Book System
Harga saham tidak bergerak acak, melainkan bergeser akibat ketidakseimbangan mekanis pada *Order Book* (Buku Pesanan):
*   **Bid (Antrean Beli)**: Representasi kekuatan permintaan (*Demand*). Tersusun berurutan dari harga tertinggi ke terendah.
*   **Ask / Offer (Antrean Jual)**: Representasi kekuatan penawaran (*Supply*). Tersusun berurutan dari harga terendah ke tertinggi.
*   **Mekanisme Matching**: Transaksi terjadi ketika sebuah pesanan pasar (*Market Order*) mengeksekusi harga batas (*Limit Order*) yang tersedia di seberangnya.

---

## BAB 2: HUBUNGAN MAKROEKONOMI GLOBAL & ROTASI SEKTOR DOMESTIK

Pasar modal Indonesia (*IHSG*) sangat sensitif terhadap gravitasi makroekonomi global dan domestik. Model AI yang mengabaikan variabel ini akan mengalami bias arah jangka panjang.

### 2.1 Parameter Makroekonomi Utama
*   **Suku Bunga (BI-Rate & Fed Funds Rate)**: Bertindak sebagai biaya modal (*cost of capital*). Suku bunga tinggi menekan valuasi saham pertumbuhan (Teknologi/Properti) dan menguntungkan sektor perbankan besar.
*   **Nilai Tukar (USD/IDR)**: Penentu margin emiten berbasis impor atau ekspor. Melemahnya Rupiah secara organik menguntungkan sektor pertambangan dan merugikan sektor farmasi/manufaktur yang bergantung pada bahan baku impor.
*   **Indeks Ketakutan Global (VIX)**: Mengukur volatilitas tersirat dari opsi S&P 500. Jika VIX > 30, terjadi *Capital Outflow* (pelarian modal asing) dari pasar berkembang (*Emerging Markets*) seperti Indonesia.

### 2.2 Peta Rotasi Sektor (Sector Rotation Matrix)
Aplikasi wajib menggunakan logika pencocokan sektor berbasis rezim ekonomi berikut:

| Kondisi Makro (Input) | Sektor yang Diuntungkan (Long) | Sektor yang Dirugikan (Short/Avoid) |
| :--- | :--- | :--- |
| Suku Bunga Naik / Inflasi Tinggi | Perbankan (Big Banks), Consumer Goods | Properti, Konstruksi, Teknologi |
| Suku Bunga Turun / Likuiditas Longgar | Properti, Otomotif, Teknologi | Perbankan (Margin Menyusut) |
| Rupiah Melemah / Komoditas Booming | Batu Bara, CPO, Logam/Mineral | Farmasi, Otomotif, Perusahaan Berutang USD |

---

## BAB 3: TAKSONOMI ANOMALI DATA DAN MANIPULASI PASAR REALITAS LAPANGAN

Model prediksi kuantitatif sering kali gagal karena melatih algoritma menggunakan data yang telah direkayasa oleh pelaku pasar bermodal besar (*Market Makers / Bandar*).

### 3.1 Manipulasi Mikro (Saham Gorengan & Pump-and-Dump)
*   **Wash Trading**: Transaksi semu antar-akun milik kelompok yang sama untuk merekayasa volume perdagangan agar terlihat aktif.
*   **Spoofing**: Memasang pesanan *Bid* raksasa untuk memancing retail membeli, kemudian membatalkan (*cancel*) pesanan tersebut dalam hitungan milidetik sebelum tereksekusi.

### 3.2 Manipulasi Makro (Window Dressing & Kosmetik Akuntansi)
*   **Earnings Management**: Emiten merekayasa komponen akrual pada laporan keuangan untuk menyembunyikan penurunan performa bisnis nyata.
*   **Fake News Hype**: Distribusi sentimen positif via media massa bayaran atau bot media sosial saat posisi bandar sedang melakukan distribusi (jual massal).

---

## BAB 4: LOGIKA ALGORITMA DAN KODE ANTI-MANIPULASI (DATA CLEANING LAYER)

Sebelum data masuk ke model prediksi (*XGBoost* atau *LSTM*), data mentah harus melewati filter matematis ketat pada *Anti-Manipulation Layer* aplikasi Anda.

### 4.1 Filter Volatilitas dan Likuiditas Semu
*   **Z-Score Volume Shock**: Menghapus atau menandai anomali volume transaksi yang melompat tidak wajar.
    \[\text{Z-Score} = \frac{V_t - \mu_{\text{Volume}(20)}}{\sigma_{\text{Volume}(20)}}\]
    Jika Z-Score > 3, tandai saham tersebut sebagai risiko tinggi manipulasi *pump*.
*   **Amihud Illiquidity Ratio**: Mengukur elastisitas harga terhadap volume uang untuk membuang saham yang harganya mudah disetir modal kecil.
    \[\text{Amihud} = \frac{\vert{}Return_t\vert{}}{Volume\_Rupiah_t}\]

### 4.2 Deteksi Rekayasa Keuangan: Beneish M-Score
Algoritma 8-Variabel untuk memindai laporan keuangan kuartalan. Jika nilai **M-Score > -1.78**, aplikasi wajib melakukan *blacklist* otomatis pada emiten tersebut dari daftar investasi karena terindikasi kuat memanipulasi laba bersih.

---

## BAB 5: MANIFAKTUR FITUR (FEATURE ENGINEERING) & MODEL PREDIKSI AI

### 5.1 Sinkronisasi Garis Waktu Data Makro (Forward Fill)
Karena data harga bergerak harian dan data makro dirilis bulanan, gunakan teknik **Forward Fill (ffill)** untuk mencegah *dimension mismatch* atau kebocoran data (*data leakage*):
```python
# Ilustrasi Logika Fitur Makro dalam Pandas DataFrame
df['BI_Rate'] = df['BI_Rate'].ffill()
```

### 5.2 Strategi Lagging untuk Indikator Global
Karena bursa Wall Street (`^GSPC`) tutup beberapa jam sebelum bursa Indonesia (`^JKSE`) buka, fitur global wajib digeser 1 hari ke belakang (`.shift(1)`) agar bertindak sebagai prediktor harian pembukaan bursa domestik secara valid.

### 5.3 Validasi Model: Walk-Forward Validation
Jangan pernah menggunakan *K-Fold Cross Validation* acak pada data pasar modal karena akan menghancurkan struktur waktu (*temporal structure*). Wajib menggunakan **Time Series Split / Walk-Forward Validation** di mana data latih (*training*) selalu berada di masa lalu dari data uji (*testing*).

---

## BAB 6: MANAJEMEN RISIKO DAN ALOKASI PORTAL OTOMATIS (THE STEERING SYSTEM)

Keberhasilan aplikasi tidak ditentukan oleh akurasi prediksi, melainkan oleh ketatnya manajemen risiko saat prediksi salah.

### 6.1 Batas Rugi Dinamis Berbasis ATR (Average True Range)
Menolak persentase kaku (seperti "selalu cut loss 5%"). Jarak batas rugi harus beradaptasi dengan volatilitas historis saham.
$$\text{Titik Stop Loss} = \text{Harga Entry} - (2 \times \text{ATR}_{14})$$

### 6.2 Alokasi Modal Aman: The 1% Rule
Aplikasi menghitung jumlah lot maksimum yang boleh dibeli berdasarkan batas toleransi kehilangan modal total sebesar maksimal 1% per transaksi.
$$\text{Jumlah Lembar Saham} = \frac{\text{Total Capital} \times 0.01}{\text{Harga Entry} - \text{Harga Stop Loss}}$$

### 6.3 Formula Ukuran Posisi Dinamis: Kelly Criterion
Guna memaksimalkan pertumbuhan modal jangka panjang secara matematis berdasarkan performa histori akurasi model AI Anda:
$$f^* = W - \frac{1 - W}{R}$$
*   $f^*$: Persentase modal total yang dialokasikan untuk transaksi saat ini.
*   $W$: Rasio kemenangan histori model (*Win Rate*).
*   $R$: Rasio perbandingan rata-rata keuntungan vs rata-rata kerugian (*Win/Loss Ratio*).

---

## BAB 7: ARSITEKTUR REKAYASA DATA DAN PIPELINE PRODUKSI

### 7.1 Kamus Data Ticker `yfinance` Gratisan
Gunakan pemetaan ticker berikut untuk otomatisasi pipa data harian Anda:

| Komponen Data | Kode Ticker `yfinance` | Metode Penyimpanan Ideal |
| :--- | :--- | :--- |
| Pasar Saham Indonesia | `[KODE_SAHAM].JK` (Contoh: `BBCA.JK`) | Parquet / PostgreSQL |
| Indeks Pasar Domestik | `^JKSE` (IHSG) | Parquet / PostgreSQL |
| Sentimen Pasar Global | `^GSPC` (S&P 500) | Parquet / SQLite |
| Indeks Ketakutan Global | `^VIX` | Parquet / SQLite |
| Biaya Modal Global | `^TNX` (US 10-Year Bond) | Parquet / SQLite |
| Nilai Tukar Mata Uang | `USDIDR=X` | Parquet / SQLite |

### 7.2 Manajemen Penyimpanan: Mengapa Parquet?
Untuk kebutuhan pelatihan mesin kecerdasan (*Machine Learning*), data historis wajib disimpan dalam format **Parquet** kompresi kolom karena mampu menghemat ruang penyimpanan hingga 80% dan dapat dibaca oleh pustaka `pandas` atau `polars` dengan kecepatan jauh melampaui file teks konvensional (CSV).

### 7.3 Sistem Rem Darurat Otomatis (Circuit Breaker Internal)
Logika portofolio otomatis harus memiliki interupsi kode yang tegas: Jika *Max Drawdown* (penurunan modal total) menyentuh batas kritis (contoh: minus 10% dari puncak portofolio dalam 7 hari kerja), matikan seluruh modul perdagangan otomatis, konversi seluruh aset menjadi kas (*Cash Only Mode*), dan jalankan proses kalibrasi ulang model AI secara mandiri.
