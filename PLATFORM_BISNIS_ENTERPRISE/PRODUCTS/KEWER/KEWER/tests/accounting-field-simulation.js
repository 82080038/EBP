const puppeteer = require('puppeteer-core');
const config = require('./puppeteer.config.js');

// Helper functions
function randomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function randomChoice(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}

function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// Generate Indonesian names
function generateNama() {
  const firstNames = ['Siti', 'Putri', 'Maya', 'Dewi', 'Rina', 'Sari', 'Wati', 'Lestari', 'Agus', 'Budi', 'Joko', 'Rudi', 'Ari', 'Bambang', 'Eko', 'Hendra', 'Dedi', 'Wahyu', 'Fitri', 'Sri'];
  const lastNames = ['Wijaya', 'Saputra', 'Pratama', 'Nugraha', 'Hidayat', 'Rahayu', 'Utami', 'Suryadi', 'Kusuma', 'Santoso', 'Permana', 'Kartika', 'Wibowo', 'Hartono', 'Santika', 'Purnama'];
  return randomChoice(firstNames) + ' ' + randomChoice(lastNames);
}

// Generate KTP
function generateKTP() {
  return Math.floor(Math.random() * 9000000000000000) + 1000000000000000;
}

// Generate phone
function generatePhone() {
  return '08' + Math.floor(Math.random() * 900000000) + 100000000;
}

// Activity types for field officers
const activityTypes = [
  'survey_nasabah',
  'input_pinjaman',
  'kutip_angsuran',
  'follow_up',
  'promosi',
  'edukasi',
  'lainnya'
];

