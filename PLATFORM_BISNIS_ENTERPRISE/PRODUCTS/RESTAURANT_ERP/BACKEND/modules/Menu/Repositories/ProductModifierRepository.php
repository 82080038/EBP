<?php



class ProductModifierRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createGroup($data)
    {
        $sql = "INSERT INTO product_modifier_groups (tenant_id, group_code, group_name, is_required, min_selections, max_selections, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['group_code'],
            $data['group_name'],
            $data['is_required'] ?? false,
            $data['min_selections'] ?? 0,
            $data['max_selections'] ?? 1,
            $data['status'] ?? 'ACTIVE'
        ]);
        return $this->db->lastInsertId();
    }

    public function createModifier($data)
    {
        $sql = "INSERT INTO product_modifiers (modifier_group_id, modifier_code, modifier_name, price_adjustment, is_available, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['modifier_group_id'],
            $data['modifier_code'],
            $data['modifier_name'],
            $data['price_adjustment'] ?? 0,
            $data['is_available'] ?? true,
            $data['status'] ?? 'ACTIVE'
        ]);
        return $this->db->lastInsertId();
    }

    public function createAssignment($data)
    {
        $sql = "INSERT INTO product_modifier_assignments (product_id, modifier_group_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['product_id'], $data['modifier_group_id']]);
        return $this->db->lastInsertId();
    }

    public function getGroupsByTenant($tenantId)
    {
        $sql = "SELECT * FROM product_modifier_groups WHERE tenant_id = ? AND status = 'ACTIVE' ORDER BY group_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getModifiersByGroup($groupId)
    {
        $sql = "SELECT * FROM product_modifiers WHERE modifier_group_id = ? AND status = 'ACTIVE' ORDER BY modifier_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getModifiersByProduct($productId)
    {
        $sql = "
            SELECT pmg.*, pm.*
            FROM product_modifier_assignments pma
            INNER JOIN product_modifier_groups pmg ON pma.modifier_group_id = pmg.modifier_group_id
            LEFT JOIN product_modifiers pm ON pmg.modifier_group_id = pm.modifier_group_id
            WHERE pma.product_id = ? AND pmg.status = 'ACTIVE'
            ORDER BY pmg.group_name ASC, pm.modifier_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
