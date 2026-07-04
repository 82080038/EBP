<?php
/**
 * OCR KTP Feature
 * 
 * Handles OCR (Optical Character Recognition) for KTP (Indonesian ID Card)
 * Extracts data from KTP images: NIK, Nama, TTL, Alamat
 * 
 * Requires:
 * 1. Install Tesseract OCR on server
 *    - Linux: sudo apt-get install tesseract-ocr tesseract-ocr-ind
 *    - Windows: Download from https://github.com/UB-Mannheim/tesseract/wiki
 * 2. Install PHP wrapper: composer require thiagoalessio/tesseract_ocr
 * 3. Configure Tesseract path if needed
 */

require_once BASE_PATH . '/vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrKtp {
    private $tesseractPath;
    
    public function __construct($tesseractPath = null) {
        // Tesseract path - auto-detect based on OS
        if ($tesseractPath) {
            $this->tesseractPath = $tesseractPath;
        } else {
            // Auto-detect based on OS
            if (PHP_OS_FAMILY === 'Windows') {
                $this->tesseractPath = 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe';
            } else {
                $this->tesseractPath = '/usr/bin/tesseract';
            }
        }
    }
    
    /**
     * Extract text from KTP image
     * 
     * @param string $imagePath Path to KTP image file
     * @return array Extracted text and parsed data
     */
    public function extractFromImage($imagePath) {
        // Check if image file exists
        if (!file_exists($imagePath)) {
            return [
                'success' => false,
                'error' => 'File gambar tidak ditemukan'
            ];
        }
        
        try {
            // Extract text using Tesseract OCR library
            $text = $this->runTesseract($imagePath);
            
            // Parse extracted text for KTP data
            $ktpData = $this->parseKtpText($text);
            
            return [
                'success' => true,
                'raw_text' => $text,
                'parsed_data' => $ktpData
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Gagal mengekstrak data: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Run Tesseract OCR using PHP library
     */
    private function runTesseract($imagePath) {
        try {
            $ocr = new TesseractOCR($imagePath);
            
            // Set Tesseract executable path if custom path is provided
            if ($this->tesseractPath && file_exists($this->tesseractPath)) {
                $ocr->executable($this->tesseractPath);
            }
            
            // Use Indonesian and English language
            $ocr->lang('ind', 'eng');
            
            // Run OCR
            $text = $ocr->run();
            
            return $text;
            
        } catch (Exception $e) {
            throw new Exception("Tesseract OCR error: " . $e->getMessage());
        }
    }
    
    /**
     * Parse extracted text for KTP data
     * 
     * This is a simplified parser. For production use, you may need:
     * - More sophisticated regex patterns
     * - Machine learning model for better accuracy
     * - Manual verification step
     */
    private function parseKtpText($text) {
        $data = [
            'nik' => $this->extractNIK($text),
            'nama' => $this->extractNama($text),
            'tempat_lahir' => $this->extractTempatLahir($text),
            'tanggal_lahir' => $this->extractTanggalLahir($text),
            'alamat' => $this->extractAlamat($text),
            'confidence' => $this->calculateConfidence($text)
        ];
        
        return $data;
    }
    
    /**
     * Extract NIK (16 digit number)
     */
    private function extractNIK($text) {
        // Pattern: 16 consecutive digits
        if (preg_match('/\b\d{16}\b/', $text, $matches)) {
            return $matches[0];
        }
        return null;
    }
    
    /**
     * Extract Name
     */
    private function extractNama($text) {
        // Pattern: "Nama:" followed by text until next field
        if (preg_match('/Nama\s*[:]\s*([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Alternative: Look for "Nama" keyword
        if (preg_match('/Nama\s+([A-Z][a-zA-Z\s]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Extract Place of Birth
     */
    private function extractTempatLahir($text) {
        // Pattern: "Tempat/Tgl Lahir:" followed by place and date
        if (preg_match('/Tempat\s*[\/]\s*Tgl\s*Lahir\s*[:]\s*([A-Z][a-zA-Z\s]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Alternative: Look for city name pattern
        if (preg_match('/Lahir\s*[:]\s*([A-Z][a-zA-Z\s]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Extract Date of Birth
     */
    private function extractTanggalLahir($text) {
        // Pattern: Date in DD-MM-YYYY or DD/MM/YYYY format
        if (preg_match('/\b(\d{2}[-\/]\d{2}[-\/]\d{4})\b/', $text, $matches)) {
            return $matches[1];
        }
        
        // Alternative: Look for year pattern
        if (preg_match('/(\d{4})/', $text, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Extract Address
     */
    private function extractAlamat($text) {
        // Pattern: "Alamat:" followed by address text
        if (preg_match('/Alamat\s*[:]\s*([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Alternative: Look for street/pattern
        if (preg_match('/Jl\.?\s+([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Calculate confidence score based on extraction results
     */
    private function calculateConfidence($text) {
        $score = 0;
        $maxScore = 5;
        
        if ($this->extractNIK($text)) $score++;
        if ($this->extractNama($text)) $score++;
        if ($this->extractTempatLahir($text)) $score++;
        if ($this->extractTanggalLahir($text)) $score++;
        if ($this->extractAlamat($text)) $score++;
        
        return ($score / $maxScore) * 100;
    }
    
    /**
     * Validate KTP data
     */
    public function validateKtpData($data) {
        $errors = [];
        
        // Validate NIK (16 digits)
        if (!$data['nik'] || !preg_match('/^\d{16}$/', $data['nik'])) {
            $errors[] = 'NIK harus 16 digit angka';
        }
        
        // Validate Name (not empty)
        if (!$data['nama'] || strlen($data['nama']) < 3) {
            $errors[] = 'Nama tidak valid';
        }
        
        // Validate Date of Birth
        if ($data['tanggal_lahir']) {
            $date = DateTime::createFromFormat('d-m-Y', $data['tanggal_lahir']);
            if (!$date) {
                $errors[] = 'Format tanggal lahir tidak valid (harus DD-MM-YYYY)';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Manual verification form data
     * 
     * This method provides a fallback when OCR is not available
     * or when manual verification is preferred
     */
    public function manualVerification($formData) {
        $data = [
            'nik' => $formData['nik'] ?? null,
            'nama' => $formData['nama'] ?? null,
            'tempat_lahir' => $formData['tempat_lahir'] ?? null,
            'tanggal_lahir' => $formData['tanggal_lahir'] ?? null,
            'alamat' => $formData['alamat'] ?? null,
            'confidence' => 100, // Manual input = 100% confidence
            'source' => 'manual'
        ];
        
        $validation = $this->validateKtpData($data);
        
        return [
            'success' => $validation['valid'],
            'data' => $data,
            'errors' => $validation['errors']
        ];
    }
}
?>
