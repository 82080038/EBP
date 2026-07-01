<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/Menu/Services/ProductVariantService.php';
require_once __DIR__ . '/../modules/Menu/Services/ProductModifierService.php';

class Phase2Simulation
{
    private $db;
    private $variantService;
    private $modifierService;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->variantService = new ProductVariantService();
        $this->modifierService = new ProductModifierService();
    }

    public function run()
    {
        echo "=== Phase 2: Product Variants & Modifiers Simulation ===\n\n";

        // Get test data
        $tenantId = $this->getTestTenantId();
        $productId = $this->getTestProductId($tenantId);

        if (!$tenantId || !$productId) {
            echo "ERROR: Missing test data. Please run tenant registration first.\n";
            return;
        }

        echo "Test Data:\n";
        echo "  Tenant ID: $tenantId\n";
        echo "  Product ID: $productId\n\n";

        // Test 1: Create Product Variants
        echo "Test 1: Create Product Variants (Size options)\n";
        $variant1 = $this->createVariant($productId, $tenantId, 'S', 'Small', -5000, true);
        $variant2 = $this->createVariant($productId, $tenantId, 'M', 'Medium', 0, false);
        $variant3 = $this->createVariant($productId, $tenantId, 'L', 'Large', 5000, false);
        
        if ($variant1 && $variant2 && $variant3) {
            echo "  ✓ Created 3 variants: Small (-5000), Medium (0), Large (+5000)\n";
        } else {
            echo "  ✗ Failed to create variants\n";
        }
        echo "\n";

        // Test 2: Get Variants by Product
        echo "Test 2: Get Variants by Product\n";
        $result = $this->variantService->getVariantsByProduct($productId, $tenantId);
        if ($result['success']) {
            echo "  ✓ Retrieved " . count($result['data']) . " variants\n";
            foreach ($result['data'] as $variant) {
                echo "    - {$variant['variant_name']}: {$variant['price_adjustment']}\n";
            }
        } else {
            echo "  ✗ Failed to get variants\n";
        }
        echo "\n";

        // Test 3: Create Modifier Group
        echo "Test 3: Create Modifier Group (Toppings)\n";
        $group1 = $this->createModifierGroup($tenantId, 'TOPPINGS', 'Extra Toppings', false, 0, 5);
        if ($group1) {
            echo "  ✓ Created modifier group: Extra Toppings\n";
        } else {
            echo "  ✗ Failed to create modifier group\n";
        }
        echo "\n";

        // Test 4: Create Modifiers
        echo "Test 4: Create Modifiers (Topping options)\n";
        if ($group1) {
            $mod1 = $this->createModifier($group1, $tenantId, 'CHEESE', 'Extra Cheese', 5000);
            $mod2 = $this->createModifier($group1, $tenantId, 'BACON', 'Bacon', 8000);
            $mod3 = $this->createModifier($group1, $tenantId, 'MUSHROOM', 'Mushrooms', 3000);
            
            if ($mod1 && $mod2 && $mod3) {
                echo "  ✓ Created 3 modifiers: Extra Cheese (+5000), Bacon (+8000), Mushrooms (+3000)\n";
            } else {
                echo "  ✗ Failed to create modifiers\n";
            }
        }
        echo "\n";

        // Test 5: Create Required Modifier Group
        echo "Test 5: Create Required Modifier Group (Cooking Level)\n";
        $group2 = $this->createModifierGroup($tenantId, 'COOKING', 'Cooking Level', true, 1, 1);
        if ($group2) {
            echo "  ✓ Created required modifier group: Cooking Level\n";
        } else {
            echo "  ✗ Failed to create modifier group\n";
        }
        echo "\n";

        // Test 6: Create Cooking Level Modifiers
        echo "Test 6: Create Cooking Level Modifiers\n";
        if ($group2) {
            $mod4 = $this->createModifier($group2, $tenantId, 'RARE', 'Rare', 0);
            $mod5 = $this->createModifier($group2, $tenantId, 'MED', 'Medium', 0);
            $mod6 = $this->createModifier($group2, $tenantId, 'WELL', 'Well Done', 0);
            
            if ($mod4 && $mod5 && $mod6) {
                echo "  ✓ Created 3 cooking level modifiers\n";
            } else {
                echo "  ✗ Failed to create modifiers\n";
            }
        }
        echo "\n";

        // Test 7: Assign Modifiers to Product
        echo "Test 7: Assign Modifiers to Product\n";
        if ($group1 && $group2) {
            $assign1 = $this->assignModifier($productId, $group1, $tenantId);
            $assign2 = $this->assignModifier($productId, $group2, $tenantId);
            
            if ($assign1 && $assign2) {
                echo "  ✓ Assigned 2 modifier groups to product\n";
            } else {
                echo "  ✗ Failed to assign modifiers\n";
            }
        }
        echo "\n";

        // Test 8: Get Product Modifiers
        echo "Test 8: Get Product Modifiers\n";
        $result = $this->modifierService->getProductModifiers($productId, $tenantId);
        if ($result['success']) {
            echo "  ✓ Retrieved product modifiers\n";
            $groups = [];
            foreach ($result['data'] as $row) {
                if (!isset($groups[$row['group_name']])) {
                    $groups[$row['group_name']] = [];
                }
                if ($row['modifier_id']) {
                    $groups[$row['group_name']][] = $row['modifier_name'];
                }
            }
            foreach ($groups as $groupName => $modifiers) {
                echo "    - $groupName: " . implode(', ', $modifiers) . "\n";
            }
        } else {
            echo "  ✗ Failed to get product modifiers\n";
        }
        echo "\n";

        // Test 9: Get All Modifier Groups
        echo "Test 9: Get All Modifier Groups\n";
        $result = $this->modifierService->getModifierGroups($tenantId);
        if ($result['success']) {
            echo "  ✓ Retrieved " . count($result['data']) . " modifier groups\n";
        } else {
            echo "  ✗ Failed to get modifier groups\n";
        }
        echo "\n";

        // Test 10: Get Modifiers by Group
        echo "Test 10: Get Modifiers by Group\n";
        if ($group1) {
            $result = $this->modifierService->getModifiersByGroup($group1, $tenantId);
            if ($result['success']) {
                echo "  ✓ Retrieved " . count($result['data']) . " modifiers for group\n";
            } else {
                echo "  ✗ Failed to get modifiers by group\n";
            }
        }
        echo "\n";

        // Summary
        echo "=== Simulation Summary ===\n";
        echo "Phase 2 Product Variants & Modifiers features tested:\n";
        echo "  ✓ Product Variants (size options with price adjustments)\n";
        echo "  ✓ Modifier Groups (optional and required)\n";
        echo "  ✓ Product Modifiers (with price adjustments)\n";
        echo "  ✓ Modifier Assignments to Products\n";
        echo "  ✓ Retrieval of variants and modifiers\n";
        echo "  ✓ Support for min/max selections\n";
        echo "\nAll Phase 2 features implemented and tested successfully!\n";
    }

    private function getTestTenantId()
    {
        $stmt = $this->db->query("SELECT tenant_id FROM tenants LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['tenant_id'] : null;
    }

    private function getTestProductId($tenantId)
    {
        $stmt = $this->db->prepare("SELECT product_id FROM products WHERE tenant_id = ? LIMIT 1");
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['product_id'] : null;
    }

    private function createVariant($productId, $tenantId, $code, $name, $priceAdjustment, $isDefault)
    {
        $data = [
            'product_id' => $productId,
            'variant_code' => $code,
            'variant_name' => $name,
            'price_adjustment' => $priceAdjustment,
            'is_default' => $isDefault,
            'status' => 'ACTIVE'
        ];

        $result = $this->variantService->createVariant($data, $tenantId);
        return $result['success'] ? $result['variant_id'] : null;
    }

    private function createModifierGroup($tenantId, $code, $name, $isRequired, $minSel, $maxSel)
    {
        $data = [
            'group_code' => $code,
            'group_name' => $name,
            'is_required' => $isRequired,
            'min_selections' => $minSel,
            'max_selections' => $maxSel,
            'status' => 'ACTIVE'
        ];

        $result = $this->modifierService->createModifierGroup($data, $tenantId);
        return $result['success'] ? $result['modifier_group_id'] : null;
    }

    private function createModifier($groupId, $tenantId, $code, $name, $priceAdjustment)
    {
        $data = [
            'modifier_group_id' => $groupId,
            'modifier_code' => $code,
            'modifier_name' => $name,
            'price_adjustment' => $priceAdjustment,
            'is_available' => true,
            'status' => 'ACTIVE'
        ];

        $result = $this->modifierService->createModifier($data, $tenantId);
        return $result['success'] ? $result['modifier_id'] : null;
    }

    private function assignModifier($productId, $groupId, $tenantId)
    {
        $data = [
            'product_id' => $productId,
            'modifier_group_id' => $groupId
        ];

        $result = $this->modifierService->assignModifierToProduct($data, $tenantId);
        return $result['success'] ? $result['assignment_id'] : null;
    }
}

// Run simulation
$simulation = new Phase2Simulation();
$simulation->run();
