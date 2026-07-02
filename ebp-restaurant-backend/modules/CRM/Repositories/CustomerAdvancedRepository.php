<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class CustomerAdvancedRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function upsertFavorite($tenantId, $branchId, $customerId, $productId)
    {
        $sql = "INSERT INTO customer_favorites (tenant_id, branch_id, customer_id, product_id, order_count, last_ordered_at) VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE order_count = order_count + 1, last_ordered_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId, $productId]);
    }

    public function getCustomerFavorites($tenantId, $branchId, $customerId)
    {
        $sql = "SELECT cf.*, p.product_name, p.product_code, p.price FROM customer_favorites cf JOIN products p ON cf.product_id = p.product_id WHERE cf.tenant_id = ? AND cf.branch_id = ? AND cf.customer_id = ? ORDER BY cf.order_count DESC, cf.last_ordered_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerHabitAnalysis($tenantId, $branchId, $customerId)
    {
        $sql = "
            SELECT 
                DATE(o.created_at) as order_date,
                HOUR(o.created_at) as order_hour,
                o.order_type,
                COUNT(o.order_id) as order_count,
                SUM(o.total_amount) as total_spent,
                GROUP_CONCAT(DISTINCT p.product_name) as products_ordered
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.tenant_id = ? 
            AND o.branch_id = ?
            AND o.user_id = ?
            AND o.deleted_at IS NULL
            GROUP BY DATE(o.created_at), HOUR(o.created_at), o.order_type
            ORDER BY o.created_at DESC
            LIMIT 50
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBirthdayPromotion($data)
    {
        $sql = "INSERT INTO birthday_promotions (tenant_id, branch_id, customer_id, promotion_year, promotion_type, discount_percentage, free_product_id, points_bonus, valid_from, valid_until, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['customer_id'],
            $data['promotion_year'],
            $data['promotion_type'],
            $data['discount_percentage'] ?? null,
            $data['free_product_id'] ?? null,
            $data['points_bonus'] ?? null,
            $data['valid_from'],
            $data['valid_until'],
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getBirthdayPromotions($tenantId, $branchId, $customerId = null)
    {
        $sql = "SELECT bp.*, c.customer_name, p.product_name as free_product_name FROM birthday_promotions bp LEFT JOIN customers c ON bp.customer_id = c.customer_id LEFT JOIN products p ON bp.free_product_id = p.product_id WHERE bp.tenant_id = ? AND bp.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($customerId) {
            $sql .= " AND bp.customer_id = ?";
            $params[] = $customerId;
        }
        
        $sql .= " ORDER BY bp.promotion_year DESC, bp.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function useBirthdayPromotion($promotionId, $tenantId)
    {
        $sql = "UPDATE birthday_promotions SET is_used = TRUE, used_at = CURRENT_TIMESTAMP WHERE promotion_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$promotionId, $tenantId]);
    }
}
