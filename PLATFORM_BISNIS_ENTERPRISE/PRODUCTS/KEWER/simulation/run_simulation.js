'use strict';
/**
 * KEWER SIMULATION — Entry Point
 * Jalankan: node simulation/run_simulation.js
 *
 * Urutan:
 *   1. FASE SETUP  — Bos daftar, SA approve, buat cabang & staff via form aplikasi
 *   2. Update quick login buttons di halaman login.php
 *   3. FASE HARIAN — 14 hari × 13 role, setiap role = window terpisah
 */

const { runSetup }      = require('./sim_setup');
const { runSimulation } = require('./sim_daily');
const { log }           = require('./sim_helpers');

async function main() {
  const mode = process.argv[2] || 'all';

  if (mode === 'setup' || mode === 'all') {
    await runSetup();
    log('Setup selesai. Tunggu 3 detik sebelum simulasi dimulai...');
    await new Promise(r => setTimeout(r, 3000));
  }

  if (mode === 'sim' || mode === 'all') {
    await runSimulation();
  }
}

main().catch(e => {
  console.error('FATAL:', e.message);
  process.exit(1);
});
