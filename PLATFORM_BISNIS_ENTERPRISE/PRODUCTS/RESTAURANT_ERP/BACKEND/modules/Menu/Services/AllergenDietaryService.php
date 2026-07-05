<?php

declare(strict_types=1);

namespace Modules\Menu\Services;

use Modules\Menu\Models\ProductAllergen;
use PDO;

class AllergenDietaryService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function addProductAllergen(array $data): ProductAllergen
    {
        $sql = "INSERT INTO product_allergens 
                (tenant_id, product_id, allergen_id, contains, cross_contamination_risk, notes, created_by) 
                VALUES 
                (:tenant_id, :product_id, :allergen_id, :contains, :cross_contamination_risk, :notes, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':product_id' => $data['product_id'],
            ':allergen_id' => $data['allergen_id'],
            ':contains' => $data['contains'] ?? true,
            ':cross_contamination_risk' => $data['cross_contamination_risk'] ?? false,
            ':notes' => $data['notes'] ?? null,
            ':created_by' => $data['created_by']
        ]);

        $data['id'] = (int)$this->db->lastInsertId();
        return new ProductAllergen($data);
    }

    public function getProductAllergens(int $tenantId, int $productId): array
    {
        $sql = "SELECT pa.*, a.name as allergen_name, a.description as allergen_description, a.severity 
                FROM product_allergens pa
                JOIN allergen_types a ON pa.allergen_id = a.id
                WHERE pa.tenant_id = :tenant_id AND pa.product_id = :product_id
                ORDER BY a.severity DESC, a.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':product_id' => $productId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProductAllergen(int $id, array $data): bool
    {
        $sql = "UPDATE product_allergens 
                SET contains = :contains, 
                    cross_contamination_risk = :cross_contamination_risk, 
                    notes = :notes, 
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':contains' => $data['contains'],
            ':cross_contamination_risk' => $data['cross_contamination_risk'],
            ':notes' => $data['notes'] ?? null,
            ':updated_by' => $data['updated_by']
        ]);
    }

    public function removeProductAllergen(int $id): bool
    {
        $sql = "DELETE FROM product_allergens WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function addProductDietaryInfo(array $data): bool
    {
        $sql = "INSERT INTO product_dietary_info 
                (tenant_id, product_id, dietary_restriction_id, is_compliant, certification_url, notes, created_by) 
                VALUES 
                (:tenant_id, :product_id, :dietary_restriction_id, :is_compliant, :certification_url, :notes, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':product_id' => $data['product_id'],
            ':dietary_restriction_id' => $data['dietary_restriction_id'],
            ':is_compliant' => $data['is_compliant'] ?? false,
            ':certification_url' => $data['certification_url'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':created_by' => $data['created_by']
        ]);
    }

    public function getProductDietaryInfo(int $tenantId, int $productId): array
    {
        $sql = "SELECT pdi.*, dr.name as dietary_name, dr.code as dietary_code, dr.description as dietary_description, dr.category 
                FROM product_dietary_info pdi
                JOIN dietary_restrictions dr ON pdi.dietary_restriction_id = dr.id
                WHERE pdi.tenant_id = :tenant_id AND pdi.product_id = :product_id
                ORDER BY dr.category, dr.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':product_id' => $productId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProductDietaryInfo(int $id, array $data): bool
    {
        $sql = "UPDATE product_dietary_info 
                SET is_compliant = :is_compliant, 
                    certification_url = :certification_url, 
                    notes = :notes, 
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':is_compliant' => $data['is_compliant'],
            ':certification_url' => $data['certification_url'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':updated_by' => $data['updated_by']
        ]);
    }

    public function removeProductDietaryInfo(int $id): bool
    {
        $sql = "DELETE FROM product_dietary_info WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function addCustomerDietaryPreference(array $data): bool
    {
        $sql = "INSERT INTO customer_dietary_preferences 
                (tenant_id, customer_id, allergen_id, dietary_restriction_id, preference_type, severity, notes) 
                VALUES 
                (:tenant_id, :customer_id, :allergen_id, :dietary_restriction_id, :preference_type, :severity, :notes)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':customer_id' => $data['customer_id'],
            ':allergen_id' => $data['allergen_id'] ?? null,
            ':dietary_restriction_id' => $data['dietary_restriction_id'] ?? null,
            ':preference_type' => $data['preference_type'],
            ':severity' => $data['severity'] ?? 'MODERATE',
            ':notes' => $data['notes'] ?? null
        ]);
    }

    public function getCustomerDietaryPreferences(int $tenantId, int $customerId): array
    {
        $sql = "SELECT cdp.*, 
                        COALESCE(a.name, dr.name) as restriction_name,
                        COALESCE(a.description, dr.description) as restriction_description,
                        COALESCE(a.severity, 'MODERATE') as severity_level
                FROM customer_dietary_preferences cdp
                LEFT JOIN allergen_types a ON cdp.allergen_id = a.id
                LEFT JOIN dietary_restrictions dr ON cdp.dietary_restriction_id = dr.id
                WHERE cdp.tenant_id = :tenant_id AND cdp.customer_id = :customer_id
                ORDER BY cdp.severity DESC, cdp.preference_type";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':customer_id' => $customerId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkProductCompatibility(int $tenantId, int $productId, int $customerId): array
    {
        $customerPreferences = $this->getCustomerDietaryPreferences($tenantId, $customerId);
        $productAllergens = $this->getProductAllergens($tenantId, $productId);
        $productDietary = $this->getProductDietaryInfo($tenantId, $productId);

        $incompatibilities = [];
        $warnings = [];

        foreach ($customerPreferences as $preference) {
            // Check allergens
            if ($preference['allergen_id']) {
                foreach ($productAllergens as $allergen) {
                    if ($allergen['allergen_id'] == $preference['allergen_id']) {
                        if ($allergen['contains']) {
                            $incompatibilities[] = [
                                'type' => 'ALLERGY',
                                'severity' => $preference['severity_level'],
                                'allergen' => $allergen['allergen_name'],
                                'message' => "Product contains allergen: {$allergen['allergen_name']}"
                            ];
                        } elseif ($allergen['cross_contamination_risk']) {
                            $warnings[] = [
                                'type' => 'CROSS_CONTAMINATION',
                                'severity' => $preference['severity_level'],
                                'allergen' => $allergen['allergen_name'],
                                'message' => "Product may have cross-contamination with: {$allergen['allergen_name']}"
                            ];
                        }
                    }
                }
            }

            // Check dietary restrictions
            if ($preference['dietary_restriction_id']) {
                foreach ($productDietary as $dietary) {
                    if ($dietary['dietary_restriction_id'] == $preference['dietary_restriction_id']) {
                        if (!$dietary['is_compliant']) {
                            $incompatibilities[] = [
                                'type' => 'DIETARY_RESTRICTION',
                                'severity' => 'MODERATE',
                                'restriction' => $dietary['dietary_name'],
                                'message' => "Product is not compliant with: {$dietary['dietary_name']}"
                            ];
                        }
                    }
                }
            }
        }

        return [
            'is_compatible' => empty($incompatibilities),
            'incompatibilities' => $incompatibilities,
            'warnings' => $warnings
        ];
    }

    public function getAllergenTypes(): array
    {
        $sql = "SELECT * FROM allergen_types ORDER BY severity DESC, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDietaryRestrictions(): array
    {
        $sql = "SELECT * FROM dietary_restrictions ORDER BY category, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
