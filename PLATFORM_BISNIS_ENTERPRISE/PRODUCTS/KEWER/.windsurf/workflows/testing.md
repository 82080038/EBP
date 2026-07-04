---
description: Testing procedures untuk aplikasi Kewer v2.5.0
---

# Testing Aplikasi Kewer (v2.5.0)

## API Testing

### Test API Endpoints dengan cURL
```bash
# Test dashboard API
curl -H "Authorization: Bearer kewer-api-token-2024" http://localhost/kewer/api/dashboard

# Test nasabah API
curl -H "Authorization: Bearer kewer-api-token-2024" http://localhost/kewer/api/nasabah

# Test pinjaman API
curl -H "Authorization: Bearer kewer-api-token-2024" http://localhost/kewer/api/pinjaman
```

### Test dengan Browser
- Buka Developer Tools → Network tab
- Navigasi ke setiap halaman
- Verifikasi XHR requests ke `/api/`
- Cek response JSON format

## Client-Side Rendering Testing

### Verifikasi Halaman
1. Buka setiap halaman yang sudah dikonversi:
   - dashboard.php
   - nasabah/index.php
   - pinjaman/index.php
   - angsuran/index.php
   - pembayaran/index.php
   - cabang/index.php
   - petugas/index.php
   - laporan/index.php
   - audit/index.php
   - pengeluaran/index.php
   - kas_bon/index.php
   - kas_petugas/index.php
   - setting_bunga/index.php
   - permissions/index.php
   - users/index.php
   - app_owner/settings.php

2. Cek:
   - Data dimuat via AJAX (loading spinner muncul)
   - Tabel dirender secara dinamis
   - Tidak ada PHP error di console
   - SweetAlert2 alerts berfungsi

### Testing JavaScript Console
1. Buka Developer Tools → Console tab
2. Verifikasi:
   - jQuery terdefinisi (bukan "jQuery is not defined")
   - KewerAPI object tersedia
   - Tidak ada JavaScript error
   - AJAX requests berhasil (Network tab)

### Testing API Endpoints
1. Buka Developer Tools → Network tab
2. Filter by XHR/fetch
3. Verifikasi:
   - Response format JSON dengan {success, data, error}
   - Status code 200 untuk success, 4xx/5xx untuk error
   - Response time reasonable (< 1s)

### Testing Form Submissions
1. Submit form pada halaman yang sudah dikonversi
2. Verifikasi:
   - Form submit via AJAX (bukan page reload)
   - SweetAlert2 alert muncul
   - Data terupdate tanpa refresh halaman
   - Loading state ditampilkan selama request

### Testing Error Handling
1. Simulasikan error (invalid data, network error)
2. Verifikasi:
   - Error message ditampilkan di SweetAlert2
   - Tidak ada native alert()
   - Error message jelas dan informatif
   - Form tidak reset jika error

### Testing Client-Side Rendering Pattern
**IMPORTANT**: Saat membuat atau memodifikasi halaman, ikuti pattern ini:

```javascript
// 1. Struktur halaman PHP minimal
<?php
require_once BASE_PATH . '/config/path.php';
requireLogin();
$page_title = 'Page Title';
?>
<?php include BASE_PATH . '/includes/sidebar.php'; ?>

<div id="alert-container"></div>
<div id="loading-spinner">
    <div class="spinner-border spinner-border-sm" role="status"></div>
</div>
<div id="data-container"></div>

<script>
// 2. Load data via API
$(document).ready(function() {
    loadData();
    setupForms();
});

function loadData() {
    window.KewerAPI.getData(params).done(response => {
        if (response.success) {
            renderData(response.data);
        } else {
            showAlert(response.error, 'danger');
        }
    }).fail(() => {
        showAlert('Gagal memuat data', 'danger');
    });
}

// 3. Render data dengan template literals
function renderData(data) {
    const html = `
        <table class="table">
            ${data.map(item => `
                <tr>
                    <td>${item.nama}</td>
                    <td>${item.status}</td>
                </tr>
            `).join('')}
        </table>
    `;
    $('#data-container').html(html);
}

// 4. Setup form submission via AJAX
function setupForms() {
    $('#formId').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: 'api/endpoint.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                showAlert('Data berhasil disimpan');
                loadData();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Gagal menyimpan data';
                showAlert(error, 'danger');
            }
        });
    });
}

// 5. Helper function untuk alerts
function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alert-container').html(alertHtml);
}
</script>
```

