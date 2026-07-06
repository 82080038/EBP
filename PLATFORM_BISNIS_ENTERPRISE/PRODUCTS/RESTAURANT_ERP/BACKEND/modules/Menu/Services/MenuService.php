<?php

if (!class_exists('CategoryRepository')) {
    require_once __DIR__ . '/../Repositories/CategoryRepository.php';
}
if (!class_exists('ProductRepository')) {
    require_once __DIR__ . '/../Repositories/ProductRepository.php';
}
if (!class_exists('RecipeRepository')) {
    require_once __DIR__ . '/../Repositories/RecipeRepository.php';
}
if (!class_exists('Product')) {
    require_once __DIR__ . '/../Models/Product.php';
}
if (!class_exists('Category')) {
    require_once __DIR__ . '/../Models/Category.php';
}


class MenuService
{
    private $categoryRepository;
    private $productRepository;
    private $recipeRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        $this->productRepository = new ProductRepository();
        $this->recipeRepository = new RecipeRepository();
        $this->transaction = new Transaction();
        // $this->audit = new Audit();
    }

    // Category Methods
    public function getAllCategories(int $tenantId): array
    {
        return $this->categoryRepository->findAll($tenantId);
    }

    public function getCategory(int $tenantId, int $categoryId): ?array
    {
        $category = $this->categoryRepository->findById($tenantId, $categoryId);
        return $category ? $category->toArray() : null;
    }

    public function createCategory(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            $category = new \Modules\Menu\Models\Category($data);
            
            $result = $this->categoryRepository->create($category);
            
            if ($result) {
                // $this->audit->log();
                
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

    public function updateCategory(int $tenantId, int $categoryId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldCategory = $this->categoryRepository->findById($tenantId, $categoryId);
            
            $data['tenant_id'] = $tenantId;
            $data['category_id'] = $categoryId;
            $category = new \Modules\Menu\Models\Category($data);
            
            $result = $this->categoryRepository->update($category);
            
            if ($result) {
                // $this->audit->log();
                
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

    public function deleteCategory(int $tenantId, int $categoryId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldCategory = $this->categoryRepository->findById($tenantId, $categoryId);
            
            $result = $this->categoryRepository->delete($tenantId, $categoryId);
            
            if ($result) {
                // $this->audit->log();
                
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

    // Product Methods
    public function getAllProducts(int $tenantId, ?int $categoryId = null): array
    {
        $products = $this->productRepository->findAll($tenantId, $categoryId);
        return array_map(function($p) { return $p->toArray(); }, $products);
    }

    public function getProduct(int $tenantId, int $productId): ?array
    {
        $product = $this->productRepository->findById($tenantId, $productId);
        return $product ? $product->toArray() : null;
    }

    public function createProduct(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            $product = new \Modules\Menu\Models\Product($data);
            
            $result = $this->productRepository->create($product);
            
            if ($result) {
                // $this->audit->log();
                
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

    public function updateProduct(int $tenantId, int $productId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldProduct = $this->productRepository->findById($tenantId, $productId);
            
            $data['tenant_id'] = $tenantId;
            $data['product_id'] = $productId;
            $product = new \Modules\Menu\Models\Product($data);
            
            $result = $this->productRepository->update($product);
            
            if ($result) {
                // $this->audit->log();
                
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

    public function deleteProduct(int $tenantId, int $productId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldProduct = $this->productRepository->findById($tenantId, $productId);
            
            $result = $this->productRepository->delete($tenantId, $productId);
            
            if ($result) {
                // $this->audit->log();
                
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

    // Recipe Methods
    public function getAllRecipes(int $tenantId): array
    {
        $recipes = $this->recipeRepository->findAll($tenantId);
        return array_map(function($r) { return $r->toArray(); }, $recipes);
    }

    public function getRecipe(int $tenantId, int $recipeId): ?array
    {
        $recipe = $this->recipeRepository->findById($tenantId, $recipeId);
        if ($recipe) {
            $data = $recipe->toArray();
            $data['ingredients'] = $this->recipeRepository->getIngredients($recipeId);
            return $data;
        }
        return null;
    }

    public function createRecipe(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            $recipe = new \Modules\Menu\Models\Recipe($data);
            
            $result = $this->recipeRepository->create($recipe);
            
            if ($result) {
                $recipeId = $this->transaction->getLastInsertId();
                
                // Add ingredients if provided
                if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                    foreach ($data['ingredients'] as $ingredient) {
                        $this->recipeRepository->addIngredient(
                            $recipeId,
                            $ingredient['ingredient_id'],
                            $ingredient['quantity'],
                            $ingredient['unit']
                        );
                    }
                }
                
                // $this->audit->log();
                
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

    public function updateRecipe(int $tenantId, int $recipeId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldRecipe = $this->recipeRepository->findById($tenantId, $recipeId);
            
            $data['tenant_id'] = $tenantId;
            $data['recipe_id'] = $recipeId;
            $recipe = new \Modules\Menu\Models\Recipe($data);
            
            $result = $this->recipeRepository->update($recipe);
            
            if ($result) {
                // Update ingredients if provided
                if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                    // Remove all existing ingredients
                    $existingIngredients = $this->recipeRepository->getIngredients($recipeId);
                    foreach ($existingIngredients as $existing) {
                        $this->recipeRepository->removeIngredient($recipeId, $existing['ingredient_id']);
                    }
                    
                    // Add new ingredients
                    foreach ($data['ingredients'] as $ingredient) {
                        $this->recipeRepository->addIngredient(
                            $recipeId,
                            $ingredient['ingredient_id'],
                            $ingredient['quantity'],
                            $ingredient['unit']
                        );
                    }
                }
                
                // $this->audit->log();
                
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

    public function deleteRecipe(int $tenantId, int $recipeId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldRecipe = $this->recipeRepository->findById($tenantId, $recipeId);
            
            $result = $this->recipeRepository->delete($tenantId, $recipeId);
            
            if ($result) {
                // $this->audit->log();
                
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
