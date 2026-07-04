<?php



class ProductVariantRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function create($data)
    {
        $sql = "INSERT INTO product_variants (product_id, variant_code, variant_name, price_adjustment, is_default, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['product_id'],
            $data['variant_code'],
            $data['variant_name'],
            $data['price_adjustment'] ?? 0,
            $data['is_default'] ?? false,
            $data['status'] ?? 'ACTIVE'
        ]);
        return $this->db->lastInsertId();
    }

    public function getByProduct($productId)
    {
        $sql = "SELECT * FROM product_variants WHERE product_id = ? AND status = 'ACTIVE' ORDER BY is_default DESC, variant_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($variantId)
    {
        $sql = "SELECT * FROM product_variants WHERE variant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$variantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($variantId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $variantId;
        
        $sql = "UPDATE product_variants SET " . implode(', ', $setClauses) . " WHERE variant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function delete($variantId)
    {
        $sql = "UPDATE product_variants SET status = 'INACTIVE' WHERE variant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$variantId]);
    }
}
