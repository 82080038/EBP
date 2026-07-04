---
description: Deployment workflow untuk aplikasi Kewer
---

## Deployment Workflow

### 1. Pre-Deployment Checklist
- [ ] Backup 3 database (kewer, db_alamat_simple, db_orang)
- [ ] Test semua halaman (55 koperasi + 7 appOwner pages)
- [ ] Test API endpoints (25 endpoints)
- [ ] Cek error log (harus 0 errors)
- [ ] Verify cross-DB links (users/nasabah/cabang ↔ db_orang)

### 2. Backup Database
```bash
cd /opt/lampp/htdocs/kewer
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers kewer > database/kewer.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_alamat_simple > database/db_alamat_simple.sql
/opt/lampp/bin/mysqldump -u root -proot --routines --triggers db_orang > database/db_orang.sql
```

### 3. Push ke GitHub
```bash
cd /opt/lampp/htdocs/kewer
git add -A
git commit -m "deploy: $(date +%Y-%m-%d)"
git push origin main
```

### 4. Set Permissions
```bash
echo "8208" | sudo -S chmod -R 755 /opt/lampp/htdocs/kewer
echo "8208" | sudo -S chmod 777 uploads
```

### 5. Restart XAMPP
```bash
echo "8208" | sudo -S /opt/lampp/lampp restart
```

### 6. Post-Deployment Verification
```bash
# Cek database
/opt/lampp/bin/mysql -u root -proot -e "
SELECT 'kewer' as db, COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='kewer'
UNION ALL SELECT 'db_alamat_simple', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_alamat_simple'
UNION ALL SELECT 'db_orang', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_orang';
"

# Akses: http://localhost/kewer/login.php
```

### 7. Post-Deployment Testing
- [ ] Login sebagai appowner (AppOwner2024!) — 7 pages
- [ ] Login sebagai patri (Kewer2024!) — dashboard, nasabah, pinjaman
- [ ] Login sebagai petugas — field activities
- [ ] Cek alamat API: /api/alamat.php?action=provinces
- [ ] Cek error log: `tail /opt/lampp/htdocs/kewer/logs/error.log`

### Rollback
```bash
cd /opt/lampp/htdocs/kewer
git checkout HEAD~1 -- .
/opt/lampp/bin/mysql -u root -proot kewer < database/kewer.sql
/opt/lampp/bin/mysql -u root -proot db_alamat_simple < database/db_alamat_simple.sql
/opt/lampp/bin/mysql -u root -proot db_orang < database/db_orang.sql
echo "8208" | sudo -S /opt/lampp/lampp restart
```

### Deployment Notes
- 3 database: kewer (49+3), db_alamat_simple (4), db_orang (19+6)
- 25 API endpoints
- 24 page modules + 7 appOwner pages
- Frontend: Bootstrap 5.3, DataTable.js, SweetAlert2, Select2, Flatpickr
