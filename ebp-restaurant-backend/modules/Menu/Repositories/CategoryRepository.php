<?php
if (!class_exists('database')) {
    require_once __DIR__ . "/../../../config/database.php";
}

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class CategoryRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function findAll(int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM categories 
            WHERE tenant_id = :tenant_id AND deleted_at IS NULL 
            ORDER BY sort_order ASC, category_name ASC
        ");
        $stmt->execute(['tenant_id' => $tenantId]);
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = new Category($row);
        }
        
        return $categories;
    }

    public function findById(int $tenantId, int $categoryId): ?Category
    {
        $stmt = $this->db->prepare("
            SELECT * FROM categories 
            WHERE tenant_id = :tenant_id AND category_id = :category_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'category_id' => $categoryId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Category($row) : null;
    }

    public function create(Category $category): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO categories 
            (tenant_id, category_code, category_name, description, parent_id, sort_order, status)
            VALUES 
            (:tenant_id, :category_code, :category_name, :description, :parent_id, :sort_order, :status)
        ");
        
        return $stmt->execute([
            'tenant_id' => $category->tenant_id,
            'category_code' => $category->category_code,
            'category_name' => $category->category_name,
            'description' => $category->description,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order ?? 0,
            'status' => $category->status ?? 'ACTIVE'
        ]);
    }

    public function update(Category $category): bool
    {
        $stmt = $this->db->prepare("
            UPDATE categories 
            SET category_name = :category_name,
                description = :description,
                parent_id = :parent_id,
                sort_order = :sort_order,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND category_id = :category_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $category->tenant_id,
            'category_id' => $category->category_id,
            'category_name' => $category->category_name,
            'description' => $category->description,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'status' => $category->status
        ]);
    }

    public function delete(int $tenantId, int $categoryId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE categories 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE tenant_id = :tenant_id AND category_id = :category_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'category_id' => $categoryId]);
    }
}
