<?php

namespace Modules\Upload\Services;

use Core\Database;
use Core\Transaction;
use Core\Audit;

class UploadService
{
    private $db;
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $this->maxFileSize = 5 * 1024 * 1024; // 5MB
    }
    
    /**
     * Upload file
     */
    public function uploadFile($file, $tenantId, $userId, $category = 'general')
    {
        Transaction::begin();
        
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $this->generateFilename($extension);
            
            // Create directory if not exists
            $categoryDir = $this->uploadDir . $category . '/';
            if (!is_dir($categoryDir)) {
                mkdir($categoryDir, 0755, true);
            }
            
            // Move file
            $filepath = $categoryDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => false,
                    'message' => 'Failed to move uploaded file'
                ];
            }
            
            // Save file record to database
            $fileData = [
                'tenant_id' => $tenantId,
                'file_name' => $file['name'],
                'file_path' => "uploads/{$category}/{$filename}",
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'file_category' => $category,
                'uploaded_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $fileId = $this->saveFileRecord($fileData);
            
            Audit::log($tenantId, $userId, 'FILE_UPLOAD', "Uploaded file: {$file['name']}");
            
            Transaction::commit();
            
            return [
                'success' => true,
                'file_id' => $fileId,
                'file_path' => $fileData['file_path'],
                'file_name' => $file['name']
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload multiple files
     */
    public function uploadMultipleFiles($files, $tenantId, $userId, $category = 'general')
    {
        $results = [];
        $successCount = 0;
        
        foreach ($files as $file) {
            $result = $this->uploadFile($file, $tenantId, $userId, $category);
            $results[] = $result;
            if ($result['success']) {
                $successCount++;
            }
        }
        
        return [
            'success' => $successCount > 0,
            'total' => count($files),
            'success_count' => $successCount,
            'results' => $results
        ];
    }
    
    /**
     * Delete file
     */
    public function deleteFile($fileId, $tenantId, $userId)
    {
        Transaction::begin();
        
        try {
            // Get file record
            $file = $this->getFileRecord($fileId, $tenantId);
            
            if (!$file) {
                return [
                    'success' => false,
                    'message' => 'File not found'
                ];
            }
            
            // Delete physical file
            $filepath = __DIR__ . '/../../public/' . $file['file_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Delete database record
            $this->deleteFileRecord($fileId, $tenantId);
            
            Audit::log($tenantId, $userId, 'FILE_DELETE', "Deleted file: {$file['file_name']}");
            
            Transaction::commit();
            
            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            return [
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get file by ID
     */
    public function getFile($fileId, $tenantId)
    {
        return $this->getFileRecord($fileId, $tenantId);
    }
    
    /**
     * Get files by category
     */
    public function getFilesByCategory($tenantId, $category, $limit = 100)
    {
        $sql = "SELECT * FROM file_uploads 
                WHERE tenant_id = :tenant_id 
                AND file_category = :category 
                AND deleted_at IS NULL 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'category' => $category, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Validate file
     */
    private function validateFile($file)
    {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'valid' => false,
                'message' => 'No file uploaded'
            ];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return [
                'valid' => false,
                'message' => 'File size exceeds maximum limit'
            ];
        }
        
        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            return [
                'valid' => false,
                'message' => 'File type not allowed'
            ];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => 'Upload error: ' . $file['error']
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generate unique filename
     */
    private function generateFilename($extension)
    {
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    /**
     * Save file record to database
     */
    private function saveFileRecord($data)
    {
        $sql = "INSERT INTO file_uploads (tenant_id, file_name, file_path, file_size, 
                                         file_type, file_category, uploaded_by, created_at)
                VALUES (:tenant_id, :file_name, :file_path, :file_size,
                        :file_type, :file_category, :uploaded_by, :created_at)";
        
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute($data);
        return $this->db->connect()->lastInsertId();
    }
    
    /**
     * Get file record from database
     */
    private function getFileRecord($fileId, $tenantId)
    {
        $sql = "SELECT * FROM file_uploads 
                WHERE id = :file_id 
                AND tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->connect()->prepare($sql);
        $stmt->execute(['file_id' => $fileId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete file record from database
     */
    private function deleteFileRecord($fileId, $tenantId)
    {
        $sql = "UPDATE file_uploads 
                SET deleted_at = NOW() 
                WHERE id = :file_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->connect()->prepare($sql);
        return $stmt->execute(['file_id' => $fileId, 'tenant_id' => $tenantId]);
    }
}
