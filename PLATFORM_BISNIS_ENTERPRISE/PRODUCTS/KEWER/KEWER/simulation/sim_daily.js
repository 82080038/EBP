'use strict';
/**
 * FASE HARIAN — Simulasi aktivitas harian semua role
 * Setiap hari mensimulasikan aktivitas sesuai role dan permission:
 *
 * - appOwner    : monitoring platform (tidak akses data koperasi)
 * - bos         : dashboard, approve pinjaman, laporan
 * - manager_pusat: dashboard, approve pinjaman, laporan, manage user
 * - manager_cabang: dashboard, approve pinjaman, kelola angsuran
 * - admin_pusat  : input nasabah, pinjaman, angsuran, laporan
 * - petugas_pusat: koleksi pembayaran, field activities
 * - petugas_cabang: koleksi pembayaran, kas petugas
 * - karyawan    : view nasabah, pinjaman, rekonsiliasi kas
 */

const {
  BASE, STATE, NAMA_BATAK, LOKASI_PASAR, JENIS_USAHA_IDS,
  log, LA, LE, LS, sleep, randItem, fmtDate, nextSimDay, isWeekend,
  ss, newBrowser, loginUser, checkAlert,
} = require('./sim_helpers');

// ─── DATA NASABAH REALISTIS ──────────────────────────────────────
const NASABAH_DATA = [
  { nama: 'Tiurma Br Tobing',      telp: '081311110001', nik: '1213010101800001', jenis_usaha: 7,  lokasi: 'Pasar Pangururan',     pinjaman: 2000000,  tenor: 60,  frekuensi: 'harian' },
  { nama: 'Horas Lumbantobing',     telp: '081311110002', nik: '1213010101800002', jenis_usaha: 8,  lokasi: 'Pasar Onan Balige',    pinjaman: 5000000,  tenor: 20,  frekuensi: 'mingguan' },
  { nama: 'Debora Br Sinaga',       telp: '081311110003', nik: '1213010101800003', jenis_usaha: 9,  lokasi: 'Pasar Simanindo',      pinjaman: 3000000,  tenor: 6,   frekuensi: 'bulanan' },
  { nama: 'Samuel Hutapea',         telp: '081311110004', nik: '1213010101800004', jenis_usaha: 10, lokasi: 'Pajak Pagi Pangururan',pinjaman: 1500000,  tenor: 30,  frekuensi: 'harian' },
  { nama: 'Risma Br Panjaitan',     telp: '081311110005', nik: '1213010101800005', jenis_usaha: 11, lokasi: 'Pasar Laguboti',       pinjaman: 4000000,  tenor: 12,  frekuensi: 'bulanan' },
  { nama: 'Guntur Simbolon',        telp: '081311110006', nik: '1213010101800006', jenis_usaha: 12, lokasi: 'Pasar Balige',         pinjaman: 2500000,  tenor: 50,  frekuensi: 'harian' },
  { nama: 'Marlina Br Sitompul',    telp: '081311110007', nik: '1213010101800007', jenis_usaha: 13, lokasi: 'Pasar Harian Samosir', pinjaman: 1000000,  tenor: 4,   frekuensi: 'mingguan' },
  { nama: 'Abdi Gultom',            telp: '081311110008', nik: '1213010101800008', jenis_usaha: 14, lokasi: 'Pasar Nainggolan',     pinjaman: 7500000,  tenor: 6,   frekuensi: 'bulanan' },
  { nama: 'Netty Br Saragih',       telp: '081311110009', nik: '1213010101800009', jenis_usaha: 15, lokasi: 'Pasar Onan Runggu',   pinjaman: 3000000,  tenor: 60,  frekuensi: 'harian' },
  { nama: 'Patar Limbong',          telp: '081311110010', nik: '1213010101800010', jenis_usaha: 16, lokasi: 'Pasar Siborongborong', pinjaman: 5000000,  tenor: 10,  frekuensi: 'mingguan' },
];

// ─── AKTIVITAS PER ROLE ──────────────────────────────────────────

