const puppeteer = require('puppeteer');
const fs = require('fs');

// Configuration
const BASE_URL = 'http://localhost/kewer';

// Roles with credentials
const ROLES = [
    { code: 'bos', username: 'patri', password: 'Kewer2024!', cabang_id: 1 },
    { code: 'manager_pusat', username: 'mgr_pusat', password: 'Kewer2024!', cabang_id: 1 },
    { code: 'manager_cabang', username: 'mgr_balige', password: 'Kewer2024!', cabang_id: 2 },
    { code: 'admin_pusat', username: 'adm_pusat', password: 'Kewer2024!', cabang_id: 1 },
    { code: 'admin_cabang', username: 'adm_balige', password: 'Kewer2024!', cabang_id: 2 },
    { code: 'petugas_pusat', username: 'ptr_pusat', password: 'Kewer2024!', cabang_id: 1 },
    { code: 'petugas_cabang', username: 'ptr_balige', password: 'Kewer2024!', cabang_id: 2 },
    { code: 'karyawan', username: 'krw_pusat', password: 'Kewer2024!', cabang_id: 1 }
];

// Simulation data
const SIMULATION_DATA = {
    nasabah: {
        nama: 'Simulasi Nasabah ' + new Date().toISOString().slice(0,10),
        telp: '081234567890',
        alamat: 'Jl Simulasi No 123',
        ktp: '1234567890123456'
    },
    pinjaman: {
        harian: { frekuensi_id: 1, plafon: 1000000, tenor: 30 },
        mingguan: { frekuensi_id: 2, plafon: 5000000, tenor: 12 },
        bulanan: { frekuensi_id: 3, plafon: 10000000, tenor: 6 }
    }
};

// Results storage
const results = {
    summary: [],
    errors: [],
    consoleErrors: [],
    operations: []
};

async function login(page, username, password) {
    try {
        await page.goto(`${BASE_URL}/login.php`, { waitUntil: 'networkidle2', timeout: 30000 });
        
        const currentUrl = page.url();
        if (currentUrl.includes('dashboard.php')) {
            console.log(`  ✓ Already logged in as ${username}`);
            return true;
        }

        await page.waitForSelector('input[name="username"]', { timeout: 10000 });
        await page.type('input[name="username"]', username);
        await page.type('input[name="password"]', password);
        await page.click('button[type="submit"]');
        
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
        
        const loggedIn = page.url().includes('dashboard.php');
        if (loggedIn) {
            console.log(`  ✓ Login successful for ${username}`);
            return true;
        } else {
            console.log(`  ✗ Login failed for ${username}`);
            return false;
        }
    } catch (error) {
        console.log(`  ✗ Login error for ${username}: ${error.message}`);
        return false;
    }
}

