const puppeteer = require('puppeteer');
const config = require('./puppeteer.config.js');

// Helper function to delay
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// Helper function to generate random data
function randomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function randomChoice(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}

// Generate realistic Indonesian names
const namaDepan = ['Budi', 'Siti', 'Agus', 'Dewi', 'Eko', 'Rina', 'Joko', 'Maya', 'Hendra', 'Lestari', 'Rudi', 'Sari', 'Bambang', 'Putri', 'Wahyu', 'Fitri', 'Ari', 'Wati', 'Dedi', 'Sri'];
const namaBelakang = ['Santoso', 'Wijaya', 'Saputra', 'Hidayat', 'Kusuma', 'Pratama', 'Nugraha', 'Permana', 'Setiawan', 'Utami', 'Suryadi', 'Rahayu', 'Wibowo', 'Kartika', 'Hartono', 'Lestari', 'Purnama', 'Santika', 'Ramadhan', 'Yuliana'];

function generateNama() {
  return randomChoice(namaDepan) + ' ' + randomChoice(namaBelakang);
}

function generateKTP() {
  let ktp = '';
  for (let i = 0; i < 16; i++) {
    ktp += randomInt(0, 9);
  }
  return ktp;
}

function generatePhone() {
  let phone = '08';
  for (let i = 0; i < 10; i++) {
    phone += randomInt(0, 9);
  }
  return phone;
}

// Jenis pinjaman dan karakteristiknya
const jenisPinjaman = [
  { 
    jenis: 'harian', 
    tenor_min: 7, 
    tenor_max: 30, 
    plafon_min: 500000, 
    plafon_max: 2000000,
    deskripsi: 'Pinjaman harian untuk modal usaha kecil sehari-hari'
  },
  { 
    jenis: 'mingguan', 
    tenor_min: 4, 
    tenor_max: 12, 
    plafon_min: 1000000, 
    plafon_max: 5000000,
    deskripsi: 'Pinjaman mingguan untuk pedagang pasar'
  },
  { 
    jenis: 'bulanan', 
    tenor_min: 3, 
    tenor_max: 24, 
    plafon_min: 2000000, 
    plafon_max: 10000000,
    deskripsi: 'Pinjaman bulanan untuk modal usaha menengah'
  },
  { 
    jenis: 'multi_guna', 
    tenor_min: 6, 
    tenor_max: 36, 
    plafon_min: 5000000, 
    plafon_max: 20000000,
    deskripsi: 'Pinjaman multi-guna dengan jaminan'
  }
];

const jenisUsaha = [
  'Warung Kelontong', 'Pedagang Pasar', 'Toko Sembako', 'Jualan Gorengan',
  'Pedagang Sayur', 'Toko Kelontong', 'Warung Makan', 'Pedagang Kue',
  'Toko Bangunan', 'Jual Es', 'Pedagang Buah', 'Toko Pakaian',
  'Usaha Catering', 'Pedagang Ayam', 'Toko Elektronik', 'Jual Pulsa'
];

