<?php
namespace Kewer\Export;

class Exporter {
    private $data;
    private $filename;
    private $format;
    
    public function __construct(array $data, $filename = 'export', $format = 'csv') {
        $this->data = $data;
        $this->filename = $filename;
        $this->format = strtolower($format);
    }
    
    /**
     * Export data to CSV
     */
    public function toCSV() {
        if (empty($this->data)) {
            throw new \Exception('No data to export');
        }
        
        $filename = $this->filename . '.csv';
        $filepath = BASE_PATH . '/exports/' . $filename;
        
        // Create exports directory if it doesn't exist
        $exportDir = dirname($filepath);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Add BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write headers
        $headers = array_keys($this->data[0]);
        fputcsv($file, $headers);
        
        // Write data
        foreach ($this->data as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return $filepath;
    }
    
    /**
     * Export data to Excel (XLSX)
     */
    public function toExcel() {
        // For now, use CSV format (can be upgraded to PhpSpreadsheet later)
        return $this->toCSV();
    }
    
    /**
     * Export data to JSON
     */
    public function toJSON() {
        $filename = $this->filename . '.json';
        $filepath = BASE_PATH . '/exports/' . $filename;
        
        // Create exports directory if it doesn't exist
        $exportDir = dirname($filepath);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        file_put_contents($filepath, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $filepath;
    }
    
    /**
     * Export data to PDF using DomPDF
     */
    public function toPDF() {
        if (empty($this->data)) {
            throw new \Exception('No data to export');
        }
        
        // Check if DomPDF is available
        if (!class_exists('Dompdf\Dompdf')) {
            throw new \Exception('DomPDF library not installed. Run: composer require dompdf/dompdf');
        }
        
        $filename = $this->filename . '.pdf';
        $filepath = BASE_PATH . '/exports/' . $filename;
        
        // Create exports directory if it doesn't exist
        $exportDir = dirname($filepath);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
        
        // Import DomPDF
        require_once BASE_PATH . '/vendor/autoload.php';
        
        // Create HTML table from data
        $html = $this->generateHTMLTable();
        
        // Initialize DomPDF
        $dompdf = new \Dompdf\Dompdf();
        
        // Load HTML
        $dompdf->loadHtml($html);
        
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        
        // Render PDF
        $dompdf->render();
        
        // Save PDF to file
        file_put_contents($filepath, $dompdf->output());
        
        return $filepath;
    }
    
    /**
     * Generate HTML table from data
     */
    private function generateHTMLTable() {
        if (empty($this->data)) {
            return '';
        }
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($this->filename) . '</h1>
    <table>
        <thead>
            <tr>';
        
        // Add headers
        $headers = array_keys($this->data[0]);
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        
        $html .= '</tr>
        </thead>
        <tbody>';
        
        // Add data rows
        foreach ($this->data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value ?? '') . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
    </table>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Export based on format
     */
    public function export() {
        switch ($this->format) {
            case 'csv':
                return $this->toCSV();
            case 'excel':
            case 'xlsx':
                return $this->toExcel();
            case 'json':
                return $this->toJSON();
            case 'pdf':
                return $this->toPDF();
            default:
                throw new \Exception("Unsupported export format: {$this->format}");
        }
    }
    
    /**
     * Download file
     */
    public function download() {
        $filepath = $this->export();
        
        if (!file_exists($filepath)) {
            throw new \Exception('Export file not found');
        }
        
        $filename = basename($filepath);
        $filesize = filesize($filepath);
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $filesize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($filepath);
        
        // Optionally delete file after download
        // unlink($filepath);
        
        exit;
    }
    
    /**
     * Set data
     */
    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Set filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
        return $this;
    }
    
    /**
     * Set format
     */
    public function setFormat($format) {
        $this->format = strtolower($format);
        return $this;
    }
}
?>
