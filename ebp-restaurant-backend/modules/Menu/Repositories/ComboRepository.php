<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class ComboRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCombo($data)
    {
        $sql = "INSERT INTO combos (tenant_id, combo_code, combo_name, combo_type, base_price, discount_percentage, discount_amount, is_active, valid_from, valid_until, max_redemptions, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['combo_code'],
            $data['combo_name'],
            $data['combo_type'],
            $data['base_price'],
            $data['discount_percentage'] ?? 0,
            $data['discount_amount'] ?? 0,
            $data['is_active'] ?? true,
            $data['valid_from'] ?? null,
            $data['valid_until'] ?? null,
            $data['max_redemptions'] ?? null,
            $data['description'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function createComboGroup($data)
    {
        $sql = "INSERT INTO combo_groups (combo_id, group_name, min_selections, max_selections, sort_order) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['combo_id'],
            $data['group_name'],
            $data['min_selections'] ?? 1,
            $data['max_selections'] ?? 1,
            $data['sort_order'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function createComboItem($data)
    {
        $sql = "INSERT INTO combo_items (combo_group_id, product_id, is_default, price_adjustment, sort_order) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['combo_group_id'],
            $data['product_id'],
            $data['is_default'] ?? false,
            $data['price_adjustment'] ?? 0,
            $data['sort_order'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function getCombosByTenant($tenantId)
    {
        $sql = "SELECT * FROM combos WHERE tenant_id = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY combo_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComboById($comboId)
    {
        $sql = "SELECT * FROM combos WHERE combo_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$comboId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getComboGroups($comboId)
    {
        $sql = "SELECT * FROM combo_groups WHERE combo_id = ? ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$comboId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComboItems($comboGroupId)
    {
        $sql = "SELECT ci.*, p.product_name, p.price FROM combo_items ci 
                LEFT JOIN products p ON ci.product_id = p.product_id 
                WHERE ci.combo_group_id = ? ORDER BY ci.sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$comboGroupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
