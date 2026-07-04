# MODUL 24 — TESTING SYSTEM

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Strategi testing untuk memastikan kualitas aplikasi: unit test, integration test,
UI test, dan security test.

---

## 2. JENIS TESTING

| Tipe | Scope | Tools |
|------|-------|-------|
| Unit Test | Fungsi individual PHP | PHPUnit (opsional) / manual |
| Integration Test | Alur multi-modul | Manual / script PHP |
| API Test | Endpoint AJAX | Postman / curl |
| UI Test | Tampilan & interaksi | Browser manual |
| Security Test | Kerentanan | Manual / OWASP checklist |
| Performance Test | Respons & beban | Apache Bench / JMeter |

---

## 3. TEST CASES — AUTENTIKASI

| ID | Skenario | Input | Expected |
|----|----------|-------|----------|
| TC-AUTH-01 | Login valid | email+password benar | Redirect dashboard |
| TC-AUTH-02 | Login invalid | password salah | Error message |
| TC-AUTH-03 | Login email tidak ada | email tidak terdaftar | Error message |
| TC-AUTH-04 | Register wisatawan | data valid | Akun dibuat, redirect login |
| TC-AUTH-05 | Register email duplikat | email sudah ada | Error: email sudah terdaftar |
| TC-AUTH-06 | Register guide | data valid + dokumen | Akun pending, menunggu approval |
| TC-AUTH-07 | Logout | - | Session destroyed, redirect login |
| TC-AUTH-08 | Akses halaman tanpa login | - | Redirect ke login |
| TC-AUTH-09 | Akses halaman admin sebagai wisatawan | - | 403 Forbidden |
| TC-AUTH-10 | Session timeout 30 menit | idle 30 menit | Auto logout |

---

## 4. TEST CASES — BOOKING

| ID | Skenario | Expected |
|----|----------|----------|
| TC-BOOK-01 | Booking guide valid | Booking pending + transaction pending |
| TC-BOOK-02 | Booking di tanggal sudah dibooking | Error: tanggal tidak tersedia |
| TC-BOOK-03 | Booking guide tidak verified | Error: guide tidak tersedia |
| TC-BOOK-04 | Kalkulasi biaya < 8 jam | duration × hourly_rate |
| TC-BOOK-05 | Kalkulasi biaya ≥ 8 jam | ceil(duration/8) × daily_rate |
| TC-BOOK-06 | Guide accept booking | Status confirmed, schedule booked |
| TC-BOOK-07 | Guide reject booking | Status rejected, notif ke wisatawan |
| TC-BOOK-08 | Wisatawan cancel booking | Status cancelled, schedule freed |
| TC-BOOK-09 | Upload bukti pembayaran | payment_proof saved |
| TC-BOOK-10 | Admin verifikasi pembayaran | payment_status=paid, booking confirmed |

---

## 5. TEST CASES — TIKET

| ID | Skenario | Expected |
|----|----------|----------|
| TC-TKT-01 | Beli tiket valid | Order pending + transaction |
| TC-TKT-02 | Beli tiket melebihi kuota | Error: kuota penuh |
| TC-TKT-03 | Verifikasi tiket valid | Status used |
| TC-TKT-04 | Verifikasi tiket sudah dipakai | Error: sudah digunakan |
| TC-TKT-05 | Verifikasi kode tidak ada | Error: tidak ditemukan |

---

## 6. TEST CASES — API

### 6.1 Login API

```bash
curl -X POST http://localhost/wisata/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@tourguide.app","password":"secret"}'

# Expected: 200, {"status":"success","data":{...}}
```

### 6.2 Get Markers

```bash
curl http://localhost/wisata/api/map/markers

# Expected: 200, {"status":"success","data":[...]}
```

### 6.3 Create Booking

```bash
curl -X POST http://localhost/wisata/api/booking/create \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: <token>" \
  -d '{"guide_id":1,"booking_date":"2026-07-15","start_time":"09:00","duration_hours":4}'

# Expected: 201, {"status":"success","data":{"booking_id":1,...}}
```

---

## 7. TEST CASES — SECURITY

| ID | Skenario | Expected |
|----|----------|----------|
| TC-SEC-01 | SQL injection di login | Tidak berhasil, prepared statement |
| TC-SEC-02 | XSS di komentar review | Tidak ter-eksekusi, htmlspecialchars |
| TC-SEC-03 | CSRF tanpa token | 419 CSRF token mismatch |
| TC-SEC-04 | Akses API tanpa login | 401 Unauthorized |
| TC-SEC-05 | Akses endpoint admin sebagai wisatawan | 403 Forbidden |
| TC-SEC-06 | Upload file PHP disguised | Error: tipe tidak diizinkan |
| TC-SEC-07 | Rate limit exceeded | 429 Too Many Requests |

---

## 8. TEST CASES — UI/UX

| ID | Skenario | Expected |
|----|----------|----------|
| TC-UI-01 | Halaman responsive mobile 360px | Layout tidak overflow |
| TC-UI-02 | Halaman responsive desktop 1920px | Layout rapi |
| TC-UI-03 | Peta dimuat < 3 detik | Marker tampil |
| TC-UI-04 | Form validasi error | Pesan error jelas |
| TC-UI-05 | AJAX loading indicator | Spinner tampil saat request |
| TC-UI-06 | SweetAlert confirm delete | Konfirmasi sebelum hapus |
| TC-UI-07 | DataTables search & pagination | Berfungsi normal |
| TC-UI-08 | Notifikasi badge update | Badge tampil real-time |

---

## 9. PHP UNIT TEST (OPSIONAL)

```php
<?php
// tests/AuthTest.php
class AuthTest {
    private $userModel;

    public function setUp() {
        $this->userModel = new User();
    }

    public function testPasswordHash() {
        $hash = password_hash('test123', PASSWORD_BCRYPT);
        assert(password_verify('test123', $hash) === true);
        assert(password_verify('wrong', $hash) === false);
        echo "testPasswordHash: PASSED\n";
    }

    public function testLoginValid() {
        $user = $this->userModel->findByEmail('admin@tourguide.app');
        assert($user !== false);
        assert(password_verify('admin123', $user['password']));
        echo "testLoginValid: PASSED\n";
    }

    public function testLoginInvalid() {
        $user = $this->userModel->findByEmail('nonexistent@test.com');
        assert($user === false);
        echo "testLoginInvalid: PASSED\n";
    }
}

$test = new AuthTest();
$test->setUp();
$test->testPasswordHash();
$test->testLoginValid();
$test->testLoginInvalid();
echo "\nAll auth tests passed!\n";
```

---

## 10. PERFORMANCE TEST

```bash
# Apache Bench — 100 requests, 10 concurrent
ab -n 100 -c 10 http://localhost/wisata/api/map/markers

# Expected:
# Time per request: < 500ms
# Failed requests: 0
# Requests per second: > 20
```

---

## 11. CHECKLIST TESTING

- [ ] Autentikasi: login, register, logout, session
- [ ] Booking: create, accept, reject, cancel, complete
- [ ] Tiket: buy, verify, quota check
- [ ] Hotel: register, search, book
- [ ] Restoran: register, order, status update
- [ ] Event: create, register, reminder
- [ ] Map: markers, nearby, geolocation
- [ ] AI Chat: intent detection, response
- [ ] Notifikasi: send, read, badge
- [ ] Report: dashboard, export
- [ ] Security: SQL injection, XSS, CSRF, RBAC
- [ ] UI: responsive, loading, validation
- [ ] Performance: < 500ms API, < 2s page

---

> **Modul Selanjutnya:** `25_DEPLOYMENT_SERVER.md`