### Checklist untuk Testing Client-Side Rendering
- [ ] Halaman menggunakan loading spinner
- [ ] Data dimuat via AJAX (bukan PHP render)
- [ ] Form submit via AJAX (bukan page reload)
- [ ] Alert menggunakan SweetAlert2
- [ ] Error handling dengan showAlert()
- [ ] Tidak ada PHP query di halaman
- [ ] API endpoint mengembalikan JSON format {success, data, error}
- [ ] jQuery terdefinisi sebelum script lain
- [ ] Tidak ada JavaScript error di console
- [ ] Data dirender dengan template literals

## Manual Testing Checklist

### CRUD Operations
- [ ] Create data via modal/form
- [ ] Read data via table/list
- [ ] Update data via edit function
- [ ] Delete data dengan konfirmasi

### API Integration
- [ ] Response format seragam (success, data, error)
- [ ] Authentication token berfungsi
- [ ] Pagination (jika ada)
- [ ] Filter dan search berfungsi

### Role-Based Access
- [ ] Permissions diterapkan via API
- [ ] Role-based filters berfungsi
- [ ] Unauthorized access ditolak

## API Interoperability Testing

### Test dengan External Applications
```bash
# Test API dengan Postman atau curl
curl -X GET "http://localhost/kewer/api/nasabah" \
  -H "Authorization: Bearer kewer-api-token-2024" \
  -H "Content-Type: application/json"

# Test POST request
curl -X POST "http://localhost/kewer/api/nasabah" \
  -H "Authorization: Bearer kewer-api-token-2024" \
  -H "Content-Type: application/json" \
  -d '{"nama":"Test","alamat":"Test"}'
```

### Test Response Format
Verifikasi semua API endpoint mengembalikan format:
```json
{
  "success": true|false,
  "data": { ... },
  "error": "Error message if failed"
}
```

---

## Legacy Testing (Optional)

### 1. Quick Full Test (semua halaman + API)
```bash
# Test semua halaman koperasi (5 users × 11 pages = 55 tests)
total=0; fail=0
PAGES="dashboard.php pages/nasabah/index.php pages/nasabah/tambah.php pages/pinjaman/index.php pages/angsuran/index.php pages/petugas/index.php pages/petugas/tambah.php pages/cabang/index.php pages/cabang/tambah.php pages/pembayaran/index.php pages/users/index.php"
for user in patri mgr_pusat adm_pusat ptr_pngr1 krw_pngr; do
  C=$(curl -s -D - -X POST "http://localhost/kewer/login.php" -d "username=$user" -d "password=Kewer2024!" | grep -i "Set-Cookie.*PHPSESSID" | head -1 | sed "s/.*PHPSESSID=/PHPSESSID=/" | sed "s/;.*//")
  for page in $PAGES; do
    total=$((total+1))
    body=$(curl -s -b "$C" "http://localhost/kewer/$page")
    echo "$body" | grep -qi "Fatal error\|Parse error\|Uncaught" && { fail=$((fail+1)); echo "✗ $user/$page"; }
  done
done
echo "Koperasi: $((total-fail))/$total OK"

# Test appOwner pages (8 pages)
OC=$(curl -s -D - -X POST "http://localhost/kewer/login.php" -d "username=appowner" -d "password=AppOwner2024!" | grep -i "Set-Cookie.*PHPSESSID" | head -1 | sed "s/.*PHPSESSID=/PHPSESSID=/" | sed "s/;.*//")
ok=0
for page in dashboard.php approvals.php koperasi.php billing.php usage.php ai_advisor.php settings.php features.php; do
  body=$(curl -s -b "$OC" "http://localhost/kewer/pages/app_owner/$page")
  echo "$body" | grep -qi "Fatal error\|Parse error\|Uncaught" || ok=$((ok+1))
done
echo "appOwner: $ok/8 OK"
```