async function activityAppOwner(browser) {
  LA('appowner', 'AppOwner: monitoring platform dashboard');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'appowner', 'AppOwner2024!');
    if (!ok) return;
    await ss(page, 'appowner_dashboard');
    // AppOwner hanya akses platform features, tidak bisa akses data koperasi
    await sleep(1500);
    LS('appowner', 'Platform monitoring selesai');
  } catch(e) {
    LE('appowner', e.message);
  } finally {
    await page.close();
  }
}

async function activityBos(browser) {
  LA('patri', 'Bos: cek dashboard & approve pinjaman pending');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'patri');
    if (!ok) return;

    await ss(page, 'bos_dashboard');

    // Cek pinjaman pending untuk diapprove
    await page.goto(`${BASE}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(800);
    await ss(page, 'bos_pinjaman_list');

    // Cek laporan
    const laporanEl = await page.$('a[href*="laporan"]');
    if (laporanEl) {
      await laporanEl.click();
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
      await ss(page, 'bos_laporan');
    }

    LS('patri', 'Aktivitas bos harian selesai');
  } catch(e) {
    LE('patri', e.message);
  } finally {
    await page.close();
  }
}

async function activityManagerPusat(browser) {
  LA('mgr_pusat', 'Manager Pusat: review dashboard & approve pinjaman');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'mgr_pusat');
    if (!ok) return;

    await ss(page, 'mgr_pusat_dashboard');

    // Lihat daftar pinjaman
    await page.goto(`${BASE}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(800);

    // Approve pinjaman yang pending (jika ada)
    const pendingBtn = await page.$('a[href*="approve"], button[data-action="approve"]');
    if (pendingBtn) {
      await pendingBtn.click();
      await sleep(1000);
      // Handle SweetAlert jika muncul
      const confirmBtn = await page.$('.swal2-confirm');
      if (confirmBtn) { await confirmBtn.click(); await sleep(800); }
      LS('mgr_pusat', 'Pinjaman pending diapprove');
    }

    await ss(page, 'mgr_pusat_pinjaman');

    // Kelola user
    await page.goto(`${BASE}/pages/petugas/index.php`, { waitUntil: 'networkidle2', timeout: 10000 });
    await sleep(600);
    await ss(page, 'mgr_pusat_users');

    LS('mgr_pusat', 'Aktivitas manager pusat harian selesai');
  } catch(e) {
    LE('mgr_pusat', e.message);
  } finally {
    await page.close();
  }
}