async function createNasabah(page, data) {
    try {
        console.log('  Creating nasabah...');
        await page.goto(`${BASE_URL}/pages/nasabah/tambah.php`, { waitUntil: 'networkidle2' });
        
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        await page.type('input[name="nama"]', data.nama);
        await page.type('input[name="telp"]', data.telp);
        await page.type('input[name="ktp"]', data.ktp);
        
        // Select province (first option)
        await page.select('select[name="province_id"]', '11'); // Sumatera Utara
        await new Promise(resolve => setTimeout(resolve, 500));
        
        // Select regency (first option after province)
        const regencySelect = await page.$('select[name="regency_id"]');
        if (regencySelect) {
            await page.select('select[name="regency_id"]', '1101'); // Kabupaten Deli Serdang
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        // Select district (first option after regency)
        const districtSelect = await page.$('select[name="district_id"]');
        if (districtSelect) {
            await page.select('select[name="district_id"]', '110101'); // Kecamatan
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        // Select village (first option after district)
        const villageSelect = await page.$('select[name="village_id"]');
        if (villageSelect) {
            await page.select('select[name="village_id"]', '1101012001'); // Desa
        }
        
        await new Promise(resolve => setTimeout(resolve, 500));
        
        await page.click('button[type="submit"]');
        
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Check if we're still on the same page (error) or redirected (success)
        const currentUrl = page.url();
        const pageContent = await page.content();
        const success = currentUrl.includes('index.php') || pageContent.includes('Berhasil') || pageContent.includes('berhasil ditambahkan');
        
        if (success) {
            console.log('  ✓ Nasabah created successfully');
            results.operations.push({ type: 'CREATE_NASABAH', status: 'SUCCESS', data: data.nama });
            return true;
        } else {
            console.log('  ✗ Failed to create nasabah');
            results.operations.push({ type: 'CREATE_NASABAH', status: 'FAILED', data: data.nama });
            return false;
        }
    } catch (error) {
        console.log(`  ✗ Error creating nasabah: ${error.message}`);
        results.operations.push({ type: 'CREATE_NASABAH', status: 'ERROR', error: error.message });
        return false;
    }
}

async function createPinjaman(page, nasabahId, pinjamanData, type) {
    try {
        console.log(`  Creating ${type} pinjaman...`);
        await page.goto(`${BASE_URL}/pages/pinjaman/tambah.php`, { waitUntil: 'networkidle2' });
        
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        await page.select('select[name="nasabah_id"]', nasabahId.toString());
        await page.type('input[name="plafon"]', pinjamanData.plafon.toString());
        await page.select('select[name="frekuensi_id"]', pinjamanData.frekuensi_id.toString());
        await page.type('input[name="tenor"]', pinjamanData.tenor.toString());
        
        await page.click('button[type="submit"]');
        
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const currentUrl = page.url();
        const pageContent = await page.content();
        const success = currentUrl.includes('index.php') || pageContent.includes('Berhasil') || pageContent.includes('berhasil');
        
        if (success) {
            console.log(`  ✓ ${type} pinjaman created successfully`);
            results.operations.push({ 
                type: 'CREATE_PINJAMAN', 
                status: 'SUCCESS', 
                data: { type, plafon: pinjamanData.plafon, tenor: pinjamanData.tenor }
            });
            return true;
        } else {
            console.log(`  ✗ Failed to create ${type} pinjaman`);
            results.operations.push({ 
                type: 'CREATE_PINJAMAN', 
                status: 'FAILED', 
                data: { type, plafon: pinjamanData.plafon, tenor: pinjamanData.tenor }
            });
            return false;
        }
    } catch (error) {
        console.log(`  ✗ Error creating ${type} pinjaman: ${error.message}`);
        results.operations.push({ 
            type: 'CREATE_PINJAMAN', 
            status: 'ERROR', 
            data: { type },
            error: error.message 
        });
        return false;
    }
}

async function bayarAngsuran(page, angsuranId) {
    try {
        console.log('  Processing angsuran payment...');
        await page.goto(`${BASE_URL}/pages/angsuran/bayar_compact.php?id=${angsuranId}`, { waitUntil: 'networkidle2' });
        
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        await page.click('button[type="submit"]');
        
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const pageContent = await page.content();
        const success = pageContent.includes('Berhasil') || pageContent.includes('Pembayaran Berhasil');
        
        if (success) {
            console.log('  ✓ Angsuran payment successful');
            results.operations.push({ type: 'BAYAR_ANGSURAN', status: 'SUCCESS', data: { angsuranId } });
            return true;
        } else {
            console.log('  ✗ Failed to process angsuran payment');
            results.operations.push({ type: 'BAYAR_ANGSURAN', status: 'FAILED', data: { angsuranId } });
            return false;
        }
    } catch (error) {
        console.log(`  ✗ Error processing angsuran payment: ${error.message}`);
        results.operations.push({ type: 'BAYAR_ANGSURAN', status: 'ERROR', error: error.message });
        return false;
    }
}

async function getLatestNasabahId(page) {
    try {
        await page.goto(`${BASE_URL}/pages/nasabah/index.php`, { waitUntil: 'networkidle2' });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Try to get the first row's ID from the table
        const nasabahId = await page.evaluate(() => {
            const rows = document.querySelectorAll('#nasabahTable tbody tr');
            if (rows.length > 0) {
                const firstRow = rows[0];
                const cells = firstRow.querySelectorAll('td');
                if (cells.length > 0) {
                    // Try to find ID in the row
                    const link = firstRow.querySelector('a[href*="edit.php"]');
                    if (link) {
                        const href = link.getAttribute('href');
                        const match = href.match(/id=(\d+)/);
                        return match ? match[1] : null;
                    }
                }
            }
            return null;
        });
        
        return nasabahId;
    } catch (error) {
        console.log(`  ✗ Error getting nasabah ID: ${error.message}`);
        return null;
    }
}

async function getLatestAngsuranId(page) {
    try {
        await page.goto(`${BASE_URL}/pages/angsuran/index.php`, { waitUntil: 'networkidle2' });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const angsuranId = await page.evaluate(() => {
            const rows = document.querySelectorAll('#angsuranTable tbody tr');
            if (rows.length > 0) {
                const firstRow = rows[0];
                const link = firstRow.querySelector('a[href*="bayar"]');
                if (link) {
                    const href = link.getAttribute('href');
                    const match = href.match(/id=(\d+)/);
                    return match ? match[1] : null;
                }
            }
            return null;
        });
        
        return angsuranId;
    } catch (error) {
        console.log(`  ✗ Error getting angsuran ID: ${error.message}`);
        return null;
    }
}

async function testRole(role) {
    console.log(`\n=== Testing Role: ${role.code} (${role.username}) ===`);
    
    const browser = await puppeteer.launch({
        headless: false, // Headed mode as requested
        args: ['--start-maximized', '--no-sandbox', '--disable-setuid-sandbox'],
        defaultViewport: null
    });
    
    const page = await browser.newPage();
    
    // Setup console error listener
    page.on('console', msg => {
        if (msg.type() === 'error') {
            results.consoleErrors.push({
                role: role.code,
                text: msg.text(),
                location: msg.location()
            });
        }
    });
    
    try {
        // Login
        const loginSuccess = await login(page, role.username, role.password);
        if (!loginSuccess) {
            results.summary.push({ role: role.code, status: 'FAILED', reason: 'Login failed' });
            await browser.close();
            return;
        }
        
        // Only bos and admin roles can create nasabah and pinjaman
        if (role.code === 'bos' || role.code.startsWith('admin')) {
            // Create nasabah
            const nasabahSuccess = await createNasabah(page, SIMULATION_DATA.nasabah);
            
            if (nasabahSuccess) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Get nasabah ID
                const nasabahId = await getLatestNasabahId(page);
                
                if (nasabahId) {
                    console.log(`  ✓ Got nasabah ID: ${nasabahId}`);
                    
                    // Create harian pinjaman
                    await createPinjaman(page, nasabahId, SIMULATION_DATA.pinjaman.harian, 'harian');
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    // Create mingguan pinjaman
                    await createPinjaman(page, nasabahId, SIMULATION_DATA.pinjaman.mingguan, 'mingguan');
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    // Create bulanan pinjaman
                    await createPinjaman(page, nasabahId, SIMULATION_DATA.pinjaman.bulanan, 'bulanan');
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    // Get angsuran ID and test payment
                    const angsuranId = await getLatestAngsuranId(page);
                    if (angsuranId) {
                        console.log(`  ✓ Got angsuran ID: ${angsuranId}`);
                        await bayarAngsuran(page, angsuranId);
                    }
                }
            }
        }
        
        // All roles can view dashboard and basic pages
        await page.goto(`${BASE_URL}/dashboard.php`, { waitUntil: 'networkidle2' });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        await page.goto(`${BASE_URL}/pages/nasabah/index.php`, { waitUntil: 'networkidle2' });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        await page.goto(`${BASE_URL}/pages/pinjaman/index.php`, { waitUntil: 'networkidle2' });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        await page.goto(`${BASE_URL}/pages/angsuran/index.php`, { waitUntil: 'networkidle2' });
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        results.summary.push({ role: role.code, status: 'PASSED', operations: results.operations.filter(o => true).length });
        console.log(`\n✓ ${role.code} role simulation completed successfully`);
        
    } catch (error) {
        console.log(`✗ Error testing ${role.code}: ${error.message}`);
        results.summary.push({ role: role.code, status: 'FAILED', reason: error.message });
        results.errors.push({ role: role.code, error: error.message });
    } finally {
        await browser.close();
    }
}

async function runSimulation() {
    console.log('=== Starting Comprehensive Application Simulation ===');
    console.log('Mode: Headed (visible browser)');
    console.log('Base URL:', BASE_URL);
    console.log('Roles to test:', ROLES.map(r => r.code).join(', '));
    console.log('\nSimulation includes:');
    console.log('- Nasabah creation');
    console.log('- Pinjaman creation (harian, mingguan, bulanan)');
    console.log('- Angsuran payment');
    console.log('- Dashboard navigation');
    console.log('- All basic page navigation');
    
    // Create screenshots directory
    if (!fs.existsSync('tests/screenshots')) {
        fs.mkdirSync('tests/screenshots', { recursive: true });
    }
    
    for (const role of ROLES) {
        await testRole(role);
    }
    
    // Print summary
    console.log('\n=== SIMULATION SUMMARY ===');
    results.summary.forEach(result => {
        console.log(`${result.role}: ${result.status}`);
        if (result.operations) {
            console.log(`  Operations: ${result.operations}`);
        }
    });
    
    // Print operations summary
    console.log('\n=== OPERATIONS SUMMARY ===');
    const opSummary = {};
    results.operations.forEach(op => {
        if (!opSummary[op.type]) {
            opSummary[op.type] = { SUCCESS: 0, FAILED: 0, ERROR: 0 };
        }
        opSummary[op.type][op.status]++;
    });
    
    Object.keys(opSummary).forEach(opType => {
        console.log(`${opType}:`);
        console.log(`  SUCCESS: ${opSummary[opType].SUCCESS}`);
        console.log(`  FAILED: ${opSummary[opType].FAILED}`);
        console.log(`  ERROR: ${opSummary[opType].ERROR}`);
    });
    
    // Print console errors
    if (results.consoleErrors.length > 0) {
        console.log('\n=== CONSOLE ERRORS ===');
        results.consoleErrors.forEach(err => {
            console.log(`${err.role}: ${err.text}`);
        });
    }
    
    // Save results to file
    fs.writeFileSync('tests/comprehensive_simulation_results.json', JSON.stringify(results, null, 2));
    console.log('\nResults saved to tests/comprehensive_simulation_results.json');
}

// Run simulation
runSimulation().catch(console.error);