### 2. Database Integrity Test
```bash
/opt/lampp/bin/mysql -u root -proot -e "
-- Tabel counts
SELECT 'kewer' as db, COUNT(*) as tables FROM information_schema.TABLES WHERE TABLE_SCHEMA='kewer'
UNION ALL SELECT 'db_alamat_simple', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_alamat_simple'
UNION ALL SELECT 'db_orang', COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_orang';

-- Cross-DB links
SELECT 'users linked' as item, COUNT(*) as cnt FROM kewer.users WHERE db_orang_person_id IS NOT NULL
UNION ALL SELECT 'nasabah linked', COUNT(*) FROM kewer.nasabah WHERE db_orang_user_id IS NOT NULL
UNION ALL SELECT 'cabang linked', COUNT(*) FROM kewer.cabang WHERE db_orang_person_id IS NOT NULL
UNION ALL SELECT 'people records', COUNT(*) FROM db_orang.people
UNION ALL SELECT 'addresses', COUNT(*) FROM db_orang.addresses;

-- Data integrity
SELECT 'Orphan angsuran' as chk, COUNT(*) FROM kewer.angsuran a LEFT JOIN kewer.pinjaman p ON a.pinjaman_id=p.id WHERE p.id IS NULL
UNION ALL SELECT 'Orphan pembayaran', COUNT(*) FROM kewer.pembayaran pb LEFT JOIN kewer.angsuran a ON pb.angsuran_id=a.id WHERE a.id IS NULL
UNION ALL SELECT 'Invalid roles', COUNT(*) FROM kewer.users u LEFT JOIN kewer.ref_roles r ON u.role=r.role_kode WHERE r.role_kode IS NULL;
"
```

### 3. Alamat API Test
```bash
# Test cascade: provinces → regencies → districts → villages
curl -s "http://localhost/kewer/api/alamat.php?action=provinces" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'Provinces: {len(d.get(\"data\",d))}')"
curl -s "http://localhost/kewer/api/alamat.php?action=regencies&province_id=3" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'Regencies: {len(d.get(\"data\",d))}')"
```

### 4. db_orang Integration Test
```bash
/opt/lampp/bin/php -r "
require_once '/opt/lampp/htdocs/kewer/config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/people_helper.php';
\$pid = createPersonWithAddress(
    ['nama' => 'Test Person', 'telp' => '08123'],
    ['label' => 'test', 'street_address' => 'Test St', 'province_id' => 3]
);
echo 'Create: ' . (\$pid ? 'OK' : 'FAIL') . \"\n\";
if (\$pid) {
    \$addr = getPersonAddresses(\$pid);
    echo 'Province: ' . (\$addr[0]['province_name'] ?? 'NULL') . \"\n\";
    query_orang('DELETE FROM addresses WHERE person_id = ?', [\$pid]);
    query_orang('DELETE FROM people WHERE id = ?', [\$pid]);
    echo \"Cleanup OK\n\";
}
"
```

### 5. Error Log Check
```bash
cat /opt/lampp/htdocs/kewer/logs/error.log | wc -l
# Harus 0 lines setelah fresh test
```

### 6. Manual Testing Checklist
- [ ] Login semua role (appowner, patri, mgr_pusat, adm_pusat, ptr_pngr1, krw_pngr)
- [ ] CRUD nasabah (tambah, edit, hapus)
- [ ] CRUD pinjaman (pengajuan, approve, angsuran)
- [ ] Pembayaran angsuran
- [ ] Alamat dropdown cascade (provinsi → kabupaten → kecamatan → desa)
- [ ] appOwner: approvals, billing, usage, AI advisor
- [ ] Field officer activities
- [ ] Cek cross-DB: nasabah detail menampilkan nama provinsi/kabupaten