async function activityManagerCabang(browser) {
  LA('mgr_balige', 'Manager Cabang: operasional harian & angsuran');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'mgr_balige');
    if (!ok) return;

    await ss(page, 'mgr_cabang_dashboard');

    // Cek angsuran hari ini
    await page.goto(`${BASE}/pages/angsuran/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(800);
    await ss(page, 'mgr_cabang_angsuran');

    // Cek pengeluaran
    await page.goto(`${BASE}/pages/pengeluaran/index.php`, { waitUntil: 'networkidle2', timeout: 10000 });
    await sleep(600);
    await ss(page, 'mgr_cabang_pengeluaran');

    LS('mgr_balige', 'Aktivitas manager cabang harian selesai');
  } catch(e) {
    LE('mgr_balige', e.message);
  } finally {
    await page.close();
  }
}

async function activityAdminPusat(browser, day) {
  LA('adm_pusat', 'Admin Pusat: input nasabah & pinjaman baru');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'adm_pusat');
    if (!ok) return;

    await ss(page, 'adm_pusat_dashboard');

    // Hari 1-2: Input nasabah baru
    if (day <= 2) {
      const nasabahBatch = NASABAH_DATA.slice((day - 1) * 3, day * 3);
      for (const nsb of nasabahBatch) {
        LA('adm_pusat', `Tambah nasabah: ${nsb.nama}`);
        await page.goto(`${BASE}/pages/nasabah/tambah.php`, { waitUntil: 'networkidle2', timeout: 12000 });
        await sleep(700);

        const formEl = await page.$('form').catch(() => null);
        if (!formEl) continue;

        await page.evaluate(data => {
          const set = (name, val) => {
            const el = document.querySelector(`input[name="${name}"],select[name="${name}"],textarea[name="${name}"]`);
            if (el) el.value = val;
          };
          set('nama', data.nama);
          set('nik', data.nik);
          set('telp', data.telp);
          set('lokasi_usaha', data.lokasi);
          set('jenis_usaha_id', data.jenis_usaha);
        }, nsb);

        await ss(page, `adm_pusat_tambah_nsb_${nsb.nik.slice(-4)}`);
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
        await sleep(600);

        const result = await checkAlert(page, 'adm_pusat');
        if (result.success) {
          STATE.nasabah.push({ nama: nsb.nama });
          LS('adm_pusat', `Nasabah ${nsb.nama} berhasil ditambah`);
        }
      }
    }

    // Hari 3+: Input pinjaman untuk nasabah yang ada
    if (day >= 3) {
      // Ambil nasabah dari API
      const nasabahList = await page.evaluate(async (base) => {
        try {
          const r = await fetch(`${base}/api/nasabah.php`);
          const data = await r.json();
          return (data.data || []).slice(0, 2);
        } catch { return []; }
      }, BASE);

      for (const nsb of nasabahList) {
        const template = randItem(NASABAH_DATA);
        LA('adm_pusat', `Input pinjaman untuk nasabah id=${nsb.id}`);

        const pinjamanPayload = {
          nasabah_id: nsb.id,
          jumlah_pinjaman: template.pinjaman,
          tenor: template.tenor,
          frekuensi_angsuran: template.frekuensi,
          tanggal_pinjaman: fmtDate(STATE.simDate),
          tujuan_pinjaman: 'Modal usaha',
        };

        const result = await page.evaluate(async (base, payload) => {
          try {
            const r = await fetch(`${base}/api/pinjaman.php`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: new URLSearchParams({ action: 'create', ...payload }).toString(),
            });
            return r.json();
          } catch(e) { return { success: false, message: e.message }; }
        }, BASE, pinjamanPayload);

        if (result.success) {
          if (result.pinjaman_id) {
            STATE.pinjaman.push({ id: result.pinjaman_id, nasabah_id: nsb.id, frekuensi: template.frekuensi, status: 'pending' });
          }
          LS('adm_pusat', `Pinjaman Rp${template.pinjaman.toLocaleString('id')} berhasil diajukan`);
        } else {
          LA('adm_pusat', `Info pinjaman: ${result.message || 'tidak ada pinjaman baru hari ini'}`);
        }
      }
    }

    // Cek laporan harian
    await page.goto(`${BASE}/pages/laporan/index.php`, { waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
    await sleep(500);
    await ss(page, 'adm_pusat_laporan');

    LS('adm_pusat', 'Aktivitas admin pusat harian selesai');
  } catch(e) {
    LE('adm_pusat', e.message);
  } finally {
    await page.close();
  }
}

async function activityPetugasPusat(browser) {
  LA('ptr_pusat', 'Petugas Pusat: koleksi pembayaran & kas petugas');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'ptr_pusat');
    if (!ok) return;

    await ss(page, 'ptr_pusat_dashboard');

    // Lihat daftar angsuran yang harus ditagih
    await page.goto(`${BASE}/pages/angsuran/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(800);
    await ss(page, 'ptr_pusat_angsuran_list');

    // Proses pembayaran via API untuk angsuran aktif
    if (STATE.pinjaman.length > 0) {
      const pinjamanAktif = STATE.pinjaman.find(p => p.status === 'aktif' || p.status === 'pending');
      if (pinjamanAktif) {
        const angsuranList = await page.evaluate(async (base, pinjamanId) => {
          try {
            const r = await fetch(`${base}/api/angsuran.php?pinjaman_id=${pinjamanId}&status=belum_bayar`);
            const data = await r.json();
            return (data.data || []).slice(0, 2);
          } catch { return []; }
        }, BASE, pinjamanAktif.id);

        for (const ang of angsuranList) {
          const result = await page.evaluate(async (base, angId, tanggal) => {
            try {
              const r = await fetch(`${base}/api/pembayaran.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                  action: 'create',
                  angsuran_id: angId,
                  tanggal_bayar: tanggal,
                  metode_bayar: 'tunai',
                }).toString(),
              });
              return r.json();
            } catch(e) { return { success: false, message: e.message }; }
          }, BASE, ang.id, fmtDate(STATE.simDate));

          if (result.success) {
            LS('ptr_pusat', `Pembayaran angsuran id=${ang.id} berhasil dicatat`);
          } else {
            LA('ptr_pusat', `Angsuran id=${ang.id}: ${result.message || 'sudah bayar'}`);
          }
        }
      }
    }

    // Setoran kas petugas
    await page.goto(`${BASE}/pages/kas_petugas/index.php`, { waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
    await sleep(600);
    await ss(page, 'ptr_pusat_kas_petugas');

    LS('ptr_pusat', 'Aktivitas petugas pusat harian selesai');
  } catch(e) {
    LE('ptr_pusat', e.message);
  } finally {
    await page.close();
  }
}

async function activityPetugasCabang(browser) {
  LA('ptr_balige', 'Petugas Cabang Balige: field activity & koleksi pembayaran');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'ptr_balige');
    if (!ok) return;

    await ss(page, 'ptr_balige_dashboard');

    // Lihat angsuran yang harus ditagih (cabang Balige)
    await page.goto(`${BASE}/pages/angsuran/index.php`, { waitUntil: 'networkidle2', timeout: 12000 });
    await sleep(800);
    await ss(page, 'ptr_balige_angsuran');

    // Kas petugas — setoran hari ini
    await page.goto(`${BASE}/pages/kas_petugas/index.php`, { waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
    await sleep(600);
    await ss(page, 'ptr_balige_kas');

    LS('ptr_balige', 'Aktivitas petugas cabang Balige harian selesai');
  } catch(e) {
    LE('ptr_balige', e.message);
  } finally {
    await page.close();
  }
}

async function activityKaryawan(browser) {
  LA('krw_pusat', 'Karyawan Pusat: rekonsiliasi kas & view data');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'krw_pusat');
    if (!ok) return;

    await ss(page, 'krw_pusat_dashboard');

    // View nasabah (read-only)
    await page.goto(`${BASE}/pages/nasabah/index.php`, { waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
    await sleep(600);
    await ss(page, 'krw_pusat_nasabah');

    // Rekonsiliasi kas
    await page.goto(`${BASE}/pages/cash_reconciliation/index.php`, { waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
    await sleep(600);
    await ss(page, 'krw_pusat_rekonsiliasi');

    // View pengeluaran
    await page.goto(`${BASE}/pages/pengeluaran/index.php`, { waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
    await sleep(600);
    await ss(page, 'krw_pusat_pengeluaran');

    LS('krw_pusat', 'Aktivitas karyawan pusat harian selesai');
  } catch(e) {
    LE('krw_pusat', e.message);
  } finally {
    await page.close();
  }
}

async function activityKaryawanBalige(browser) {
  LA('krw_balige', 'Karyawan Balige: rekonsiliasi kas cabang');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'krw_balige');
    if (!ok) return;

    await ss(page, 'krw_balige_dashboard');

    // Rekonsiliasi kas cabang Balige
    await page.goto(`${BASE}/pages/cash_reconciliation/index.php`, { waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
    await sleep(600);
    await ss(page, 'krw_balige_rekonsiliasi');

    LS('krw_balige', 'Aktivitas karyawan Balige harian selesai');
  } catch(e) {
    LE('krw_balige', e.message);
  } finally {
    await page.close();
  }
}

// ─── APPROVE PINJAMAN OLEH BOS ──────────────────────────────────
async function approvePinjamanPending(browser) {
  if (STATE.pinjaman.filter(p => p.status === 'pending').length === 0) return;

  LA('patri', 'Bos: approve semua pinjaman pending');
  const page = await browser.newPage();
  try {
    const ok = await loginUser(page, 'patri');
    if (!ok) return;

    for (const pin of STATE.pinjaman.filter(p => p.status === 'pending')) {
      const result = await page.evaluate(async (base, pid) => {
        try {
          const r = await fetch(`${base}/api/pinjaman.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'approve', id: pid }).toString(),
          });
          return r.json();
        } catch(e) { return { success: false, message: e.message }; }
      }, BASE, pin.id);

      if (result.success) {
        pin.status = 'aktif';
        LS('patri', `Pinjaman id=${pin.id} DISETUJUI ✓`);
      } else {
        LA('patri', `Pinjaman id=${pin.id}: ${result.message || 'tidak bisa approve'}`);
      }
    }
  } catch(e) {
    LE('patri', `Approve pinjaman: ${e.message}`);
  } finally {
    await page.close();
  }
}