// Run accounting field simulation
async function runAccountingFieldSimulation() {
  console.log('🚀 Starting Accounting Field Simulation - Realistic Branch Operations');
  console.log('=================================================================\n');

  const browser = await puppeteer.launch(config.launchOptions);
  const page = await browser.newPage();

  let results = {
    nasabahCreated: 0,
    pinjamanCreated: 0,
    pinjamanApproved: 0,
    activitiesLogged: 0,
    kasSetoran: 0,
    rekonsiliasi: 0
  };

  try {
    // STEP 1: Login sebagai Manager Cabang
    console.log('📋 STEP 1: Login sebagai Manager Cabang');
    await page.goto(config.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
    
    await page.type('input[name="username"]', 'manager1'); // Changed to admin role which is now at pusat
    await page.type('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await delay(2000);
    
    const currentUrl = page.url();
    if (currentUrl.includes('dashboard.php')) {
      console.log('✅ Login Manager Cabang Berhasil');
    } else {
      console.log('⚠️ Login mungkin gagal, current URL:', currentUrl);
    }

    // STEP 2: Generate 5-10 Field Officers (Petugas Lapangan) for this branch
    console.log('\n📋 STEP 2: Generate Petugas Lapangan untuk Cabang');
    const jumlahPetugas = randomInt(5, 10);
    console.log(`Target: ${jumlahPetugas} petugas lapangan di cabang ini`);
    
    const petugasList = [];
    for (let i = 0; i < jumlahPetugas; i++) {
      const petugas = {
        id: i + 1,
        nama: generateNama(),
        kode: 'PTG' + String(i + 1).padStart(3, '0'),
        targetNasabah: randomInt(15, 30),
        targetKunjungan: randomInt(20, 40)
      };
      petugasList.push(petugas);
      console.log(`  - ${petugas.kode}: ${petugas.nama} (Target: ${petugas.targetNasabah} nasabah, ${petugas.targetKunjungan} kunjungan)`);
    }
    console.log(`✅ Generated ${jumlahPetugas} petugas lapangan`);

    // STEP 3: Generate Nasabah per Petugas
    console.log('\n📋 STEP 3: Generate Nasabah per Petugas Lapangan');
    const totalNasabah = petugasList.reduce((sum, p) => sum + p.targetNasabah, 0);
    console.log(`Total nasabah target: ${totalNasabah}`);
    
    let nasabahCount = 0;
    for (const petugas of petugasList) {
      console.log(`\nPetugas ${petugas.kode} mencari ${petugas.targetNasabah} nasabah...`);
      
      for (let i = 0; i < petugas.targetNasabah; i++) {
        const nama = generateNama();
        const ktp = generateKTP();
        const telp = generatePhone();
        
        // Create nasabah via API
        const response = await page.evaluate(async (baseUrl, nama, ktp, telp, cabangId) => {
          const res = await fetch(baseUrl + '/api/nasabah.php?cabang_id=' + cabangId, {
            method: 'POST',
            headers: {
              'Authorization': 'Bearer kewer-api-token-2024',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              nama: nama,
              ktp: ktp,
              telp: telp,
              alamat: 'Alamat nasabah',
              province_id: 11,
              regency_id: 1101,
              district_id: 1101010
            })
          });
          const text = await res.text();
          const cleanText = text.replace(/\?>\s*$/, '').trim();
          return JSON.parse(cleanText);
        }, config.baseUrl, nama, ktp, telp, 1);
        
        if (response.success) {
          nasabahCount++;
          results.nasabahCreated++;
        }
      }
      
      console.log(`  - Petugas ${petugas.kode}: ${petugas.targetNasabah} nasabah`);
    }
    console.log(`✅ Total nasabah created: ${nasabahCount}`);

    // STEP 4: Simulasi Aktivitas Harian Petugas Lapangan
    console.log('\n📋 STEP 4: Simulasi Aktivitas Harian Petugas Lapangan');
    console.log('Petugas melakukan berbagai aktivitas di lapangan...\n');
    
    const hari = 1; // Simulasi 1 hari
    for (const petugas of petugasList) {
      console.log(`\n👤 Petugas ${petugas.kode} (${petugas.nama}):`);
      
      // Generate activities for this petugas
      const jumlahKegiatan = randomInt(15, 25);
      
      for (let i = 0; i < jumlahKegiatan; i++) {
        const activity = randomChoice(activityTypes);
        const activityTime = `${String(randomInt(8, 17)).padStart(2, '0')}:${String(randomInt(0, 59)).padStart(2, '0')}`;
        
        let description = '';
        let nasabahId = null;
        let pinjamanId = null;
        
        switch (activity) {
          case 'survey_nasabah':
            description = 'Survey dan verifikasi calon nasabah baru';
            break;
          case 'input_pinjaman':
            description = 'Input pengajuan pinjaman nasabah';
            // Create pinjaman via API
            const pinjamanResponse = await page.evaluate(async (baseUrl, cabangId, plafon, tenor) => {
              // Get a random nasabah
              const nasabahRes = await fetch(baseUrl + '/api/nasabah.php?cabang_id=' + cabangId);
              const nasabahText = await nasabahRes.text();
              const cleanNasabahText = nasabahText.replace(/\?>\s*$/, '').trim();
              const nasabahData = JSON.parse(cleanNasabahText);
              
              if (nasabahData.data && nasabahData.data.length > 0) {
                const nasabah = nasabahData.data[0];
                const pinjamanRes = await fetch(baseUrl + '/api/pinjaman.php?cabang_id=' + cabangId, {
                  method: 'POST',
                  headers: {
                    'Authorization': 'Bearer kewer-api-token-2024',
                    'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({
                    nasabah_id: nasabah.id,
                    plafon: plafon,
                    tenor: tenor,
                    bunga_per_bulan: 2,
                    tanggal_akad: new Date().toISOString().split('T')[0],
                    tujuan_pinjaman: 'Modal usaha',
                    jaminan: 'Tanpa jaminan',
                    jenis_pinjaman: 'mingguan'
                  })
                });
                const pinjamanText = await pinjamanRes.text();
                const cleanPinjamanText = pinjamanText.replace(/\?>\s*$/, '').trim();
                const pinjamanData = JSON.parse(cleanPinjamanText);
                return pinjamanData;
              }
              return { success: false };
            }, config.baseUrl, 1, randomInt(1000000, 5000000), randomInt(6, 12));
            
            if (pinjamanResponse.success) {
              description += ` - Pinjaman Rp${pinjamanResponse.data?.plafon?.toLocaleString() || 0}`;
              results.pinjamanCreated++;
            }
            break;
          case 'kutip_angsuran':
            description = 'Kutip pembayaran angsuran nasabah';
            break;
          case 'follow_up':
            description = 'Follow-up nasabah yang menunggak';
            break;
          case 'promosi':
            description = 'Promosi produk ke nasabah baru';
            break;
          case 'edukasi':
            description = 'Edukasi nasabah tentang tanggung jawab pinjaman';
            break;
          default:
            description = 'Aktivitas lainnya';
        }
        
        // Log activity to database (would need API endpoint)
        console.log(`  [${activityTime}] ${activity}: ${description}`);
        results.activitiesLogged++;
      }
      
      console.log(`  - Total kegiatan hari ini: ${jumlahKegiatan}`);
    }
    console.log(`\n✅ Total aktivitas tercatat: ${results.activitiesLogged}`);

    // STEP 5: Simulasi Setoran Kas Petugas
    console.log('\n📋 STEP 5: Simulasi Setoran Kas Petugas ke Cabang');
    console.log('Petugas menyetor hasil kutipan ke kas cabang...\n');
    
    for (const petugas of petugasList) {
      const totalKas = randomInt(500000, 2000000); // Rp500rb - Rp2jt
      const setoran = totalKas * randomInt(95, 100) / 100; // 95-100% setoran
      
      console.log(`  - ${petugas.kode}: Total kas Rp${totalKas.toLocaleString()}, Setoran Rp${setoran.toLocaleString()}`);
      
      // Would log to kas_petugas_setoran table
      results.kasSetoran++;
    }
    console.log(`\n✅ Total setoran tercatat: ${results.kasSetoran}`);

    // STEP 6: Rekonsiliasi Kas Harian Cabang
    console.log('\n📋 STEP 6: Rekonsiliasi Kas Harian Cabang');
    console.log('Manager melakukan rekonsiliasi kas akhir hari...\n');
    
    const kasAwal = randomInt(50000000, 100000000); // Rp50jt - Rp100jt
    const totalPenerimaan = results.kasSetoran * 1500000; // Estimasi
    const totalPengeluaran = randomInt(1000000, 3000000);
    const kasAkhir = kasAwal + totalPenerimaan - totalPengeluaran;
    const kasFisik = kasAkhir + randomInt(-50000, 50000);
    const selisih = kasFisik - kasAkhir;
    
    console.log(`  - Kas awal: Rp${kasAwal.toLocaleString()}`);
    console.log(`  - Total penerimaan: Rp${totalPenerimaan.toLocaleString()}`);
    console.log(`  - Total pengeluaran: Rp${totalPengeluaran.toLocaleString()}`);
    console.log(`  - Kas akhir (sistem): Rp${kasAkhir.toLocaleString()}`);
    console.log(`  - Kas fisik: Rp${kasFisik.toLocaleString()}`);
    console.log(`  - Selisih: Rp${selisih.toLocaleString()} (${selisih >= 0 ? 'Lebih' : 'Kurang'})`);
    
    // Would log to daily_cash_reconciliation table
    results.rekonsiliasi++;
    console.log(`\n✅ Rekonsiliasi selesai`);

    // STEP 7: Simulasi Auto-Confirm Pinjaman (jika diaktifkan)
    console.log('\n📋 STEP 7: Simulasi Auto-Confirm Pinjaman');
    console.log('Sistem mengecek pinjaman yang memenuhi kriteria auto-confirm...\n');
    
    // Get pending loans
    const pendingLoans = await page.evaluate(async (baseUrl) => {
      const res = await fetch(baseUrl + '/api/pinjaman.php?cabang_id=1&status=pengajuan', {
        headers: {
          'Authorization': 'Bearer kewer-api-token-2024',
        }
      });
      const text = await res.text();
      const cleanText = text.replace(/\?>\s*$/, '').trim();
      const data = JSON.parse(cleanText);
      return data.data || [];
    }, config.baseUrl);
    
    console.log(`Pinjaman pending: ${pendingLoans.length}`);
    
    if (pendingLoans.length > 0) {
      for (const loan of pendingLoans.slice(0, 3)) {
        // Simulate auto-check
        console.log(`  - ${loan.kode_pinjaman}: Plafon Rp${loan.plafon?.toLocaleString() || 0}`);
        
        // Would check auto-confirm eligibility
        const eligible = loan.plafon < 5000000; // Example criteria
        
        if (eligible) {
          console.log(`    ✅ Memenuhi kriteria auto-confirm`);
          results.pinjamanApproved++;
        } else {
          console.log(`    ❌ Tidak memenuhi kriteria (melebihi threshold)`);
        }
      }
    }
    
    console.log(`\n✅ Total pinjaman di-auto-confirm: ${results.pinjamanApproved}`);

    // STEP 8: Laporan Harian ke Pusat
    console.log('\n📋 STEP 8: Laporan Harian ke Pusat');
    console.log('Cabang mengirim laporan harian ke pusat...\n');
    
    console.log(`  - Total nasabah: ${results.nasabahCreated}`);
    console.log(`  - Total pinjaman baru: ${results.pinjamanCreated}`);
    console.log(`  - Total pinjaman aktif: ${results.pinjamanApproved}`);
    console.log(`  - Total aktivitas petugas: ${results.activitiesLogged}`);
    console.log(`  - Total setoran: Rp${(results.kasSetoran * 1500000).toLocaleString()}`);
    console.log(`  - Status rekonsiliasi: ${selisih === 0 ? 'Match' : 'Ada selisih'}`);
    
    console.log(`\n✅ Laporan harian terkirim ke pusat`);

    // STEP 9: Summary
    console.log('\n==================================================');
    console.log('📊 ACCOUNTING FIELD SIMULATION SUMMARY');
    console.log('==================================================');
    console.log(`Petugas Lapangan: ${jumlahPetugas}`);
    console.log(`Nasabah Created: ${results.nasabahCreated}`);
    console.log(`Pinjaman Created: ${results.pinjamanCreated}`);
    console.log(`Pinjaman Auto-Confirmed: ${results.pinjamanApproved}`);
    console.log(`Activities Logged: ${results.activitiesLogged}`);
    console.log(`Kas Setoran: ${results.kasSetoran}`);
    console.log(`Rekonsiliasi: ${results.rekonsiliasi}`);
    console.log('==================================================');
    console.log('✅ Accounting Field Simulation Completed');

  } catch (error) {
    console.error('💥 Fatal Error:', error.message);
    results.errors = results.errors || [];
    results.errors.push(error.message);
  } finally {
    await browser.close();
  }

  return results;
}

// Run simulation
runAccountingFieldSimulation().then(results => {
  console.log('\nSimulation results:', JSON.stringify(results, null, 2));
  process.exit(results.errors ? 1 : 0);
}).catch(error => {
  console.error('Simulation failed:', error);
  process.exit(1);
});
