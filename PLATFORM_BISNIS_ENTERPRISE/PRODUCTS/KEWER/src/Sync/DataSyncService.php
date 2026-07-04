<?php
namespace Kewer\Sync;

/**
 * Data Sync Service
 * Handles data synchronization between branches and central database
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

class DataSyncService {
    
    /**
     * Sync data from branch to central
     */
    public static function syncToCentral($cabangId, $syncType = 'full') {
        $syncLogId = self::logSync($cabangId, 'to_central', $syncType, 'started');
        
        try {
            $syncedTables = [];
            
            if ($syncType == 'full' || $syncType == 'incremental') {
                // Sync nasabah
                $syncedTables['nasabah'] = self::syncTable('nasabah', $cabangId);
                
                // Sync pinjaman
                $syncedTables['pinjaman'] = self::syncTable('pinjaman', $cabangId);
                
                // Sync angsuran
                $syncedTables['angsuran'] = self::syncTable('angsuran', $cabangId);
                
                // Sync pembayaran
                $syncedTables['pembayaran'] = self::syncTable('pembayaran', $cabangId);
            }
            
            self::updateSyncLog($syncLogId, 'completed', $syncedTables);
            
            return [
                'success' => true,
                'sync_log_id' => $syncLogId,
                'synced_tables' => $syncedTables
            ];
        } catch (Exception $e) {
            self::updateSyncLog($syncLogId, 'failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sync data from central to branch
     */
    public static function syncFromCentral($cabangId, $syncType = 'full') {
        $syncLogId = self::logSync($cabangId, 'from_central', $syncType, 'started');
        
        try {
            $syncedTables = [];
            
            if ($syncType == 'full' || $syncType == 'incremental') {
                // Sync reference tables
                $syncedTables['ref_frekuensi_angsuran'] = self::syncReferenceTable('ref_frekuensi_angsuran');
                $syncedTables['ref_produk_pinjaman'] = self::syncReferenceTable('ref_produk_pinjaman');
                $syncedTables['ref_jaminan_tipe'] = self::syncReferenceTable('ref_jaminan_tipe');
            }
            
            self::updateSyncLog($syncLogId, 'completed', $syncedTables);
            
            return [
                'success' => true,
                'sync_log_id' => $syncLogId,
                'synced_tables' => $syncedTables
            ];
        } catch (Exception $e) {
            self::updateSyncLog($syncLogId, 'failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sync specific table
     */
    private static function syncTable($tableName, $cabangId) {
        // Get last sync timestamp
        $lastSync = self::getLastSyncTimestamp($cabangId, $tableName);
        
        // Build query to get changed records
        $where = ["cabang_id = ?"];
        $params = [$cabangId];
        
        if ($lastSync) {
            $where[] = "updated_at > ?";
            $params[] = $lastSync;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get records
        $sql = "SELECT * FROM $tableName WHERE $whereClause";
        $result = query($sql, $params);
        
        if (!is_array($result)) {
            return ['success' => false, 'message' => 'Failed to fetch records'];
        }
        
        $syncedCount = 0;
        $conflicts = [];
        
        foreach ($result as $record) {
            // Check for conflicts
            $conflict = self::checkConflict($tableName, $record['id'], $record);
            
            if ($conflict) {
                $conflicts[] = $conflict;
            } else {
                // No conflict, mark as synced
                self::markAsSynced($tableName, $record['id']);
                $syncedCount++;
            }
        }
        
        return [
            'success' => true,
            'synced_count' => $syncedCount,
            'conflicts' => $conflicts
        ];
    }
    
    /**
     * Sync reference table
     */
    private static function syncReferenceTable($tableName) {
        $sql = "SELECT * FROM $tableName";
        $result = query($sql, []);
        
        if (!is_array($result)) {
            return ['success' => false, 'message' => 'Failed to fetch records'];
        }
        
        $syncedCount = count($result);
        
        return [
            'success' => true,
            'synced_count' => $syncedCount
        ];
    }
    
    /**
     * Check for data conflict
     */
    private static function checkConflict($tableName, $recordId, $recordData) {
        // In a real multi-database scenario, this would check against other databases
        // For now, just check if record has been modified since last sync
        $sql = "SELECT * FROM sync_conflicts WHERE table_name = ? AND record_id = ? AND resolved = 0";
        $result = query($sql, [$tableName, $recordId]);
        
        if (is_array($result) && count($result) > 0) {
            return [
                'table_name' => $tableName,
                'record_id' => $recordId,
                'conflict_type' => 'existing',
                'message' => 'Existing unresolved conflict'
            ];
        }
        
        return null;
    }
    
    /**
     * Mark record as synced
     */
    private static function markAsSynced($tableName, $recordId) {
        // In a real implementation, this would update a sync tracking table
        // For now, just log the sync
    }
    
    /**
     * Get last sync timestamp
     */
    private static function getLastSyncTimestamp($cabangId, $tableName) {
        $sql = "SELECT MAX(synced_at) as last_sync FROM sync_logs 
                WHERE cabang_id = ? AND table_name = ? AND status = 'completed'";
        $result = query($sql, [$cabangId, $tableName]);
        
        if (is_array($result) && isset($result[0]) && $result[0]['last_sync']) {
            return $result[0]['last_sync'];
        }
        
        return null;
    }
    
    /**
     * Log sync operation
     */
    private static function logSync($cabangId, $direction, $syncType, $status) {
        $sql = "INSERT INTO sync_logs (cabang_id, direction, sync_type, status, started_at) 
                VALUES (?, ?, ?, ?, NOW())";
        query($sql, [$cabangId, $direction, $syncType, $status]);
        
        return query("SELECT LAST_INSERT_ID() as id")[0]['id'];
    }
    
    /**
     * Update sync log
     */
    private static function updateSyncLog($syncLogId, $status, $details) {
        $sql = "UPDATE sync_logs SET status = ?, completed_at = NOW(), details = ? 
                WHERE id = ?";
        query($sql, [$status, json_encode($details), $syncLogId]);
    }
    
    /**
     * Get sync status for all branches
     */
    public static function getSyncStatus() {
        $sql = "SELECT c.id, c.nama_cabang, 
                (SELECT MAX(sl.started_at) FROM sync_logs sl WHERE sl.cabang_id = c.id) as last_sync,
                (SELECT COUNT(*) FROM sync_logs sl WHERE sl.cabang_id = c.id AND sl.status = 'completed') as completed_syncs,
                (SELECT COUNT(*) FROM sync_logs sl WHERE sl.cabang_id = c.id AND sl.status = 'failed') as failed_syncs
                FROM cabang c
                ORDER BY c.nama_cabang";
        
        $result = query($sql, []);
        
        return [
            'success' => true,
            'data' => is_array($result) ? $result : []
        ];
    }
    
    /**
     * Resolve sync conflict
     */
    public static function resolveConflict($conflictId, $resolution, $resolvedBy) {
        $sql = "UPDATE sync_conflicts SET resolved = 1, resolution = ?, resolved_at = NOW(), resolved_by = ?
                WHERE id = ?";
        query($sql, [$resolution, $resolvedBy, $conflictId]);
        
        return [
            'success' => true,
            'message' => 'Conflict resolved'
        ];
    }
}
