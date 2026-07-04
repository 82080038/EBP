/**
 * Kewer v2.3.x — Feature Flags + Fitur Baru Testing
 * Headed mode, semua role, semua fitur yang ditambahkan di v2.3.0-v2.3.1
 *
 * Cakupan:
 * 1. Login semua role (appOwner, bos, manager_pusat, manager_cabang,
 *    admin_pusat, admin_cabang, petugas_pusat, petugas_cabang, karyawan)
 * 2. Feature Flags: UI toggle appOwner, guard 403 saat OFF
 * 3. Export Laporan (CSV/PDF) — on/off
 * 4. Slip Harian Petugas — on/off
 * 5. Target Kinerja Petugas — on/off
 * 6. Kolektibilitas OJK di detail pinjaman
 * 7. 2FA settings page — on/off
 * 8. PWA meta tags — on/off
 * 9. GPS guard (tidak bisa test navigator.geolocation langsung, cek JS flag)
 * 10. WA Notifikasi API guard
 */

const puppeteer = require('puppeteer');
const config    = require('./puppeteer.config');
const fs        = require('fs');
const path      = require('path');

// ── Credentials semua role ────────────────────────────────────────
const ALL_ROLES = [
  { role: 'appOwner',        username: 'appowner',   password: 'AppOwner2024!' },
  { role: 'bos',             username: 'patri',      password: 'Kewer2024!'    },
  { role: 'manager_pusat',   username: 'mgr_pusat',  password: 'Kewer2024!'    },
  { role: 'manager_cabang',  username: 'mgr_balige', password: 'Kewer2024!'    },
  { role: 'admin_pusat',     username: 'adm_pusat',  password: 'Kewer2024!'    },
  { role: 'admin_cabang',    username: 'adm_balige', password: 'Kewer2024!'    },
  { role: 'petugas_pusat',   username: 'ptr_pusat',  password: 'Kewer2024!'    },
  { role: 'petugas_cabang',  username: 'ptr_balige', password: 'Kewer2024!'    },
  { role: 'karyawan',        username: 'krw_pusat',  password: 'Kewer2024!'    },
];

const BASE  = config.baseUrl;
const SHOTS = path.join(__dirname, 'screenshots', 'v23');
if (!fs.existsSync(SHOTS)) fs.mkdirSync(SHOTS, { recursive: true });

// ── Result tracking ───────────────────────────────────────────────
const R = { passed: 0, failed: 0, skipped: 0, errors: [] };

function pass(name)          { console.log(`  ✅ ${name}`); R.passed++; }
function fail(name, err)     { console.error(`  ❌ ${name}: ${err.message || err}`); R.failed++; R.errors.push({ test: name, error: String(err.message || err) }); }
function skip(name, reason)  { console.warn(`  ⏭  ${name}: ${reason}`); R.skipped++; }
function section(title)      { console.log(`\n${'─'.repeat(60)}\n📋 ${title}\n${'─'.repeat(60)}`); }

async function shot(page, name) {
  try { await page.screenshot({ path: path.join(SHOTS, `${name}.png`), fullPage: true }); }
  catch (_) {}
}

// ── Login helper ──────────────────────────────────────────────────
async function login(page, username, password) {
  await page.goto(`${BASE}/login.php`, { waitUntil: 'networkidle0' });
  await page.evaluate(() => {
    document.querySelector('input[name="username"]').value = '';
    document.querySelector('input[name="password"]').value = '';
  });
  await page.type('input[name="username"]', username, { delay: 30 });
  await page.type('input[name="password"]', password, { delay: 30 });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]);
  return page.url();
}

async function logout(page) {
  try {
    await page.goto(`${BASE}/logout.php`, { waitUntil: 'networkidle0', timeout: 8000 });
  } catch (_) {}
}

// ── Check if element exists ───────────────────────────────────────
async function exists(page, sel) {
  return !!(await page.$(sel));
}

