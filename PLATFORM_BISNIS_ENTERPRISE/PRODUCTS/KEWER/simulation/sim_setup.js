'use strict';
/**
 * FASE 0 — SETUP: Bos + Semua Staff
 * Flow aplikasi single-office Kewer:
 * 1. Bos login → dashboard (patri/Kewer2024!)
 * 2. Bos tambah semua staff via /pages/petugas/tambah.php
 * 3. Update tombol quick login di login.php
 */

const path = require('path');
const fs   = require('fs');

const {
  BASE, STATE, WILAYAH, NAMA_BATAK,
  log, LA, LE, LS, sleep, randItem, fmtDate,
  ss, newBrowser, loginUser, fillWilayah, checkAlert,
} = require('./sim_helpers');

// ─── DEFINISI SEMUA USER (sesuai DB aktual) ────────────────────
// Cabang 1 = Kantor Pusat Pangururan (cabang_id=1)
// Cabang 2 = Cabang Balige (cabang_id=2)
const ALL_USERS = [
  // bos — patri sudah ada di DB
  { username: 'patri',      password: 'Kewer2024!', nama: 'Patri Sihaloho',       role: 'bos',            email: 'patri@kewer.id',         telp: '081234560001', cabang_id: 1 },
  // manager pusat — cabang 1
  { username: 'mgr_pusat',  password: 'Kewer2024!', nama: 'Sondang Br Silaban',   role: 'manager_pusat',  email: 'mgr.pusat@kewer.id',     telp: '081234560002', cabang_id: 1 },
  // manager cabang — cabang 2 (Balige)
  { username: 'mgr_balige', password: 'Kewer2024!', nama: 'Roswita Nainggolan',   role: 'manager_cabang', email: 'mgr.balige@kewer.id',    telp: '081234560003', cabang_id: 2 },
  // admin pusat — cabang 1
  { username: 'adm_pusat',  password: 'Kewer2024!', nama: 'Melvina Hutabarat',    role: 'admin_pusat',    email: 'adm.pusat@kewer.id',     telp: '081234560004', cabang_id: 1 },
  // admin cabang — cabang 2 (Balige)
  { username: 'adm_balige', password: 'Kewer2024!', nama: 'Junita Br Sianturi',   role: 'admin_cabang',   email: 'adm.balige@kewer.id',    telp: '081234560008', cabang_id: 2 },
  // petugas pusat — cabang 1
  { username: 'ptr_pusat',  password: 'Kewer2024!', nama: 'Darwin Sinaga',        role: 'petugas_pusat',  email: 'ptr.pusat@kewer.id',     telp: '081234560005', cabang_id: 1 },
  // petugas cabang — cabang 2 (Balige)
  { username: 'ptr_balige', password: 'Kewer2024!', nama: 'Nico Purba',           role: 'petugas_cabang', email: 'ptr.balige@kewer.id',    telp: '081234560006', cabang_id: 2 },
  // karyawan pusat — cabang 1
  { username: 'krw_pusat',  password: 'Kewer2024!', nama: 'Susi Aritonang',       role: 'karyawan',       email: 'krw.pusat@kewer.id',     telp: '081234560007', cabang_id: 1 },
  // karyawan balige — cabang 2
  { username: 'krw_balige', password: 'Kewer2024!', nama: 'Tio Simatupang',       role: 'karyawan',       email: 'krw.balige@kewer.id',    telp: '081234560009', cabang_id: 2 },
];

// ─── STEP 1: BOS LOGIN & CEK DASHBOARD ─────────────────────────
async function verifyBosLogin(browser) {
  log('═══ STEP 1: Verifikasi login Bos ═══');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'patri');
    if (!ok) {
      LE('patri', 'Bos tidak bisa login — periksa database');
      return false;
    }
    await ss(page, 'bos_dashboard');
    LS('patri', 'Bos berhasil login → dashboard');
    return true;
  } catch(e) {
    LE('patri', `Verifikasi login: ${e.message}`);
    return false;
  } finally {
    await page.close();
  }
}

