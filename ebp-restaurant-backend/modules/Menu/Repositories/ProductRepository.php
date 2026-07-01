<?php
require_once __DIR__ . "/../../../config/database.php";



class ProductRepository
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function findAll(int $tenantId, ?int $categoryId = null): array
    {
        $sql = "
            SELECT p.*, c.category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.tenant_id = :tenant_id AND p.deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($categoryId !== null) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        
        $sql .= " ORDER BY p.product_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = new Product($row);
        }
        
        return $products;
    }

    public function findById(int $tenantId, int $productId): ?Product
    {
        $stmt = $this->db->prepare("
            SELECT * FROM products 
            WHERE tenant_id = :tenant_id AND product_id = :product_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'product_id' => $productId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Product($row) : null;
    }

    public function create(Product $product): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO products 
            (tenant_id, category_id, product_code, product_name, description, price, cost, image_url, is_available, preparation_time, status)
            VALUES 
            (:tenant_id, :category_id, :product_code, :product_name, :description, :price, :cost, :image_url, :is_available, :preparation_time, :status)
        ");
        
        return $stmt->execute([
            'tenant_id' => $product->tenant_id,
            'category_id' => $product->category_id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'description' => $product->description,
            'price' => $product->price,
            'cost' => $product->cost,
            'image_url' => $product->image_url,
            'is_available' => $product->is_available ?? true,
            'preparation_time' => $product->preparation_time ?? 0,
            'status' => $product->status ?? 'ACTIVE'
        ]);
    }

    public function update(Product $product): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET category_id = :category_id,
                product_name = :product_name,
                description = :description,
                price = :price,
                cost = :cost,
                image_url = :image_url,
                is_available = :is_available,
                preparation_time = :preparation_time,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND product_id = :product_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->product_id,
            'category_id' => $product->category_id,
            'product_name' => $product->product_name,
            'description' => $product->description,
            'price' => $product->price,
            'cost' => $product->cost,
            'image_url' => $product->image_url,
            'is_available' => $product->is_available,
            'preparation_time' => $product->preparation_time,
            'status' => $product->status
        ]);
    }

    public function delete(int $tenantId, int $productId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE tenant_id = :tenant_id AND product_id = :product_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'product_id' => $productId]);
    }
}