async function runFieldSimulation() {
  console.log('🚀 Starting Field Simulation - Realistic Scenario');
  console.log('==================================================');
  
  const browser = await puppeteer.launch(config.launchOptions);
  const page = await browser.newPage();
  
  let results = {
    nasabahCreated: 0,
    pinjamanCreated: 0,
    pembayaranDilakukan: 0,
    errors: []
  };

  try {
    // STEP 1: Login sebagai Petugas Lapangan
    console.log('\n📋 STEP 1: Login sebagai Petugas Lapangan');
    await page.goto(config.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
    
    // Fill login form manually
    await page.type('input[name="username"]', 'petugas1');
    await page.type('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await delay(2000);
    
    // Verify dashboard by checking URL
    const currentUrl = page.url();
    if (currentUrl.includes('dashboard.php')) {
      console.log('✅ Login Petugas Lapangan Berhasil');
    } else {
      console.log('⚠️ Login mungkin gagal, current URL:', currentUrl);
    }
    
    // STEP 2: Get current user's cabang_id
    console.log('\n📋 STEP 2: Get Current User Cabang ID');
    await page.goto(config.baseUrl + '/pages/users/index.php');
    await delay(1000);
    
    const userInfo = await page.evaluate(() => {
      const rows = document.querySelectorAll('table tbody tr');
      if (rows.length > 0) {
        const firstRow = rows[0];
        const cells = firstRow.querySelectorAll('td');
        // Find the current user row
        for (let i = 0; i < rows.length; i++) {
          const row = rows[i];
          const rowCells = row.querySelectorAll('td');
          if (rowCells.length > 0 && rowCells[1]?.textContent.includes('petugas1')) {
            return {
              username: rowCells[1]?.textContent.trim(),
              cabang: rowCells[4]?.textContent.trim()
            };
          }
        }
      }
      return { username: 'unknown', cabang: 'unknown' };
    });
    
    console.log(`User: ${userInfo.username}, Cabang: ${userInfo.cabang}`);
    
    // For now, use cabang_id=1 since we can't easily extract the numeric ID
    const userCabangId = 1;
    console.log(`Using cabang_id: ${userCabangId}`);
    
    // STEP 3: Generate 50-100 Nasabah dengan Berbagai Profil
    console.log('\n📋 STEP 3: Generate Nasabah dengan Berbagai Profil');
    const jumlahNasabah = randomInt(50, 100);
    console.log(`Target: ${jumlahNasabah} nasabah`);
    
    const nasabahList = [];
    for (let i = 0; i < jumlahNasabah; i++) {
      const nama = generateNama();
      const ktp = generateKTP();
      const telp = generatePhone();
      const usaha = randomChoice(jenisUsaha);
      
      // Determine risk profile based on random factors
      const riskScore = randomInt(1, 10);
      const riskProfile = riskScore <= 3 ? 'low' : riskScore <= 7 ? 'medium' : 'high';
      
      nasabahList.push({
        nama,
        ktp,
        telp,
        usaha,
        riskProfile,
        riskScore
      });
      
      if ((i + 1) % 10 === 0) {
        console.log(`Generated ${i + 1}/${jumlahNasabah} nasabah...`);
      }
    }
    
    console.log(`✅ Generated ${jumlahNasabah} nasabah profiles`);
    console.log(`   - Low risk: ${nasabahList.filter(n => n.riskProfile === 'low').length}`);
    console.log(`   - Medium risk: ${nasabahList.filter(n => n.riskProfile === 'medium').length}`);
    console.log(`   - High risk: ${nasabahList.filter(n => n.riskProfile === 'high').length}`);
    
    // STEP 3: Create nasabah via API (batch)
    console.log('\n📋 STEP 3: Create Nasabah via API');
    let createdCount = 0;
    
    for (let i = 0; i < nasabahList.length; i++) {
      const nasabah = nasabahList[i];
      
      try {
        const apiResponse = await page.evaluate(async (baseUrl, nama, ktp, telp, usaha) => {
          const response = await fetch(baseUrl + '/api/nasabah.php?cabang_id=1', {
            method: 'POST',
            headers: {
              'Authorization': 'Bearer kewer-api-token-2024',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              nama: nama,
              ktp: ktp,
              telp: telp,
              alamat: 'Alamat simulasi lapangan',
              jenis_usaha: usaha,
              status: 'aktif'
            })
          });
          const text = await response.text();
          const cleanText = text.replace(/\?>\s*$/, '').trim();
          return JSON.parse(cleanText);
        }, config.baseUrl, nasabah.nama, nasabah.ktp, nasabah.telp, nasabah.usaha);
        
        if (apiResponse.success && apiResponse.data) {
          nasabahList[i].id = apiResponse.data.id;
          createdCount++;
        }
        
        if ((i + 1) % 10 === 0) {
          console.log(`Created ${createdCount}/${i + 1} nasabah...`);
        }
        
        await delay(100); // Small delay between requests
      } catch (error) {
        console.log(`Failed to create nasabah ${i + 1}:`, error.message);
        results.errors.push(`Nasabah ${nasabah.nama}: ${error.message}`);
      }
    }
    
    results.nasabahCreated = createdCount;
    console.log(`✅ Created ${createdCount}/${jumlahNasabah} nasabah`);
    
    // STEP 4: Simulasi Petugas Lapangan Memberikan Pinjaman
    console.log('\n📋 STEP 4: Simulasi Petugas Lapangan Memberikan Pinjaman');
    console.log('Petugas lapangan menganalisis profil nasabah dan memberikan pinjaman sesuai jenis usaha');
    
    // Simulate 30-50 loans per day (realistic field scenario)
    const jumlahPinjaman = randomInt(30, 50);
    console.log(`Target: ${jumlahPinjaman} pinjaman hari ini`);
    
    const pinjamanList = [];
    let pinjamanCount = 0;
    
    for (let i = 0; i < jumlahPinjaman; i++) {
      // Select random nasabah
      const nasabahIndex = randomInt(0, nasabahList.length - 1);
      const nasabah = nasabahList[nasabahIndex];
      
      if (!nasabah.id) continue; // Skip if nasabah not created
      
      // Determine jenis pinjaman based on risk profile and business type
      let jenisPilihan;
      if (nasabah.riskProfile === 'high') {
        // High risk gets smaller, shorter loans
        jenisPilihan = jenisPinjaman.filter(j => j.jenis === 'harian' || j.jenis === 'mingguan');
      } else if (nasabah.riskProfile === 'medium') {
        jenisPilihan = jenisPinjaman.filter(j => j.jenis === 'mingguan' || j.jenis === 'bulanan');
      } else {
        // Low risk can get any type
        jenisPilihan = jenisPinjaman;
      }
      
      const jenis = randomChoice(jenisPilihan);
      const tenor = randomInt(jenis.tenor_min, jenis.tenor_max);
      const plafon = randomInt(jenis.plafon_min, jenis.plafon_max);
      
      try {
        const apiResponse = await page.evaluate(async (baseUrl, nasabahId, plafon, tenor, jenis) => {
          const response = await fetch(baseUrl + '/api/pinjaman.php?cabang_id=1', {
            method: 'POST',
            headers: {
              'Authorization': 'Bearer kewer-api-token-2024',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              nasabah_id: nasabahId,
              plafon: plafon,
              tenor: tenor,
              bunga_per_bulan: 2.5, // Will be overridden by system calculation
              tanggal_akad: new Date().toISOString().split('T')[0],
              tujuan_pinjaman: 'Modal usaha',
              jaminan: jenis === 'multi_guna' ? 'BPKB' : 'Tanpa jaminan',
              status: 'pengajuan'
            })
          });
          const text = await response.text();
          const cleanText = text.replace(/\?>\s*$/, '').trim();
          return JSON.parse(cleanText);
        }, config.baseUrl, nasabah.id, plafon, tenor, jenis.jenis);
        
        if (apiResponse.success && apiResponse.data) {
          pinjamanList.push({
            id: apiResponse.data.id,
            nasabahId: nasabah.id,
            namaNasabah: nasabah.nama,
            jenis: jenis.jenis,
            tenor,
            plafon,
            riskProfile: nasabah.riskProfile
          });
          pinjamanCount++;
          
          console.log(`Pinjaman #${pinjamanCount}: ${nasabah.nama} - ${jenis.jenis} - Rp${plafon.toLocaleString()} - ${tenor} bulan - Risk: ${nasabah.riskProfile}`);
        }
        
        await delay(200); // Delay between loan applications
      } catch (error) {
        console.log(`Failed to create pinjaman for ${nasabah.nama}:`, error.message);
        results.errors.push(`Pinjaman ${nasabah.nama}: ${error.message}`);
      }
    }
    
    results.pinjamanCreated = pinjamanCount;
    console.log(`✅ Created ${pinjamanCount}/${jumlahPinjaman} pinjaman`);
    
    // STEP 5: Approve Pinjaman dengan Analisa Risiko Otomatis
    console.log('\n📋 STEP 5: Approve Pinjaman dengan Analisa Risiko Otomatis');
    console.log('Sistem menganalisis risiko dan memberikan keputusan persen pinjaman');
    
    let approvedCount = 0;
    let rejectedCount = 0;
    
    for (let i = 0; i < pinjamanList.length; i++) {
      const pinjaman = pinjamanList[i];
      
      // Simulate risk-based approval
      const approvalRate = 
        pinjaman.riskProfile === 'low' ? 0.95 :
        pinjaman.riskProfile === 'medium' ? 0.80 : 0.60;
      
      const approved = Math.random() < approvalRate;
      
      if (approved) {
        try {
          await page.goto(config.baseUrl + `/pages/pinjaman/proses.php?action=approve&id=${pinjaman.id}`);
          await delay(500);
          approvedCount++;
          console.log(`✅ Approved: ${pinjaman.namaNasabah} - ${pinjaman.jenis} - Rp${pinjaman.plafon.toLocaleString()}`);
        } catch (error) {
          console.log(`Failed to approve pinjaman ${pinjaman.id}:`, error.message);
        }
      } else {
        rejectedCount++;
        console.log(`❌ Rejected: ${pinjaman.namaNasabah} - ${pinjaman.riskProfile} risk - High risk`);
        
        try {
          await page.goto(config.baseUrl + `/pages/pinjaman/proses.php?action=reject&id=${pinjaman.id}`);
          await delay(500);
        } catch (error) {
          console.log(`Failed to reject pinjaman ${pinjaman.id}:`, error.message);
        }
      }
      
      await delay(300);
    }
    
    console.log(`✅ Approved: ${approvedCount}, Rejected: ${rejectedCount}`);
    
    // STEP 6: Simulasi Pembayaran Angsuran
    console.log('\n📋 STEP 6: Simulasi Pembayaran Angsuran');
    console.log('Petugas lapangan mengutip pembayaran harian/mingguan/bulanan');
    
    // Get all loans via API to check status
    const apiResult = await page.evaluate(async (baseUrl) => {
      try {
        const response = await fetch(baseUrl + '/api/pinjaman.php?cabang_id=1', {
          method: 'GET',
          headers: {
            'Authorization': 'Bearer kewer-api-token-2024',
            'Content-Type': 'application/json',
          }
        });
        const text = await response.text();
        const cleanText = text.replace(/\?>\s*$/, '').trim();
        
        // Check if response is HTML error
        if (cleanText.startsWith('<')) {
          return { error: 'HTML response', raw: cleanText.substring(0, 200) };
        }
        
        const data = JSON.parse(cleanText);
        return { success: true, data: data.data || [] };
      } catch (error) {
        return { error: error.message };
      }
    }, config.baseUrl);
    
    if (apiResult.error) {
      console.log(`❌ API Error: ${apiResult.error}`);
      if (apiResult.raw) {
        console.log(`Raw response: ${apiResult.raw}`);
      }
      // Fallback to UI check
      await page.goto(config.baseUrl + '/pages/pinjaman/index.php');
      await delay(1000);
      
      const allLoans = await page.evaluate(() => {
        const rows = document.querySelectorAll('table tbody tr');
        const loans = [];
        rows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length > 0) {
            loans.push({
              id: cells[0]?.textContent.trim(),
              kode: cells[1]?.textContent.trim(),
              status: cells[cells.length - 2]?.textContent.trim()
            });
          }
        });
        return loans;
      });
      
      const activeLoans = allLoans.filter(loan => loan.status === 'aktif');
      const approvedLoans = allLoans.filter(loan => loan.status === 'disetujui');
      const pengajuanLoans = allLoans.filter(loan => loan.status === 'pengajuan');
      
      console.log(`Total loans (UI): ${allLoans.length}`);
      console.log(`Active loans: ${activeLoans.length}`);
      console.log(`Approved loans: ${approvedLoans.length}`);
      console.log(`Pengajuan loans: ${pengajuanLoans.length}`);
    } else {
      const allLoans = apiResult.data;
      const activeLoans = allLoans.filter(loan => loan.status === 'aktif');
      const approvedLoans = allLoans.filter(loan => loan.status === 'disetujui');
      const pengajuanLoans = allLoans.filter(loan => loan.status === 'pengajuan');
      
      console.log(`Total loans: ${allLoans.length}`);
      console.log(`Active loans: ${activeLoans.length}`);
      console.log(`Approved loans: ${approvedLoans.length}`);
      console.log(`Pengajuan loans: ${pengajuanLoans.length}`);
      
      if (approvedLoans.length > 0) {
        console.log('Sample approved loans:');
        approvedLoans.slice(0, 3).forEach(loan => {
          console.log(`  - ${loan.kode_pinjaman}: ${loan.status}`);
        });
      }
    }
    
    // Simulate payments for some loans
    const paymentCount = Math.min(activeLoans.length, randomInt(20, 30));
    let pembayaranDilakukan = 0;
    
    for (let i = 0; i < paymentCount; i++) {
      const loan = activeLoans[i];
      
      // Go to angsuran page
      await page.goto(config.baseUrl + '/pages/angsuran/index.php');
      await delay(500);
      
      // Search for loan
      await page.type('input[name="search"]', loan.kode);
      await delay(500);
      
      // Get first unpaid installment
      const angsuranId = await page.evaluate(() => {
        const rows = document.querySelectorAll('table tbody tr');
        if (rows.length > 0) {
          const row = rows[0];
          const cells = row.querySelectorAll('td');
          const bayarBtn = row.querySelector('a[href*="bayar.php"]');
          if (bayarBtn) {
            const href = bayarBtn.getAttribute('href');
            const match = href.match(/id=(\d+)/);
            return match ? match[1] : null;
          }
        }
        return null;
      });
      
      if (angsuranId) {
        try {
          await page.goto(config.baseUrl + `/pages/angsuran/bayar.php?id=${angsuranId}`);
          await page.waitForSelector('form', { timeout: 5000 });
          await page.click('button[type="submit"]');
          await delay(1000);
          pembayaranDilakukan++;
          console.log(`✅ Payment: ${loan.nama} - ${loan.kode}`);
        } catch (error) {
          console.log(`Failed to process payment for ${loan.kode}:`, error.message);
        }
      }
      
      await delay(500);
    }
    
    results.pembayaranDilakukan = pembayaranDilakukan;
    console.log(`✅ Processed ${pembayaranDilakukan} payments`);
    
    // STEP 7: Check Late Payments
    console.log('\n📋 STEP 7: Check Late Payments');
    await page.goto(config.baseUrl + '/pages/angsuran/index.php');
    await delay(1000);
    
    const latePayments = await page.evaluate(() => {
      const badges = document.querySelectorAll('.badge.bg-danger');
      return badges.length;
    });
    
    console.log(`Late payments detected: ${latePayments}`);
    
    // STEP 8: Dashboard Summary
    console.log('\n📋 STEP 8: Dashboard Summary');
    await page.goto(config.baseUrl + '/dashboard.php');
    await delay(1000);
    
    console.log('\n==================================================');
    console.log('📊 FIELD SIMULATION SUMMARY');
    console.log('==================================================');
    console.log(`Nasabah Created: ${results.nasabahCreated}`);
    console.log(`Pinjaman Created: ${results.pinjamanCreated}`);
    console.log(`Pinjaman Approved: ${approvedCount}`);
    console.log(`Pinjaman Rejected: ${rejectedCount}`);
    console.log(`Pembayaran Dilakukan: ${results.pembayaranDilakukan}`);
    console.log(`Late Payments: ${latePayments}`);
    console.log(`Errors: ${results.errors.length}`);
    
    if (results.errors.length > 0) {
      console.log('\n❌ Errors:');
      results.errors.forEach(err => console.log(`   - ${err}`));
    }
    
    await delay(3000);
    await browser.close();
    
    console.log('\n✅ Field Simulation Completed');
    
  } catch (error) {
    console.error('\n💥 Fatal Error:', error);
    await browser.close();
    process.exit(1);
  }
}

runFieldSimulation();
