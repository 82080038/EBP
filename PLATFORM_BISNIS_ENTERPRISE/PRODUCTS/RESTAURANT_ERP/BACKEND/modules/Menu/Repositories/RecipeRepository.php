<?php




class RecipeRepository
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function findAll(int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, p.product_name 
            FROM recipes r
            JOIN products p ON r.product_id = p.product_id
            WHERE r.tenant_id = :tenant_id AND r.deleted_at IS NULL 
            ORDER BY r.recipe_name ASC
        ");
        $stmt->execute(['tenant_id' => $tenantId]);
        
        $recipes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $recipes[] = new Recipe($row);
        }
        
        return $recipes;
    }

    public function findById(int $tenantId, int $recipeId): ?Recipe
    {
        $stmt = $this->db->prepare("
            SELECT * FROM recipes 
            WHERE tenant_id = :tenant_id AND recipe_id = :recipe_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'recipe_id' => $recipeId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Recipe($row) : null;
    }

    public function findByProductId(int $tenantId, int $productId): ?Recipe
    {
        $stmt = $this->db->prepare("
            SELECT * FROM recipes 
            WHERE tenant_id = :tenant_id AND product_id = :product_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'product_id' => $productId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Recipe($row) : null;
    }

    public function create(Recipe $recipe): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO recipes 
            (tenant_id, product_id, recipe_code, recipe_name, instructions, yield_quantity, yield_unit, status)
            VALUES 
            (:tenant_id, :product_id, :recipe_code, :recipe_name, :instructions, :yield_quantity, :yield_unit, :status)
        ");
        
        return $stmt->execute([
            'tenant_id' => $recipe->tenant_id,
            'product_id' => $recipe->product_id,
            'recipe_code' => $recipe->recipe_code,
            'recipe_name' => $recipe->recipe_name,
            'instructions' => $recipe->instructions,
            'yield_quantity' => $recipe->yield_quantity ?? 1,
            'yield_unit' => $recipe->yield_unit ?? 'portion',
            'status' => $recipe->status ?? 'ACTIVE'
        ]);
    }

    public function update(Recipe $recipe): bool
    {
        $stmt = $this->db->prepare("
            UPDATE recipes 
            SET recipe_name = :recipe_name,
                instructions = :instructions,
                yield_quantity = :yield_quantity,
                yield_unit = :yield_unit,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND recipe_id = :recipe_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $recipe->tenant_id,
            'recipe_id' => $recipe->recipe_id,
            'recipe_name' => $recipe->recipe_name,
            'instructions' => $recipe->instructions,
            'yield_quantity' => $recipe->yield_quantity,
            'yield_unit' => $recipe->yield_unit,
            'status' => $recipe->status
        ]);
    }

    public function delete(int $tenantId, int $recipeId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE recipes 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE tenant_id = :tenant_id AND recipe_id = :recipe_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'recipe_id' => $recipeId]);
    }

    public function addIngredient(int $recipeId, int $ingredientId, float $quantity, string $unit): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit)
            VALUES (:recipe_id, :ingredient_id, :quantity, :unit)
        ");
        
        return $stmt->execute([
            'recipe_id' => $recipeId,
            'ingredient_id' => $ingredientId,
            'quantity' => $quantity,
            'unit' => $unit
        ]);
    }

    public function removeIngredient(int $recipeId, int $ingredientId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM recipe_ingredients 
            WHERE recipe_id = :recipe_id AND ingredient_id = :ingredient_id
        ");
        
        return $stmt->execute(['recipe_id' => $recipeId, 'ingredient_id' => $ingredientId]);
    }

    public function getIngredients(int $recipeId): array
    {
        $stmt = $this->db->prepare("
            SELECT ri.*, p.product_name, p.product_code 
            FROM recipe_ingredients ri
            JOIN products p ON ri.ingredient_id = p.product_id
            WHERE ri.recipe_id = :recipe_id
        ");
        $stmt->execute(['recipe_id' => $recipeId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
