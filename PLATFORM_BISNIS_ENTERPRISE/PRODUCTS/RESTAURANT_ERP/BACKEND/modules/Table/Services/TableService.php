<?php

if (!class_exists('TableRepository')) {
    require_once __DIR__ . '/../Repositories/TableRepository.php';
}



class TableService
{
    private $tableRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->tableRepository = new TableRepository();
        $this->transaction = new Transaction();
        $this->audit = new Audit();
    }

    public function getAllTables(int $tenantId, ?int $branchId = null): array
    {
        $tables = $this->tableRepository->findAll($tenantId, $branchId);
        return array_map(function($t) { return $t->toArray(); }, $tables);
    }

    public function getAvailableTables(int $tenantId, ?int $branchId = null): array
    {
        $tables = $this->tableRepository->findAvailable($tenantId, $branchId);
        return array_map(function($t) { return $t->toArray(); }, $tables);
    }

    public function getTable(int $tenantId, int $tableId): ?array
    {
        $table = $this->tableRepository->findById($tenantId, $tableId);
        return $table ? $table->toArray() : null;
    }

    public function createTable(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            $table = new \Modules\Table\Models\Table($data);
            
            // Check if table number already exists for this branch
            $existing = $this->tableRepository->findByNumber(
                $tenantId, 
                $table->branch_id, 
                $table->table_number
            );
            
            if ($existing) {
                $this->transaction->rollback();
                return false;
            }
            
            $result = $this->tableRepository->create($table);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'TABLE',
                    'action' => 'CREATE_TABLE',
                    'record_id' => $this->transaction->getLastInsertId(),
                    'table_name' => 'tables',
                    'new_values' => json_encode($data)
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateTable(int $tenantId, int $tableId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldTable = $this->tableRepository->findById($tenantId, $tableId);
            
            $data['tenant_id'] = $tenantId;
            $data['table_id'] = $tableId;
            $table = new \Modules\Table\Models\Table($data);
            
            $result = $this->tableRepository->update($table);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'TABLE',
                    'action' => 'UPDATE_TABLE',
                    'record_id' => $tableId,
                    'table_name' => 'tables',
                    'old_values' => json_encode($oldTable->toArray()),
                    'new_values' => json_encode($data)
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateTableStatus(int $tenantId, int $tableId, string $status): bool
    {
        $this->transaction->begin();
        
        try {
            $oldTable = $this->tableRepository->findById($tenantId, $tableId);
            
            $result = $this->tableRepository->updateStatus($tenantId, $tableId, $status);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'TABLE',
                    'action' => 'UPDATE_TABLE_STATUS',
                    'record_id' => $tableId,
                    'table_name' => 'tables',
                    'old_values' => json_encode(['status' => $oldTable->status]),
                    'new_values' => json_encode(['status' => $status])
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function deleteTable(int $tenantId, int $tableId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldTable = $this->tableRepository->findById($tenantId, $tableId);
            
            $result = $this->tableRepository->delete($tenantId, $tableId);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'TABLE',
                    'action' => 'DELETE_TABLE',
                    'record_id' => $tableId,
                    'table_name' => 'tables',
                    'old_values' => json_encode($oldTable->toArray())
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }
}
