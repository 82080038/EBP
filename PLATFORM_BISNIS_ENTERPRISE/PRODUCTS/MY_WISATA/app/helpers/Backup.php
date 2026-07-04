<?php
/**
 * MyWisata Application - Backup Helper
 * 
 * Handles database backup and recovery.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Backup {
    
    /**
     * Backup database
     * 
     * @param string $outputFile Output file path
     * @return bool
     */
    public static function backupDatabase($outputFile = null) {
        if (!$outputFile) {
            $backupDir = APP_ROOT . '/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            $outputFile = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
        }
        
        $config = require APP_ROOT . '/app/config/database.php';
        
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $outputFile
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            Logger::info('Database backup successful', ['file' => $outputFile]);
            return true;
        } else {
            Logger::error('Database backup failed', ['return_code' => $returnCode]);
            return false;
        }
    }
    
    /**
     * Restore database
     * 
     * @param string $backupFile Backup file path
     * @return bool
     */
    public static function restoreDatabase($backupFile) {
        if (!file_exists($backupFile)) {
            Logger::error('Backup file not found', ['file' => $backupFile]);
            return false;
        }
        
        $config = require APP_ROOT . '/app/config/database.php';
        
        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s',
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $backupFile
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            Logger::info('Database restore successful', ['file' => $backupFile]);
            return true;
        } else {
            Logger::error('Database restore failed', ['return_code' => $returnCode]);
            return false;
        }
    }
    
    /**
     * List backup files
     * 
     * @return array
     */
    public static function listBackups() {
        $backupDir = APP_ROOT . '/backups';
        
        if (!is_dir($backupDir)) {
            return [];
        }
        
        $files = glob($backupDir . '/*.sql');
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'created_at' => filemtime($file)
            ];
        }
        
        usort($backups, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });
        
        return $backups;
    }
    
    /**
     * Delete backup file
     * 
     * @param string $backupFile Backup file path
     * @return bool
     */
    public static function deleteBackup($backupFile) {
        if (file_exists($backupFile)) {
            if (unlink($backupFile)) {
                Logger::info('Backup file deleted', ['file' => $backupFile]);
                return true;
            }
        }
        
        Logger::error('Failed to delete backup file', ['file' => $backupFile]);
        return false;
    }
}