// ─── SIMULASI SATU HARI ──────────────────────────────────────────
async function simulateOneDay(browsers) {
  nextSimDay();
  const dateStr = fmtDate(STATE.simDate);
  log('');
  log(`╔══════════════════════════════════╗`);
  log(`║  HARI ${String(STATE.simDay).padStart(2,'0')} — ${dateStr}            ║`);
  log(`╚══════════════════════════════════╝`);

  if (isWeekend(STATE.simDate)) {
    log('WEEKEND — Hanya monitoring minimal');
    await activityBos(browsers.bos);
    return;
  }

  // Urutan aktivitas sesuai flow bisnis:
  // 1. Admin input nasabah/pinjaman (pagi)
  await activityAdminPusat(browsers.admin, STATE.simDay);
  await sleep(1000);

  // 2. Bos/Manager approve pinjaman pending
  await approvePinjamanPending(browsers.bos);
  await sleep(1000);

  // 3. Petugas koleksi pembayaran (siang)
  await activityPetugasPusat(browsers.petugas);
  await sleep(800);

  await activityPetugasCabang(browsers.petugas);
  await sleep(800);

  // 4. Manager review & angsuran
  await activityManagerPusat(browsers.mgr);
  await sleep(800);

  await activityManagerCabang(browsers.mgr);
  await sleep(800);

  // 5. Bos review dashboard & laporan (sore)
  await activityBos(browsers.bos);
  await sleep(800);

  // 6. Karyawan rekonsiliasi kas (akhir hari)
  await activityKaryawan(browsers.petugas);
  await sleep(500);

  // 7. Karyawan Balige rekonsiliasi
  await activityKaryawanBalige(browsers.petugas);
  await sleep(500);

  // 8. AppOwner monitoring (platform level)
  await activityAppOwner(browsers.bos);
}

