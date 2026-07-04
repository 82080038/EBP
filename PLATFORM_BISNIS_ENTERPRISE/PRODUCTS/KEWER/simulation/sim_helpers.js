'use strict';
const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

// ─── CONFIG ───────────────────────────────────────────────────
const BASE = 'http://localhost/kewer';
const SS_DIR = path.join(__dirname, 'screenshots');
const LOG_DIR = path.join(__dirname, 'logs');
[SS_DIR, LOG_DIR].forEach(d => fs.mkdirSync(d, { recursive: true }));

const LOG_FILE = path.join(LOG_DIR, `sim_${new Date().toISOString().split('T')[0]}.log`);
const logStream = fs.createWriteStream(LOG_FILE, { flags: 'a' });

// Wilayah Sumatera Utara - Samosir & Toba Samosir
const WILAYAH = {
  province_id: 3,       // SUMATERA UTARA
  regency_samosir: 40,  // KABUPATEN SAMOSIR
  regency_toba: 29,     // KABUPATEN TOBA SAMOSIR
  // Kecamatan Samosir
  district_pangururan: 590,  // PANGURURAN
  district_simanindo: 591,   // SIMANINDO
  // Kecamatan Toba
  district_balige: 372,      // BALIGE
  // Desa
  village_pangururan: 10611, // RIANIATE (Pangururan)
  village_balige: 7802,      // AEK BOLON JULU (Balige)
};

// Nama-nama realistis Batak
const NAMA_BATAK = [
  'Hendrik Simanjuntak','Sondang Br Silaban','Torus Napitupulu',
  'Roswita Nainggolan','Melvina Hutabarat','Lestari Tambunan',
  'Ruli Sirait','Darwin Sinaga','Nico Purba','Markus Situmorang',
  'Susi Aritonang','Petrus Hutagalung','Benny Manullang',
  'Tiurma Br Tobing','Horas Lumbantobing','Debora Br Sinaga',
  'Samuel Hutapea','Risma Br Panjaitan','Guntur Simbolon',
  'Marlina Br Sitompul','Abdi Gultom','Netty Br Saragih',
  'Patar Limbong','Junisar Silalahi','Oloan Pardosi',
  'Marnala Br Manurung','Felix Siahaan','Yenny Br Situmorang',
];

// Pasar/Lokasi nyata di Samosir & Toba
const LOKASI_PASAR = [
  'Pasar Pangururan','Pasar Onan Balige','Pasar Simanindo',
  'Pasar Nainggolan','Pasar Onan Runggu','Pasar Harian Samosir',
  'Pasar Laguboti','Pasar Balige','Pasar Lumban Julu',
  'Pajak Pagi Pangururan','Pasar Siborongborong',
];

const JENIS_USAHA_IDS = [7,8,9,10,11,12,13,14,15,16]; // dari ref_jenis_usaha

// State global simulasi
const STATE = {
  bos: null,           // { id, username, cabang_hq_id }
  cabang: [],          // [{ id, nama }]
  users: [],           // semua user yg dibuat
  nasabah: [],         // [{ id, nama, cabang_id }]
  pinjaman: [],        // [{ id, nasabah_id, frekuensi, status }]
  simDay: 0,
  simDate: new Date('2026-05-01'),
};

// ─── HELPERS ──────────────────────────────────────────────────
function log(msg, level='INFO') {
  const ts = new Date().toISOString();
  const line = `[${ts}] [${level.padEnd(7)}] ${msg}`;
  console.log(line);
  logStream.write(line + '\n');
}
const LA = (role, msg) => log(`[${role}] ${msg}`, 'ACT');
const LE = (role, msg) => log(`[${role}] ERROR: ${msg}`, 'ERROR');
const LS = (role, msg) => log(`[${role}] ✓ ${msg}`, 'SUCCESS');

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

function randItem(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

function fmtDate(d) {
  return d.toISOString().split('T')[0];
}

function nextSimDay() {
  STATE.simDay++;
  STATE.simDate = new Date('2026-05-01');
  STATE.simDate.setDate(STATE.simDate.getDate() + STATE.simDay - 1);
}

function isWeekend(d) {
  return d.getDay() === 0 || d.getDay() === 6;
}

async function ss(page, name) {
  try {
    const f = path.join(SS_DIR, `d${STATE.simDay}_${name}_${Date.now()}.png`);
    await page.screenshot({ path: f, fullPage: false });
  } catch {}
}

async function newBrowser(x, y, w=900, h=700) {
  return puppeteer.launch({
    headless: false,
    slowMo: 40,
    args: [
      '--no-sandbox',
      `--window-position=${x},${y}`,
      `--window-size=${w},${h}`,
    ],
    defaultViewport: null,
  });
}

async function loginUser(page, username, password = null) {
  const pwd = password || 'Kewer2024!';
  await page.goto(`${BASE}/login.php?test_login=true&username=${username}&password=${pwd}`, {
    waitUntil: 'networkidle2', timeout: 15000
  });
  await sleep(600);
  if (!page.url().includes('dashboard')) {
    await page.goto(`${BASE}/dashboard.php`, { waitUntil: 'networkidle2', timeout: 10000 });
  }
  const url = page.url();
  const validPages = ['dashboard', 'setup_headquarters', 'pages/bos'];
  if (validPages.some(p => url.includes(p))) {
    LA(username, 'LOGIN ✓');
    return true;
  }
  LE(username, `Login gagal - URL: ${url}`);
  return false;
}

// Isi dropdown wilayah bertingkat dengan tunggu ajax
async function fillWilayah(page, prov, kab, kec, desa) {
  // Province
  await page.waitForSelector('select[name="province_id"]', { timeout: 5000 }).catch(() => {});
  const provEl = await page.$('select[name="province_id"]');
  if (!provEl) return;
  await page.select('select[name="province_id"]', String(prov));
  await sleep(1200); // tunggu AJAX load kab
  // Kabupaten
  await page.waitForSelector('select[name="regency_id"]', { timeout: 5000 }).catch(() => {});
  await page.select('select[name="regency_id"]', String(kab)).catch(() => {});
  await sleep(1200); // tunggu AJAX load kec
  // Kecamatan
  await page.waitForSelector('select[name="district_id"]', { timeout: 5000 }).catch(() => {});
  await page.select('select[name="district_id"]', String(kec)).catch(() => {});
  await sleep(1200); // tunggu AJAX load desa
  // Desa
  await page.waitForSelector('select[name="village_id"]', { timeout: 5000 }).catch(() => {});
  await page.select('select[name="village_id"]', String(desa)).catch(() => {});
  await sleep(500);
}

// Cek alert sukses/danger setelah submit
async function checkAlert(page, role) {
  const ok  = await page.$('.alert-success');
  const err = await page.$('.alert-danger');
  if (ok) {
    const msg = await page.$eval('.alert-success', e => e.textContent.trim());
    LS(role, msg);
    return { success: true, msg };
  }
  if (err) {
    const msg = await page.$eval('.alert-danger', e => e.textContent.trim());
    LE(role, msg);
    return { success: false, msg };
  }
  return { success: null, msg: '' };
}

module.exports = {
  BASE, STATE, WILAYAH, NAMA_BATAK, LOKASI_PASAR, JENIS_USAHA_IDS,
  log, LA, LE, LS, sleep, randItem, fmtDate, nextSimDay, isWeekend,
  ss, newBrowser, loginUser, fillWilayah, checkAlert,
};
