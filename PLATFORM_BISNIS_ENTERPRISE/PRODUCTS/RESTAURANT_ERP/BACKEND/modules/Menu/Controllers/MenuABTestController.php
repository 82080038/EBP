<?php

declare(strict_types=1);

namespace Modules\Menu\Controllers;

use Modules\Menu\Services\MenuABTestService;
use Response;

class MenuABTestController
{
    private MenuABTestService $abTestService;

    public function __construct()
    {
        $db = Database::getInstance()->connect();
        $this->abTestService = new MenuABTestService($db);
    }

    public function createABTest(array $request): void
    {
        try {
            $abTest = $this->abTestService->createABTest($request);
            Response::success($abTest->toArray(), 'A/B test created successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getABTest(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $abTest = $this->abTestService->getABTestById($id);
            
            if (!$abTest) {
                Response::notFound('A/B test not found');
                return;
            }

            Response::success($abTest->toArray());
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getABTests(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $branchId = isset($request['branch_id']) ? (int)$request['branch_id'] : null;
            
            $abTests = $this->abTestService->getABTestsByTenant($tenantId, $branchId);
            Response::success(array_map(fn($test) => $test->toArray(), $abTests));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getActiveABTests(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $abTests = $this->abTestService->getActiveABTests($tenantId);
            Response::success(array_map(fn($test) => $test->toArray(), $abTests));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateABTest(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $abTest = $this->abTestService->updateABTest($id, $request);
            
            if (!$abTest) {
                Response::notFound('A/B test not found');
                return;
            }

            Response::success($abTest->toArray(), 'A/B test updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function deleteABTest(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $deletedBy = (int)$request['deleted_by'];
            
            $result = $this->abTestService->deleteABTest($id, $deletedBy);
            
            if (!$result) {
                Response::notFound('A/B test not found');
                return;
            }

            Response::success(null, 'A/B test deleted successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function assignUserToVariant(array $request): void
    {
        try {
            $abTestId = (int)$request['ab_test_id'];
            $userId = isset($request['user_id']) ? (int)$request['user_id'] : null;
            $sessionId = $request['session_id'] ?? null;
            
            $variantId = $this->abTestService->assignUserToVariant($abTestId, $userId, $sessionId);
            Response::success(['variant_id' => $variantId], 'User assigned to variant');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function recordTestResult(array $request): void
    {
        try {
            $abTestId = (int)$request['ab_test_id'];
            $variantId = (int)$request['variant_id'];
            $metricName = $request['metric_name'];
            $metricValue = (float)$request['metric_value'];
            $sampleSize = (int)($request['sample_size'] ?? 1);
            
            $result = $this->abTestService->recordTestResult($abTestId, $variantId, $metricName, $metricValue, $sampleSize);
            
            if (!$result) {
                Response::error('Failed to record test result', 500);
                return;
            }

            Response::success(null, 'Test result recorded successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getTestResults(array $request): void
    {
        try {
            $abTestId = (int)$request['ab_test_id'];
            $results = $this->abTestService->getTestResults($abTestId);
            Response::success($results);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
