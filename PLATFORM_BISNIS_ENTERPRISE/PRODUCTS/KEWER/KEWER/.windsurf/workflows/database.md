---
description: Database operations untuk aplikasi Kewer (3 database)
---

## Database Operations

> Kewer menggunakan 3 database: `kewer`, `db_alamat`, `db_orang`
> Semua SQL dump ada di folder `database/`

### Backup Semua Database
```bash
cd /opt/lampp/htdocs/kewer
# Export ketiga database ke folder database/
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers kewer > database/kewer.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_alamat > database/db_alamat.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_orang > database/db_orang.sql
```

### Restore Semua Database
```bash
cd /opt/lampp/htdocs/kewer
# Create databases
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS kewer;"
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS db_alamat;"
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS db_orang;"

# Import dari folder database/
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat < database/db_alamat.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang.sql
```

### Reset Database
```bash
cd /opt/lampp/htdocs/kewer
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS kewer; CREATE DATABASE kewer;"
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS db_alamat; CREATE DATABASE db_alamat;"
/opt/lampp/bin/mysql -u root -proot -e "DROP DATABASE IF EXISTS db_orang; CREATE DATABASE db_orang;"
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat < database/db_alamat.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang.sql
```

### Cek Database Status
```bash
# Cek jumlah tabel ketiga database (base tables only)
/opt/lampp/bin/mysql -u root -proot -e "
SELECT 'kewer' as db, COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='kewer' AND TABLE_TYPE='BASE TABLE'
UNION ALL SELECT 'db_alamat', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_alamat' AND TABLE_TYPE='BASE TABLE'
UNION ALL SELECT 'db_orang', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_orang' AND TABLE_TYPE='BASE TABLE';
"

# Cek data utama
/opt/lampp/bin/mysql -u root -proot kewer -e "
SELECT 'users' as tbl, COUNT(*) as cnt FROM users
UNION ALL SELECT 'cabang', COUNT(*) FROM cabang
UNION ALL SELECT 'nasabah', COUNT(*) FROM nasabah
UNION ALL SELECT 'pinjaman', COUNT(*) FROM pinjaman
UNION ALL SELECT 'angsuran', COUNT(*) FROM angsuran
UNION ALL SELECT 'pembayaran', COUNT(*) FROM pembayaran
UNION ALL SELECT 'koperasi_activities', COUNT(*) FROM koperasi_activities;
"

# Cek cross-DB links
/opt/lampp/bin/mysql -u root -proot -e "
SELECT 'users linked' as item, COUNT(*) as cnt FROM kewer.users WHERE db_orang_person_id IS NOT NULL
UNION ALL SELECT 'nasabah linked', COUNT(*) FROM kewer.nasabah WHERE db_orang_user_id IS NOT NULL
UNION ALL SELECT 'cabang linked', COUNT(*) FROM kewer.cabang WHERE db_orang_person_id IS NOT NULL
UNION ALL SELECT 'people records', COUNT(*) FROM db_orang.people
UNION ALL SELECT 'addresses', COUNT(*) FROM db_orang.addresses;
"
```

### Cek Koneksi Database
```bash
/opt/lampp/bin/mysql -u root -proot -e "SELECT 'Connection OK' as status;"
```

### Database Files (database/)
- `kewer.sql` — Full export kewer (64 tabel + 3 views + data)
- `db_alamat.sql` — Referensi lokasi nasional + geospasial (24 tabel, 38 prov + 541 kab + 7,938 kec + 80,937 desa)
- `db_orang.sql` — Identitas orang + alamat + master data (20 tabel: people, addresses, people_phones, people_emails, people_documents, family_relations, people_audit_log, ref_agama, ref_jenis_kelamin, ref_golongan_darah, ref_status_perkawinan, ref_suku, ref_pekerjaan, ref_jenis_alamat, ref_jenis_identitas, ref_jenis_telepon, ref_jenis_email, ref_jenis_gelar, ref_jenis_relasi, ref_jenis_properti)

### 3-Database Architecture
| DB | Koneksi | Fungsi |
|---|---|---|
| `kewer` | `$conn` → `query()` | Transaksi koperasi, users, billing, usage, activities |
| `db_alamat` | `$conn_alamat` → `query_alamat()` | Referensi lokasi nasional + geospasial |
| `db_orang` | `$conn_orang` → `query_orang()` | Identitas orang + alamat + master data |

### Cross-DB Links
- `kewer.users.db_orang_person_id` → `db_orang.people.id`
- `kewer.nasabah.db_orang_user_id` → `db_orang.people.id`
- `kewer.cabang.db_orang_person_id` → `db_orang.people.id`
- `db_orang.addresses` → `db_alamat.provinces/regencies/districts/villages`
- `db_orang.addresses.jenis_alamat_id` → `db_orang.ref_jenis_alamat.id`
- `db_orang.people.agama_id` → `db_orang.ref_agama.id`
- `db_orang.people.jenis_kelamin_id` → `db_orang.ref_jenis_kelamin.id`
- `db_orang.people.golongan_darah_id` → `db_orang.ref_golongan_darah.id`
- `db_orang.people.suku_id` → `db_orang.ref_suku.id`
- `db_orang.people.status_perkawinan_id` → `db_orang.ref_status_perkawinan.id`
- `db_orang.people.pekerjaan_id` → `db_orang.ref_pekerjaan.id`
- `db_orang.people.jenis_identitas_id` → `db_orang.ref_jenis_identitas.id`
- Models use `LEFT JOIN db_alamat.provinces` etc. for location names