// ─── STEP 2: BOS TAMBAH SEMUA STAFF ─────────────────────────────
async function bosCreateAllStaff(browser) {
  log('═══ STEP 2: Bos tambah semua staff via /pages/petugas/tambah.php ═══');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'patri');
    if (!ok) return;

    // Ambil kantor_id dari DB
    const { execSync } = require('child_process');
    let kantorId = 1;
    try {
      const raw = execSync(
        `/opt/lampp/bin/mysql -u root -proot --socket=/opt/lampp/var/mysql/mysql.sock kewer -e "SELECT id FROM cabang LIMIT 1;" --batch --skip-column-names 2>/dev/null`
      ).toString().trim();
      if (raw) kantorId = parseInt(raw);
    } catch(e) {
      log(`Gunakan kantor_id default=1 (${e.message})`);
    }
    log(`Kantor ID: ${kantorId}`);

    // Tambah setiap user kecuali bos (patri sudah ada)
    const staffList = ALL_USERS.filter(u => u.role !== 'bos');

    for (const user of staffList) {
      LA('patri', `Tambah staff: ${user.nama} (${user.role})`);
      await page.goto(`${BASE}/pages/petugas/tambah.php`, { waitUntil: 'networkidle2', timeout: 12000 });
      await sleep(700);

      const formEl = await page.$('form[method="POST"]').catch(() => null);
      if (!formEl) {
        LE('patri', `Form tambah petugas tidak tersedia untuk ${user.nama}`);
        continue;
      }

      // Isi form
      await page.type('input[name="username"]', user.username, { delay: 50 });
      await page.type('input[name="password"]', user.password, { delay: 50 });
      const confirmEl = await page.$('input[name="confirm_password"]');
      if (confirmEl) await page.type('input[name="confirm_password"]', user.password, { delay: 50 });
      await page.type('input[name="nama"]', user.nama, { delay: 50 });
      const emailEl = await page.$('input[name="email"]');
      if (emailEl) await page.type('input[name="email"]', user.email, { delay: 40 });
      const telpEl = await page.$('input[name="telp"]');
      if (telpEl) await page.type('input[name="telp"]', user.telp, { delay: 40 });

      // Pilih role
      await page.select('select[name="role"]', user.role).catch(() => {});
      await sleep(400);

      // Kantor ID (single office)
      const userCabangId = user.cabang_id || kantorId;
      const kantorEl = await page.$('select[name="kantor_id"]');
      if (kantorEl) {
        await page.select('select[name="kantor_id"]', String(userCabangId)).catch(() => {});
      }
      // Fallback: cabang_id
      const cabangEl = await page.$('select[name="cabang_id"]');
      if (cabangEl) {
        await page.select('select[name="cabang_id"]', String(userCabangId)).catch(() => {});
      }

      // Tanggal masuk
      await page.evaluate(() => {
        const el = document.querySelector('input[name="tanggal_masuk"]');
        if (el) el.value = '2026-05-01';
      });

      await ss(page, `bos_tambah_${user.username}`);
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
      await sleep(700);

      const result = await checkAlert(page, 'patri');
      if (result.success) {
        STATE.users.push({ ...user, kantor_id: kantorId });
        LS('patri', `Staff ${user.nama} (${user.role}) berhasil ditambahkan`);
      } else if (result.success === false && (result.msg.includes('sudah') || result.msg.includes('duplicate'))) {
        STATE.users.push({ ...user, kantor_id: kantorId });
        LA('patri', `Staff ${user.username} sudah ada, lanjut`);
      } else {
        LE('patri', `Gagal tambah ${user.username}: ${result.msg}`);
      }
      await ss(page, `bos_staff_${user.username}_result`);
    }

    LS('patri', `Total staff berhasil: ${STATE.users.length}`);
  } catch(e) {
    LE('patri', `Tambah staff: ${e.message}`);
  } finally {
    await page.close();
  }
}

// ─── STEP 3: VERIFIKASI SEMUA USER BISA LOGIN ───────────────────
async function verifyAllLogins(browser) {
  log('═══ STEP 3: Verifikasi semua user bisa login ═══');
  const page = await browser.newPage();
  try {
    for (const user of ALL_USERS) {
      LA(user.username, `Test login ${user.role}`);
      await page.goto(`${BASE}/login.php?test_login=true&username=${user.username}&password=${user.password}`, {
        waitUntil: 'networkidle2', timeout: 12000
      });
      await sleep(500);
      const url = page.url();
      if (url.includes('dashboard') || url.includes('pages/bos')) {
        LS(user.username, `Login ✓ (${user.role})`);
      } else {
        LE(user.username, `Login GAGAL — URL: ${url}`);
      }
      await ss(page, `verify_login_${user.username}`);
    }
    // Test appowner
    LA('appowner', 'Test login appOwner');
    await page.goto(`${BASE}/login.php?test_login=true&username=appowner&password=AppOwner2024!`, {
      waitUntil: 'networkidle2', timeout: 12000
    });
    await sleep(500);
    const aoUrl = page.url();
    if (aoUrl.includes('dashboard') || aoUrl.includes('pages/')) {
      LS('appowner', 'Login ✓ (appOwner)');
    } else {
      LE('appowner', `Login GAGAL — URL: ${aoUrl}`);
    }
    await ss(page, 'verify_login_appowner');
  } catch(e) {
    LE('system', `Verifikasi login: ${e.message}`);
  } finally {
    await page.close();
  }
}

