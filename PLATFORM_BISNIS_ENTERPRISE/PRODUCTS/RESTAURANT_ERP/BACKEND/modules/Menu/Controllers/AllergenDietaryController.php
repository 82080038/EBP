<?php

declare(strict_types=1);

namespace Modules\Menu\Controllers;

use Modules\Menu\Services\AllergenDietaryService;
use Response;

class AllergenDietaryController
{
    private AllergenDietaryService $allergenDietaryService;

    public function __construct()
    {
        $db = Database::getInstance()->connect();
        $this->allergenDietaryService = new AllergenDietaryService($db);
    }

    public function addProductAllergen(array $request): void
    {
        try {
            $allergen = $this->allergenDietaryService->addProductAllergen($request);
            Response::success($allergen->toArray(), 'Product allergen added successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getProductAllergens(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $productId = (int)$request['product_id'];
            
            $allergens = $this->allergenDietaryService->getProductAllergens($tenantId, $productId);
            Response::success($allergens);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateProductAllergen(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $result = $this->allergenDietaryService->updateProductAllergen($id, $request);
            
            if (!$result) {
                Response::notFound('Product allergen not found');
                return;
            }

            Response::success(null, 'Product allergen updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function removeProductAllergen(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $result = $this->allergenDietaryService->removeProductAllergen($id);
            
            if (!$result) {
                Response::notFound('Product allergen not found');
                return;
            }

            Response::success(null, 'Product allergen removed successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function addProductDietaryInfo(array $request): void
    {
        try {
            $result = $this->allergenDietaryService->addProductDietaryInfo($request);
            
            if (!$result) {
                Response::error('Failed to add product dietary info', 500);
                return;
            }

            Response::success(null, 'Product dietary info added successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getProductDietaryInfo(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $productId = (int)$request['product_id'];
            
            $dietaryInfo = $this->allergenDietaryService->getProductDietaryInfo($tenantId, $productId);
            Response::success($dietaryInfo);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateProductDietaryInfo(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $result = $this->allergenDietaryService->updateProductDietaryInfo($id, $request);
            
            if (!$result) {
                Response::notFound('Product dietary info not found');
                return;
            }

            Response::success(null, 'Product dietary info updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function removeProductDietaryInfo(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $result = $this->allergenDietaryService->removeProductDietaryInfo($id);
            
            if (!$result) {
                Response::notFound('Product dietary info not found');
                return;
            }

            Response::success(null, 'Product dietary info removed successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function addCustomerDietaryPreference(array $request): void
    {
        try {
            $result = $this->allergenDietaryService->addCustomerDietaryPreference($request);
            
            if (!$result) {
                Response::error('Failed to add customer dietary preference', 500);
                return;
            }

            Response::success(null, 'Customer dietary preference added successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getCustomerDietaryPreferences(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $customerId = (int)$request['customer_id'];
            
            $preferences = $this->allergenDietaryService->getCustomerDietaryPreferences($tenantId, $customerId);
            Response::success($preferences);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function checkProductCompatibility(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $productId = (int)$request['product_id'];
            $customerId = (int)$request['customer_id'];
            
            $compatibility = $this->allergenDietaryService->checkProductCompatibility($tenantId, $productId, $customerId);
            Response::success($compatibility);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getAllergenTypes(array $request): void
    {
        try {
            $allergenTypes = $this->allergenDietaryService->getAllergenTypes();
            Response::success($allergenTypes);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getDietaryRestrictions(array $request): void
    {
        try {
            $dietaryRestrictions = $this->allergenDietaryService->getDietaryRestrictions();
            Response::success($dietaryRestrictions);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
