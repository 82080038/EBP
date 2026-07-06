<?php

if (!class_exists('StockOpnameRepository')) {
    require_once __DIR__ . '/../Repositories/StockOpnameRepository.php';
}


class StockOpnameService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new StockOpnameRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createOpname($data, $userId, $tenantId, $branchId)
    {
        try {
            if (empty($data['opname_date'])) {
                return [
                    'success' => false,
                    'message' => 'Opname date is required'
                ];
            }

            $date = date('Ymd', strtotime($data['opname_date']));
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM stock_opname WHERE tenant_id = ? AND opname_number LIKE ?");
            $stmt->execute([$tenantId, "OP-$date-%"]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
            $opnameNumber = "OP-$date-$sequence";

            $opnameData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'opname_number' => $opnameNumber,
                'opname_date' => $data['opname_date'],
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId
            ];

            $opnameId = $this->repository->create($opnameData);

            return [
                'success' => true,
                'message' => 'Stock opname created successfully',
                'opname_id' => $opnameId,
                'opname_number' => $opnameNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create opname: ' . $e->getMessage()
            ];
        }
    }

    public function addItem($opnameId, $data, $tenantId)
    {
        try {
            $stmt = $this->db->prepare("SELECT opname_id FROM stock_opname WHERE opname_id = ? AND tenant_id = ?");
            $stmt->execute([$opnameId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Opname not found'
                ];
            }

            $itemData = [
                'opname_id' => $opnameId,
                'inventory_id' => $data['inventory_id'],
                'system_quantity' => $data['system_quantity'],
                'physical_quantity' => $data['physical_quantity'],
                'difference' => $data['physical_quantity'] - $data['system_quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'difference_value' => isset($data['unit_cost']) ? ($data['physical_quantity'] - $data['system_quantity']) * $data['unit_cost'] : null,
                'reason' => $data['reason'] ?? null
            ];

            $this->repository->createItem($itemData);

            return [
                'success' => true,
                'message' => 'Item added successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add item: ' . $e->getMessage()
            ];
        }
    }

    public function completeOpname($opnameId, $userId, $tenantId)
    {
        try {
            $stmt = $this->db->prepare("SELECT opname_id, status FROM stock_opname WHERE opname_id = ? AND tenant_id = ?");
            $stmt->execute([$opnameId, $tenantId]);
            $opname = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$opname) {
                return [
                    'success' => false,
                    'message' => 'Opname not found'
                ];
            }

            if ($opname['status'] !== 'DRAFT' && $opname['status'] !== 'IN_PROGRESS') {
                return [
                    'success' => false,
                    'message' => 'Only draft or in-progress opname can be completed'
                ];
            }

            $this->repository->update($opnameId, [
                'status' => 'COMPLETED',
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s')
            ]);

            // Update inventory quantities based on physical count
            $this->updateInventoryFromOpname($opnameId);

            return [
                'success' => true,
                'message' => 'Stock opname completed successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to complete opname: ' . $e->getMessage()
            ];
        }
    }

    private function updateInventoryFromOpname($opnameId)
    {
        $items = $this->repository->getItems($opnameId);

        foreach ($items as $item) {
            $sql = "UPDATE inventory SET quantity = ? WHERE inventory_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$item['physical_quantity'], $item['inventory_id']]);
        }
    }

    public function getOpnames($tenantId, $branchId = null)
    {
        try {
            $opnames = $this->repository->getByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Opnames retrieved successfully',
                'data' => $opnames
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get opnames: ' . $e->getMessage()
            ];
        }
    }
}