// ─── STEP 4: UPDATE QUICK LOGIN DI HALAMAN LOGIN ────────────────
function updateLoginQuickButtons() {
  log('═══ STEP 4: Update tombol quick login di login.php ═══');
  const loginFile = path.join(__dirname, '../login.php');
  if (!fs.existsSync(loginFile)) {
    LE('system', 'login.php tidak ditemukan');
    return;
  }
  let content = fs.readFileSync(loginFile, 'utf8');

  const roleColors = {
    bos:            'btn-dark',
    manager_pusat:  'btn-primary',
    manager_cabang: 'btn-info',
    admin_pusat:    'btn-secondary',
    admin_cabang:   'btn-success',
    petugas_pusat:  'btn-warning',
    petugas_cabang: 'btn-warning',
    karyawan:       'btn-light border',
  };

  const buttons = ALL_USERS.map(u => {
    const color = roleColors[u.role] || 'btn-secondary';
    const label = u.role.replace(/_/g, ' ').toUpperCase();
    return `                <button type="button" class="btn ${color} btn-sm" onclick="quickLogin('${u.username}','${u.password}')">`
         + `<strong>${label}</strong>: ${u.nama}</button>`;
  }).join('\n');

  const appOwnerBtn = `                <button type="button" class="btn btn-danger btn-sm" onclick="quickLogin('appowner','AppOwner2024!')">`
                    + `<strong>APP OWNER</strong>: Platform Owner</button>`;

  const newBlock = `        <div class="mt-3">
            <p class="text-center text-muted mb-2"><small>⚡ Quick Login Simulasi (Development Only):</small></p>
            <div class="d-grid gap-1">
${appOwnerBtn}
${buttons}
            </div>
        </div>`;

  const replaced = content.replace(
    /<div class="mt-3">\s*<p class="text-center text-muted mb-2">[\s\S]*?<\/div>\s*<\/div>/,
    newBlock
  );

  if (replaced !== content) {
    fs.writeFileSync(loginFile, replaced, 'utf8');
    LS('system', 'Tombol quick login berhasil diupdate');
  } else {
    log('Quick login block tidak ditemukan, skip update login.php');
  }
}

// ─── MAIN SETUP ─────────────────────────────────────────────────
async function runSetup() {
  log('');
  log('╔══════════════════════════════════════════════════════════╗');
  log('║  KEWER SIMULATION — FASE SETUP (Single Office)          ║');
  log('║  Bos login → Tambah staff → Verifikasi semua login       ║');
  log('╚══════════════════════════════════════════════════════════╝');
  log('');

  const browser = await newBrowser(0, 0, 1280, 800);

  try {
    // 1. Verifikasi bos login
    const bosOk = await verifyBosLogin(browser);
    if (!bosOk) {
      LE('system', 'Setup dihentikan — bos tidak bisa login');
      return;
    }
    await sleep(1000);

    // 2. Bos tambah semua staff
    await bosCreateAllStaff(browser);
    await sleep(1000);

    // 3. Verifikasi semua login
    await verifyAllLogins(browser);
    await sleep(1000);

    // 4. Update quick login buttons
    updateLoginQuickButtons();

    log('');
    log('╔══════════════════════════════════════════════════════════╗');
    log('║  SETUP SELESAI — Siap untuk simulasi                    ║');
    log('╚══════════════════════════════════════════════════════════╝');
    log(`Users: ${ALL_USERS.map(u => u.username).join(', ')}`);

  } finally {
    await sleep(5000);
    await browser.close();
  }
}

module.exports = { runSetup, ALL_USERS };

if (require.main === module) {
  runSetup().catch(e => { console.error(e); process.exit(1); });
}
