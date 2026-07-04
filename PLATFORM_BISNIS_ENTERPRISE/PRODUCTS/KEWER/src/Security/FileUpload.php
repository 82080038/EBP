<?php
namespace Kewer\Security;

class FileUpload {
    private $allowedTypes;
    private $maxSize;
    private $uploadDir;
    private $errors = [];
    
    public function __construct($uploadDir = null) {
        $this->allowedTypes = explode(',', ALLOWED_FILE_TYPES);
        $this->maxSize = UPLOAD_MAX_SIZE;
        $this->uploadDir = $uploadDir ?? UPLOADS_PATH;
    }
    
    /**
     * Validate uploaded file
     */
    public function validate($file) {
        $this->errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'No file uploaded or upload error';
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds maximum limit of ' . ($this->maxSize / 1024 / 1024) . 'MB';
            return false;
        }
        
        // Check file type
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $this->allowedTypes)) {
            $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes);
            return false;
        }
        
        // Validate actual file content (MIME type)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'application/pdf' => ['pdf']
        ];
        
        if (!isset($allowedMimes[$mimeType]) || !in_array($fileExt, $allowedMimes[$mimeType])) {
            $this->errors[] = 'Invalid file content or MIME type mismatch';
            return false;
        }
        
        // Check for potential malicious files
        if ($this->isMalicious($file['tmp_name'])) {
            $this->errors[] = 'File contains malicious content';
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for malicious file content
     */
    private function isMalicious($filePath) {
        // Check for PHP tags in image files
        $content = file_get_contents($filePath);
        
        // Check for common PHP patterns
        $patterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<script/i',
            '/javascript:/i',
            '/base64_decode/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Upload file with secure naming
     */
    public function upload($file, $subdir = '') {
        if (!$this->validate($file)) {
            return ['success' => false, 'errors' => $this->errors];
        }
        
        // Create upload directory if it doesn't exist
        $targetDir = $this->uploadDir;
        if ($subdir) {
            $targetDir .= '/' . trim($subdir, '/');
        }
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Generate secure filename
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = $this->generateSecureName($fileExt);
        $targetPath = $targetDir . '/' . $fileName;
        
        // Move file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'errors' => ['Failed to move uploaded file']];
        }
        
        // Set secure permissions
        chmod($targetPath, 0644);
        
        // Return relative path for database storage
        $relativePath = $subdir ? $subdir . '/' . $fileName : $fileName;
        
        return [
            'success' => true,
            'filename' => $fileName,
            'path' => $relativePath,
            'full_path' => $targetPath,
            'size' => $file['size'],
            'type' => $fileExt
        ];
    }
    
    /**
     * Generate secure random filename
     */
    private function generateSecureName($extension) {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }
    
    /**
     * Delete file
     */
    public function delete($filePath) {
        $fullPath = $this->uploadDir . '/' . $filePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }
}
?>