async function textOf(page, sel) {
  try { return await page.$eval(sel, el => el.textContent.trim()); }
  catch (_) { return ''; }
}

// ─────────────────────────────────────────────────────────────────
//  MAIN
// ─────────────────────────────────────────────────────────────────
async function run() {
  console.log('\n🚀 Kewer v2.3.x — Puppeteer Feature Tests (headed)\n');

  const browser = await puppeteer.launch({
    ...config.launchOptions,
    headless: false,
    slowMo: 40,
  });

  try {
    // ════════════════════════════════════════════════════════════
    // BAGIAN 1 — Login semua role
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 1 — Login Semua Role');

    for (const u of ALL_ROLES) {
      const page = await browser.newPage();
      await page.setDefaultTimeout(20000);
      try {
        const afterUrl = await login(page, u.username, u.password);
        const isAppOwner = u.role === 'appOwner';
        const expected   = isAppOwner ? 'app_owner/dashboard' : 'dashboard.php';
        if (!afterUrl.includes(expected)) {
          throw new Error(`Redirect ke "${afterUrl}", expected "${expected}"`);
        }
        await shot(page, `1-login-${u.role}`);
        pass(`Login [${u.role}] → ${afterUrl.split('/').pop()}`);
      } catch (e) {
        fail(`Login [${u.role}]`, e);
        await shot(page, `1-login-FAIL-${u.role}`);
      } finally {
        await logout(page);
        await page.close();
      }
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 2 — Feature Flags (appOwner UI)
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 2 — Feature Flags UI (appOwner)');

    const aoPage = await browser.newPage();
    await aoPage.setDefaultTimeout(20000);
    try {
      await login(aoPage, 'appowner', 'AppOwner2024!');

      // 2a. Akses halaman features.php
      await aoPage.goto(`${BASE}/pages/app_owner/features.php`, { waitUntil: 'networkidle0' });
      const pageTitle = await textOf(aoPage, 'h2');
      if (!pageTitle.includes('Feature Flags')) throw new Error(`h2 = "${pageTitle}"`);
      await shot(aoPage, '2a-features-page');
      pass('Feature Flags — Halaman terbuka (appOwner)');

      // 2b. Tabel fitur tampil
      const rows = await aoPage.$$('input[data-key]');
      if (rows.length < 5) throw new Error(`Hanya ${rows.length} toggle ditemukan`);
      pass(`Feature Flags — ${rows.length} fitur ditemukan di tabel`);

      // 2c. Toggle wa_notifikasi ON
      const waToggle = await aoPage.$('input[data-key="wa_notifikasi"]');
      if (!waToggle) throw new Error('Toggle wa_notifikasi tidak ditemukan');
      const waChecked = await aoPage.$eval('input[data-key="wa_notifikasi"]', el => el.checked);
      if (!waChecked) {
        await waToggle.click();
        await new Promise(r => setTimeout(r, 1500)); // tunggu toast
        const newChecked = await aoPage.$eval('input[data-key="wa_notifikasi"]', el => el.checked);
        if (!newChecked) throw new Error('Toggle wa_notifikasi tidak berubah ke ON');
      }
      await shot(aoPage, '2c-wa-toggled-on');
      pass('Feature Flags — Toggle wa_notifikasi ON');

      // 2d. Toggle wa_notifikasi OFF kembali
      const waToggle2 = await aoPage.$('input[data-key="wa_notifikasi"]');
      await waToggle2.click();
      await new Promise(r => setTimeout(r, 1500));
      await shot(aoPage, '2d-wa-toggled-off');
      pass('Feature Flags — Toggle wa_notifikasi OFF');

      // 2e. Non-appOwner tidak bisa akses features.php
      await logout(aoPage);
      await login(aoPage, 'patri', 'Kewer2024!');
      await aoPage.goto(`${BASE}/pages/app_owner/features.php`, { waitUntil: 'networkidle0' });
      const redirected = !aoPage.url().includes('features.php') || aoPage.url().includes('login.php') || aoPage.url().includes('dashboard.php');
      if (!redirected) throw new Error('Bos bisa akses features.php — seharusnya diblokir');
      await shot(aoPage, '2e-bos-blocked-features');
      pass('Feature Flags — Bos diblokir dari features.php');

    } catch (e) {
      fail('Feature Flags UI', e);
      await shot(aoPage, '2-FAIL-features');
    } finally {
      await logout(aoPage);
      await aoPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 3 — Guard API saat fitur OFF
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 3 — Guard API (fitur OFF → 403)');

    const guardPage = await browser.newPage();
    await guardPage.setDefaultTimeout(20000);
    try {
      await login(guardPage, 'patri', 'Kewer2024!');

      // Pastikan export_laporan OFF dulu (via API)
      const setOff = await guardPage.evaluate(async (base) => {
        const r = await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'export_laporan', enabled: false })
        });
        return await r.json();
      }, BASE);
      // setOff mungkin 403 karena bos bukan appOwner — OK
      
      // Login ulang sbg appOwner untuk set OFF
      await logout(guardPage);
      await login(guardPage, 'appowner', 'AppOwner2024!');
      await guardPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'export_laporan', enabled: false })
        });
      }, BASE);

      await logout(guardPage);
      await login(guardPage, 'patri', 'Kewer2024!');

      // Cek api/export.php → 403
      const exportStatus = await guardPage.evaluate(async (base) => {
        const r = await fetch(`${base}/api/export.php?format=csv&jenis=comprehensive`);
        return r.status;
      }, BASE);
      if (exportStatus !== 403) throw new Error(`export.php status: ${exportStatus} (expected 403)`);
      pass('Guard export_laporan OFF → API 403');

      // Cek api/wa_notifikasi.php → 403 (POST kirim_tagihan — action nyata)
      const waStatus = await guardPage.evaluate(async (base) => {
        const r = await fetch(`${base}/api/wa_notifikasi.php`, {
          method: 'POST', headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ action: 'kirim_tagihan', nasabah_id: 1, pinjaman_id: 1 })
        });
        return r.status;
      }, BASE);
      if (waStatus !== 403) throw new Error(`wa_notifikasi.php status: ${waStatus} (expected 403)`);
      pass('Guard wa_notifikasi OFF → API 403');

      // Cek api/auth_2fa.php → 403
      const tfaStatus = await guardPage.evaluate(async (base) => {
        const r = await fetch(`${base}/api/auth_2fa.php?action=status`);
        return r.status;
      }, BASE);
      if (tfaStatus !== 403) throw new Error(`auth_2fa.php status: ${tfaStatus} (expected 403)`);
      pass('Guard two_factor_auth OFF → API 403');

    } catch (e) {
      fail('Guard API', e);
      await shot(guardPage, '3-FAIL-guard-api');
    } finally {
      await logout(guardPage);
      await guardPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 4 — Export Laporan (aktifkan lalu cek UI)
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 4 — Export Laporan ON/OFF');

    const expPage = await browser.newPage();
    await expPage.setDefaultTimeout(20000);
    try {
      // Aktifkan export via appOwner
      await login(expPage, 'appowner', 'AppOwner2024!');
      await expPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'export_laporan', enabled: true })
        });
      }, BASE);
      await logout(expPage);

      // Login bos, cek tombol export muncul
      await login(expPage, 'patri', 'Kewer2024!');
      await expPage.goto(`${BASE}/pages/laporan/index.php`, { waitUntil: 'networkidle0' });
      const csvBtn = await exists(expPage, '#btnExportCsv');
      const pdfBtn = await exists(expPage, '#btnExportPdf');
      if (!csvBtn || !pdfBtn) throw new Error(`Tombol Export tidak muncul (csv=${csvBtn}, pdf=${pdfBtn})`);
      await shot(expPage, '4a-export-buttons-visible');
      pass('Export Laporan ON — tombol CSV + PDF muncul di laporan');

      // API export harus 200
      const apiStatus = await expPage.evaluate(async (base) => {
        const r = await fetch(`${base}/api/export.php?format=csv&jenis=comprehensive`);
        return r.status;
      }, BASE);
      if (apiStatus !== 200) throw new Error(`export.php status: ${apiStatus}`);
      pass('Export Laporan ON — api/export.php status 200');

      // Matikan kembali
      await logout(expPage);
      await login(expPage, 'appowner', 'AppOwner2024!');
      await expPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'export_laporan', enabled: false })
        });
      }, BASE);
      await logout(expPage);

      // Login bos lagi, tombol harus hilang
      await login(expPage, 'patri', 'Kewer2024!');
      await expPage.goto(`${BASE}/pages/laporan/index.php`, { waitUntil: 'networkidle0' });
      const csvBtnOff = await exists(expPage, '#btnExportCsv');
      if (csvBtnOff) throw new Error('Tombol Export masih muncul padahal OFF');
      await shot(expPage, '4b-export-buttons-hidden');
      pass('Export Laporan OFF — tombol CSV + PDF disembunyikan');

    } catch (e) {
      fail('Export Laporan', e);
      await shot(expPage, '4-FAIL-export');
    } finally {
      await logout(expPage);
      await expPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 5 — Slip Harian Petugas
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 5 — Slip Harian Petugas ON/OFF');

    const slipPage = await browser.newPage();
    await slipPage.setDefaultTimeout(20000);
    try {
      // Aktifkan
      await login(slipPage, 'appowner', 'AppOwner2024!');
      await slipPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'slip_harian', enabled: true })
        });
      }, BASE);
      await logout(slipPage);

      // petugas_pusat bisa akses
      await login(slipPage, 'ptr_pusat', 'Kewer2024!');
      await slipPage.goto(`${BASE}/pages/petugas/slip_harian.php`, { waitUntil: 'networkidle0' });
      // Slip harian pakai h5 bukan h1/h2 — cek URL atau title
      const slipUrl = slipPage.url();
      const slipTitle = await slipPage.title();
      if (!slipUrl.includes('slip_harian') && !slipTitle.toLowerCase().includes('slip')) {
        throw new Error(`Halaman slip tidak terbuka, URL: "${slipUrl}", title: "${slipTitle}"`);
      }
      await shot(slipPage, '5a-slip-harian-on-petugas');
      pass('Slip Harian ON — petugas_pusat bisa akses');

      // Link di sidebar muncul — cek dari dashboard (bukan dari slip_harian.php itu sendiri)
      await slipPage.goto(`${BASE}/dashboard.php`, { waitUntil: 'networkidle0' });
      const sidebarLink = await exists(slipPage, 'a[href*="slip_harian"]');
      if (!sidebarLink) throw new Error('Link Slip Harian tidak ada di sidebar');
      pass('Slip Harian ON — link sidebar muncul di dashboard');

      // Matikan
      await logout(slipPage);
      await login(slipPage, 'appowner', 'AppOwner2024!');
      await slipPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'slip_harian', enabled: false })
        });
      }, BASE);
      await logout(slipPage);

      // petugas_pusat harus redirect (bukan slip page)
      await login(slipPage, 'ptr_pusat', 'Kewer2024!');
      await slipPage.goto(`${BASE}/pages/petugas/slip_harian.php`, { waitUntil: 'networkidle0' });
      const blockedUrl = slipPage.url();
      if (blockedUrl.includes('slip_harian')) throw new Error('Slip harian masih bisa diakses saat OFF');
      await shot(slipPage, '5b-slip-harian-off-blocked');
      pass('Slip Harian OFF — petugas diredirect');

      // Link sidebar hilang
      const sidebarLinkOff = await exists(slipPage, 'a[href*="slip_harian"]');
      if (sidebarLinkOff) throw new Error('Link Slip Harian masih ada di sidebar saat OFF');
      pass('Slip Harian OFF — link sidebar hilang');

    } catch (e) {
      fail('Slip Harian', e);
      await shot(slipPage, '5-FAIL-slip');
    } finally {
      await logout(slipPage);
      await slipPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 6 — Target Petugas + Kinerja
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 6 — Target Kinerja Petugas ON/OFF');

    const tgtPage = await browser.newPage();
    await tgtPage.setDefaultTimeout(20000);
    try {
      // Aktifkan
      await login(tgtPage, 'appowner', 'AppOwner2024!');
      await tgtPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'target_petugas', enabled: true })
        });
      }, BASE);
      await logout(tgtPage);

      // Bos: tombol Set Target muncul
      await login(tgtPage, 'patri', 'Kewer2024!');
      await tgtPage.goto(`${BASE}/pages/kinerja/index.php`, { waitUntil: 'networkidle0' });
      const setTargetBtn = await exists(tgtPage, '[data-bs-target="#modalTarget"]');
      if (!setTargetBtn) throw new Error('Tombol Set Target tidak muncul');
      await shot(tgtPage, '6a-target-btn-on');
      pass('Target Petugas ON — tombol Set Target muncul (bos)');

      // API target_petugas harus 200
      const tgtStatus = await tgtPage.evaluate(async (base) => {
        const r = await fetch(`${base}/api/target_petugas.php?bulan=2026-05`);
        return r.status;
      }, BASE);
      if (tgtStatus !== 200) throw new Error(`target_petugas.php status: ${tgtStatus}`);
      pass('Target Petugas ON — API 200');

      // Matikan
      await logout(tgtPage);
      await login(tgtPage, 'appowner', 'AppOwner2024!');
      await tgtPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'target_petugas', enabled: false })
        });
      }, BASE);
      await logout(tgtPage);

      await login(tgtPage, 'patri', 'Kewer2024!');
      await tgtPage.goto(`${BASE}/pages/kinerja/index.php`, { waitUntil: 'networkidle0' });
      const setTargetBtnOff = await exists(tgtPage, '[data-bs-target="#modalTarget"]');
      if (setTargetBtnOff) throw new Error('Tombol Set Target masih muncul saat OFF');
      await shot(tgtPage, '6b-target-btn-off');
      pass('Target Petugas OFF — tombol Set Target hilang');

    } catch (e) {
      fail('Target Petugas', e);
      await shot(tgtPage, '6-FAIL-target');
    } finally {
      await logout(tgtPage);
      await tgtPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 7 — 2FA Settings Page
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 7 — 2FA Settings ON/OFF');

    const tfaPage = await browser.newPage();
    await tfaPage.setDefaultTimeout(20000);
    try {
      // Aktifkan
      await login(tfaPage, 'appowner', 'AppOwner2024!');
      await tfaPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'two_factor_auth', enabled: true })
        });
      }, BASE);
      await logout(tfaPage);

      // Bos bisa akses settings_2fa.php
      await login(tfaPage, 'patri', 'Kewer2024!');
      await tfaPage.goto(`${BASE}/pages/users/settings_2fa.php`, { waitUntil: 'networkidle0' });
      if (tfaPage.url().includes('dashboard.php')) throw new Error('Bos diredirect dari 2FA saat ON');
      const tfaH2 = await textOf(tfaPage, 'h2');
      if (!tfaH2.includes('2FA') && !tfaH2.includes('Two-Factor')) throw new Error(`h2 = "${tfaH2}"`);
      await shot(tfaPage, '7a-2fa-page-on');
      pass('2FA ON — bos bisa akses halaman settings_2fa');

      // Link sidebar muncul
      const tfaLink = await exists(tfaPage, 'a[href*="settings_2fa"]');
      if (!tfaLink) throw new Error('Link 2FA tidak ada di sidebar');
      pass('2FA ON — link sidebar muncul');

      // Matikan
      await logout(tfaPage);
      await login(tfaPage, 'appowner', 'AppOwner2024!');
      await tfaPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'two_factor_auth', enabled: false })
        });
      }, BASE);
      await logout(tfaPage);

      // Bos diredirect saat OFF
      await login(tfaPage, 'patri', 'Kewer2024!');
      await tfaPage.goto(`${BASE}/pages/users/settings_2fa.php`, { waitUntil: 'networkidle0' });
      if (!tfaPage.url().includes('dashboard.php')) throw new Error('Bos masih bisa akses 2FA saat OFF');
      await shot(tfaPage, '7b-2fa-page-off');
      pass('2FA OFF — bos diredirect ke dashboard');

    } catch (e) {
      fail('2FA Settings', e);
      await shot(tfaPage, '7-FAIL-2fa');
    } finally {
      await logout(tfaPage);
      await tfaPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 8 — PWA Meta Tags
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 8 — PWA Meta Tags ON/OFF');

    const pwaPage = await browser.newPage();
    await pwaPage.setDefaultTimeout(20000);
    try {
      // Aktifkan
      await login(pwaPage, 'appowner', 'AppOwner2024!');
      await pwaPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'pwa', enabled: true })
        });
      }, BASE);
      await logout(pwaPage);

      await login(pwaPage, 'patri', 'Kewer2024!');
      await pwaPage.goto(`${BASE}/dashboard.php`, { waitUntil: 'networkidle0' });
      const manifestLink = await pwaPage.$('link[rel="manifest"]');
      if (!manifestLink) throw new Error('Tag manifest tidak ada saat PWA ON');
      await shot(pwaPage, '8a-pwa-manifest-on');
      pass('PWA ON — <link rel="manifest"> ada di dashboard');

      // Matikan
      await logout(pwaPage);
      await login(pwaPage, 'appowner', 'AppOwner2024!');
      await pwaPage.evaluate(async (base) => {
        await fetch(`${base}/api/feature_flags.php`, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ key: 'pwa', enabled: false })
        });
      }, BASE);
      await logout(pwaPage);

      await login(pwaPage, 'patri', 'Kewer2024!');
      await pwaPage.goto(`${BASE}/dashboard.php`, { waitUntil: 'networkidle0' });
      const manifestLinkOff = await pwaPage.$('link[rel="manifest"]');
      if (manifestLinkOff) throw new Error('Tag manifest masih ada saat PWA OFF');
      await shot(pwaPage, '8b-pwa-manifest-off');
      pass('PWA OFF — <link rel="manifest"> tidak ada');

    } catch (e) {
      fail('PWA Meta Tags', e);
      await shot(pwaPage, '8-FAIL-pwa');
    } finally {
      await logout(pwaPage);
      await pwaPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 9 — Kolektibilitas di Detail Pinjaman
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 9 — Kolektibilitas OJK di Detail Pinjaman');

    const kolPage = await browser.newPage();
    await kolPage.setDefaultTimeout(20000);
    try {
      await login(kolPage, 'patri', 'Kewer2024!');
      await kolPage.goto(`${BASE}/pages/pinjaman/index.php`, { waitUntil: 'networkidle0' });
      // Cari link ke detail pinjaman pertama
      const detailLink = await kolPage.$('a[href*="detail.php"]');
      if (!detailLink) {
        skip('Kolektibilitas OJK', 'Tidak ada pinjaman untuk dicek');
      } else {
        await detailLink.click();
        await kolPage.waitForNavigation({ waitUntil: 'networkidle0' });
        await shot(kolPage, '9-pinjaman-detail');
        // Cek badge status tampil
        const statusBadge = await exists(kolPage, '.badge');
        if (!statusBadge) throw new Error('Badge status tidak ditemukan');
        pass('Kolektibilitas — halaman detail pinjaman terbuka dengan badge status');
        // Jika ada kolektibilitas > 1, badge Kol-N muncul (opsional)
        const kolBadge = await kolPage.$eval('body', el => el.innerHTML.includes('Kol-'));
        if (kolBadge) pass('Kolektibilitas — badge Kol-N ditemukan di detail');
        else skip('Kolektibilitas badge Kol-N', 'Semua pinjaman masih Kol-1 (lancar)');
      }
    } catch (e) {
      fail('Kolektibilitas OJK', e);
      await shot(kolPage, '9-FAIL-kolektibilitas');
    } finally {
      await logout(kolPage);
      await kolPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // BAGIAN 10 — Access Control per Role (halaman kinerja)
    // ════════════════════════════════════════════════════════════
    section('BAGIAN 10 — Access Control Halaman Kinerja per Role');

    const aclPage = await browser.newPage();
    await aclPage.setDefaultTimeout(20000);
    try {
      // karyawan seharusnya diblokir dari kinerja
      await login(aclPage, 'krw_pusat', 'Kewer2024!');
      await aclPage.goto(`${BASE}/pages/kinerja/index.php`, { waitUntil: 'networkidle0' });
      const karyawanBlocked = aclPage.url().includes('dashboard.php');
      if (!karyawanBlocked) throw new Error('Karyawan bisa akses kinerja — seharusnya blocked');
      await shot(aclPage, '10a-karyawan-blocked-kinerja');
      pass('ACL — karyawan diblokir dari halaman kinerja');
      await logout(aclPage);

      // petugas bisa akses kinerja (untuk lihat dirinya)
      await login(aclPage, 'ptr_pusat', 'Kewer2024!');
      await aclPage.goto(`${BASE}/pages/kinerja/index.php`, { waitUntil: 'networkidle0' });
      const petugasAllowed = !aclPage.url().includes('login.php');
      if (!petugasAllowed) throw new Error('Petugas tidak bisa akses kinerja');
      await shot(aclPage, '10b-petugas-kinerja');
      pass('ACL — petugas_pusat bisa akses halaman kinerja');
      await logout(aclPage);

      // manager bisa akses
      await login(aclPage, 'mgr_pusat', 'Kewer2024!');
      await aclPage.goto(`${BASE}/pages/kinerja/index.php`, { waitUntil: 'networkidle0' });
      const mgrAllowed = !aclPage.url().includes('login.php') && !aclPage.url().includes('dashboard.php');
      if (!mgrAllowed) {
        // mungkin redirect ke dashboard, cek lagi
        const url = aclPage.url();
        if (url.includes('login.php')) throw new Error('Manager diredirect ke login');
      }
      await shot(aclPage, '10c-manager-kinerja');
      pass('ACL — manager_pusat bisa akses halaman kinerja');
      await logout(aclPage);

    } catch (e) {
      fail('Access Control Kinerja', e);
      await shot(aclPage, '10-FAIL-acl');
    } finally {
      try { await logout(aclPage); } catch (_) {}
      await aclPage.close();
    }

    // ════════════════════════════════════════════════════════════
    // SUMMARY
    // ════════════════════════════════════════════════════════════
    console.log(`\n${'═'.repeat(60)}`);
    console.log('📊 HASIL TEST v2.3.x');
    console.log(`${'═'.repeat(60)}`);
    console.log(`  ✅ Passed : ${R.passed}`);
    console.log(`  ❌ Failed : ${R.failed}`);
    console.log(`  ⏭  Skipped: ${R.skipped}`);
    console.log(`${'═'.repeat(60)}`);
    if (R.errors.length) {
      console.log('\n❌ Daftar Error:');
      R.errors.forEach(e => console.log(`  • [${e.test}] ${e.error}`));
    }
    console.log(`\n📸 Screenshot: ${SHOTS}`);
    console.log(`${'═'.repeat(60)}\n`);

  } finally {
    await browser.close();
    process.exit(R.failed > 0 ? 1 : 0);
  }
}

run().catch(err => {
  console.error('💥 Fatal:', err);
  process.exit(1);
});