// ─── MAIN SIMULASI ──────────────────────────────────────────────
async function runSimulation() {
  log('');
  log('╔══════════════════════════════════════════════════════════╗');
  log('║  KEWER SIMULATION — FASE HARIAN (14 Hari)               ║');
  log('║  Flow: Admin → Approve → Koleksi → Manager → Bos → Kas  ║');
  log('╚══════════════════════════════════════════════════════════╝');
  log('');

  // Buka browser per kelompok role
  const browsers = {
    bos:    await newBrowser(0,    0,   960, 700),
    mgr:    await newBrowser(960,  0,   960, 700),
    admin:  await newBrowser(0,    700, 960, 700),
    petugas:await newBrowser(960,  700, 960, 700),
  };

  try {
    const TOTAL_DAYS = 14;
    for (let i = 0; i < TOTAL_DAYS; i++) {
      await simulateOneDay(browsers);
      if (i < TOTAL_DAYS - 1) {
        log('Tunggu 2 detik sebelum hari berikutnya...');
        await sleep(2000);
      }
    }

    log('');
    log('╔══════════════════════════════════════════════════════════╗');
    log('║  SIMULASI 14 HARI SELESAI                               ║');
    log(`║  Total nasabah: ${String(STATE.nasabah.length).padEnd(8)} Pinjaman: ${String(STATE.pinjaman.length).padEnd(8)}  ║`);
    log('╚══════════════════════════════════════════════════════════╝');

  } finally {
    await sleep(5000);
    for (const b of Object.values(browsers)) {
      await b.close().catch(() => {});
    }
  }
}

module.exports = { runSimulation };

if (require.main === module) {
  runSimulation().catch(e => { console.error(e); process.exit(1); });
}
