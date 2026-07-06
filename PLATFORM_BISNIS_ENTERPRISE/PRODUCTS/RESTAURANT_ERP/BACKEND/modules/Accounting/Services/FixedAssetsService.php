<?php

if (!class_exists('FixedAssetsRepository')) {
    require_once __DIR__ . '/../Repositories/FixedAssetsRepository.php';
}

class FixedAssetsService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new FixedAssetsRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createAsset($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['asset_name']) || empty($data['purchase_date']) || empty($data['purchase_cost']) || empty($data['useful_life'])) {
                return [
                    'success' => false,
                    'message' => 'Asset name, purchase date, purchase cost, and useful life are required'
                ];
            }

            $assetCode = 'AST-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $assetData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'asset_code' => $assetCode,
                'asset_name' => $data['asset_name'],
                'asset_category' => $data['asset_category'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'purchase_cost' => $data['purchase_cost'],
                'salvage_value' => $data['salvage_value'] ?? 0,
                'useful_life' => $data['useful_life'],
                'depreciation_method' => $data['depreciation_method'] ?? 'STRAIGHT_LINE',
                'current_value' => $data['purchase_cost'],
                'accumulated_depreciation' => 0,
                'location' => $data['location'] ?? null,
                'status' => 'ACTIVE',
                'notes' => $data['notes'] ?? null
            ];

            $assetId = $this->repository->createAsset($assetData);

            return [
                'success' => true,
                'message' => 'Fixed asset created successfully',
                'asset_id' => $assetId,
                'asset_code' => $assetCode
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create fixed asset: ' . $e->getMessage()
            ];
        }
    }

    public function getAssets($tenantId, $branchId, $status = null, $category = null)
    {
        try {
            $assets = $this->repository->getAssets($tenantId, $branchId, $status, $category);
            
            return [
                'success' => true,
                'message' => 'Fixed assets retrieved successfully',
                'data' => $assets
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get fixed assets: ' . $e->getMessage()
            ];
        }
    }

    public function getAsset($tenantId, $branchId, $assetId)
    {
        try {
            $asset = $this->repository->getAsset($tenantId, $branchId, $assetId);
            
            if (!$asset) {
                return [
                    'success' => false,
                    'message' => 'Fixed asset not found'
                ];
            }

            // Get depreciation schedule
            $depreciationSchedule = $this->repository->getDepreciationSchedule($assetId);
            $asset['depreciation_schedule'] = $depreciationSchedule;

            return [
                'success' => true,
                'message' => 'Fixed asset retrieved successfully',
                'data' => $asset
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get fixed asset: ' . $e->getMessage()
            ];
        }
    }

    public function calculateDepreciation($tenantId, $branchId, $assetId, $fiscalYear, $fiscalMonth, $userId)
    {
        try {
            $asset = $this->repository->getAsset($tenantId, $branchId, $assetId);
            
            if (!$asset) {
                return [
                    'success' => false,
                    'message' => 'Fixed asset not found'
                ];
            }

            if ($asset['status'] !== 'ACTIVE') {
                return [
                    'success' => false,
                    'message' => 'Cannot calculate depreciation for inactive asset'
                ];
            }

            // Check if depreciation already calculated for this period
            $existingDepreciation = $this->repository->getDepreciationForPeriod($assetId, $fiscalYear, $fiscalMonth);
            if ($existingDepreciation) {
                return [
                    'success' => false,
                    'message' => 'Depreciation already calculated for this period'
                ];
            }

            // Calculate depreciation based on method
            $depreciationAmount = $this->calculateDepreciationAmount($asset, $fiscalYear, $fiscalMonth);

            // Update accumulated depreciation and current value
            $newAccumulatedDepreciation = $asset['accumulated_depreciation'] + $depreciationAmount;
            $newCurrentValue = $asset['current_value'] - $depreciationAmount;

            // Create depreciation record
            $depreciationData = [
                'asset_id' => $assetId,
                'fiscal_year' => $fiscalYear,
                'fiscal_month' => $fiscalMonth,
                'depreciation_amount' => $depreciationAmount,
                'accumulated_depreciation' => $newAccumulatedDepreciation,
                'book_value' => $newCurrentValue
            ];

            $depreciationId = $this->repository->createDepreciation($depreciationData);

            // Update asset
            $this->repository->updateAsset($assetId, [
                'accumulated_depreciation' => $newAccumulatedDepreciation,
                'current_value' => $newCurrentValue
            ]);

            return [
                'success' => true,
                'message' => 'Depreciation calculated successfully',
                'data' => [
                    'depreciation_id' => $depreciationId,
                    'depreciation_amount' => $depreciationAmount,
                    'accumulated_depreciation' => $newAccumulatedDepreciation,
                    'book_value' => $newCurrentValue
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate depreciation: ' . $e->getMessage()
            ];
        }
    }

    public function getDepreciationSchedule($tenantId, $branchId, $assetId)
    {
        try {
            $schedule = $this->repository->getDepreciationSchedule($assetId);
            
            return [
                'success' => true,
                'message' => 'Depreciation schedule retrieved successfully',
                'data' => $schedule
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get depreciation schedule: ' . $e->getMessage()
            ];
        }
    }

    public function disposeAsset($tenantId, $branchId, $assetId, $disposalType, $disposalValue, $userId)
    {
        try {
            $asset = $this->repository->getAsset($tenantId, $branchId, $assetId);
            
            if (!$asset) {
                return [
                    'success' => false,
                    'message' => 'Fixed asset not found'
                ];
            }

            // Calculate gain/loss on disposal
            $bookValue = $asset['current_value'];
            $gainLoss = $disposalValue - $bookValue;

            // Update asset status
            $this->repository->updateAsset($assetId, [
                'status' => $disposalType,
                'current_value' => $disposalValue
            ]);

            return [
                'success' => true,
                'message' => 'Asset disposed successfully',
                'data' => [
                    'asset_id' => $assetId,
                    'disposal_type' => $disposalType,
                    'disposal_value' => $disposalValue,
                    'book_value' => $bookValue,
                    'gain_loss' => $gainLoss
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to dispose asset: ' . $e->getMessage()
            ];
        }
    }

    private function calculateDepreciationAmount($asset, $fiscalYear, $fiscalMonth)
    {
        $method = $asset['depreciation_method'];
        $cost = $asset['purchase_cost'];
        $salvageValue = $asset['salvage_value'];
        $usefulLife = $asset['useful_life'];

        switch ($method) {
            case 'STRAIGHT_LINE':
                // Annual depreciation
                $annualDepreciation = ($cost - $salvageValue) / $usefulLife;
                // Monthly depreciation
                return $annualDepreciation / 12;

            case 'DECLINING_BALANCE':
                // Double declining balance
                $rate = 2 / $usefulLife;
                $bookValue = $asset['current_value'];
                return ($bookValue * $rate) / 12;

            case 'UNITS_OF_PRODUCTION':
                // Would need production data - for now use straight line
                $annualDepreciation = ($cost - $salvageValue) / $usefulLife;
                return $annualDepreciation / 12;

            default:
                $annualDepreciation = ($cost - $salvageValue) / $usefulLife;
                return $annualDepreciation / 12;
        }
    }
}
