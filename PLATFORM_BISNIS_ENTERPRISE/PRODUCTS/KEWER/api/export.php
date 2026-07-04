<?php
/**
 * API: Export Laporan
 * GET /api/export.php?format=pdf|csv&jenis=comprehensive|financial|loan_performance|customer
 *       &tanggal_mulai=YYYY-MM-DD&tanggal_selesai=YYYY-MM-DD&cabang_id=
 */
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';

if (!isFeatureEnabled('export_laporan')) {
    http_response_code(403);
    echo json_encode(['error' => 'Fitur Export Laporan belum diaktifkan oleh appOwner.']);
    exit();
}
require_once BASE_PATH . '/src/Reporting/ReportGenerator.php';

requireLogin();
$user = getCurrentUser();
if (!hasPermission('view_laporan') && !in_array($user['role'], ['bos', 'appOwner'])) {
    http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit();
}

$format      = strtolower($_GET['format'] ?? 'csv');
$jenis       = $_GET['jenis'] ?? 'comprehensive';
$start_date  = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$end_date    = $_GET['tanggal_selesai'] ?? date('Y-m-t');
$cabang_id   = (int)($_GET['cabang_id'] ?? 0) ?: null;

$reportGen = new \Kewer\Reporting\ReportGenerator(1, $start_date, $end_date);
switch ($jenis) {
    case 'financial':      $report = $reportGen->financialReport(); break;
    case 'loan_performance': $report = $reportGen->loanPerformanceReport(); break;
    case 'customer':       $report = $reportGen->customerReport(); break;
    default:               $report = $reportGen->comprehensiveReport(); break;
}

// Flatten report jadi rows untuk tabel
function flattenReport(array $report, string $jenis, string $start, string $end): array {
    $rows = [];
    $periode = "Periode: $start s/d $end";

    if ($jenis === 'financial' || $jenis === 'comprehensive') {
        $fin = ($jenis === 'comprehensive') ? ($report['financial'] ?? []) : $report;
        $rows[] = ['Laporan Keuangan', $periode, '', '', ''];
        $rows[] = ['Total Pemasukan', number_format($fin['total_income'] ?? 0, 0, ',', '.'), '', '', ''];
        $rows[] = ['Total Pengeluaran', number_format($fin['total_expense'] ?? 0, 0, ',', '.'), '', '', ''];
        $rows[] = ['Net Cashflow', number_format(($fin['total_income'] ?? 0) - ($fin['total_expense'] ?? 0), 0, ',', '.'), '', '', ''];
        $rows[] = ['', '', '', '', ''];
    }

    if ($jenis === 'loan_performance' || $jenis === 'comprehensive') {
        $loan = ($jenis === 'comprehensive') ? ($report['loan'] ?? []) : $report;
        $rows[] = ['Kinerja Pinjaman', $periode, '', '', ''];
        $rows[] = ['Status', 'Jumlah', 'Total', '', ''];
        foreach (($loan['status_distribution'] ?? []) as $s) {
            $rows[] = [ucfirst($s['status'] ?? '-'), $s['count'] ?? 0, 'Rp '.number_format($s['total'] ?? 0, 0, ',', '.'), '', ''];
        }
        $rows[] = ['', '', '', '', ''];
    }

    if ($jenis === 'customer' || $jenis === 'comprehensive') {
        $cust = ($jenis === 'comprehensive') ? ($report['customer'] ?? []) : $report;
        $rows[] = ['Laporan Nasabah', $periode, '', '', ''];
        $rows[] = ['Nasabah Baru', $cust['new_customers'] ?? 0, '', '', ''];
        $rows[] = ['Nasabah Aktif', $cust['active_customers'] ?? 0, '', '', ''];
        $rows[] = ['Nasabah dengan Pinjaman', $cust['customers_with_loans'] ?? 0, '', '', ''];
    }

    return $rows;
}

$rows   = flattenReport($report, $jenis, $start_date, $end_date);
$title  = ucfirst($jenis) . '_' . $start_date . '_' . $end_date;

// ─── CSV ──────────────────────────────────────────────────────────
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="Laporan_' . $title . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
    foreach ($rows as $row) fputcsv($out, $row, ';');
    fclose($out);
    exit();
}

// ─── PDF via dompdf ───────────────────────────────────────────────
if ($format === 'pdf') {
    $autoload = BASE_PATH . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        http_response_code(500);
        echo json_encode(['error' => 'dompdf tidak ditemukan. Jalankan composer install.']);
        exit();
    }
    require_once $autoload;

    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new \Dompdf\Dompdf($options);

    // Build HTML tabel
    $rows_html = '';
    foreach ($rows as $r) {
        $tds = implode('', array_map(fn($c) => '<td style="padding:4px 8px;border:1px solid #ddd;">' . htmlspecialchars((string)$c) . '</td>', $r));
        $rows_html .= "<tr>$tds</tr>";
    }
    $tanggal_cetak = date('d/m/Y H:i');
    $html = <<<HTML
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
<style>
  body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#333;}
  h2{margin:0 0 4px;font-size:15px;}
  .sub{color:#666;font-size:10px;margin-bottom:12px;}
  table{width:100%;border-collapse:collapse;}
  th{background:#2c3e50;color:#fff;padding:5px 8px;text-align:left;font-size:11px;}
  tr:nth-child(even) td{background:#f8f9fa;}
  .footer{margin-top:16px;font-size:9px;color:#999;text-align:right;}
</style></head><body>
<h2>Laporan Kewer — {$title}</h2>
<div class="sub">Dicetak: {$tanggal_cetak} | Oleh: {$user['nama']} ({$user['role']})</div>
<table><tbody>$rows_html</tbody></table>
<div class="footer">Kewer — Sistem Pinjaman Modal Pedagang Digital</div>
</body></html>
HTML;

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('Laporan_' . $title . '.pdf', ['Attachment' => true]);
    exit();
}

http_response_code(400);
echo json_encode(['error' => 'Format tidak dikenal. Gunakan: pdf atau csv']);
