<?php

if (!class_exists('LocationRepository')) {
    require_once __DIR__ . '/../Repositories/LocationRepository.php';
}


class LocationService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new LocationRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Find nearby branches within a given radius
     */
    public function findNearbyBranches($latitude, $longitude, $radiusKm = 10)
    {
        try {
            $branches = $this->repository->getAllBranches();
            $nearbyBranches = [];

            foreach ($branches as $branch) {
                if ($branch['latitude'] && $branch['longitude']) {
                    $distance = $this->calculateDistance(
                        $latitude,
                        $longitude,
                        $branch['latitude'],
                        $branch['longitude']
                    );

                    if ($distance <= $radiusKm) {
                        $branch['distance_km'] = round($distance, 2);
                        $branch['is_within_delivery_radius'] = $distance <= ($branch['delivery_radius_km'] ?? 5);
                        $nearbyBranches[] = $branch;
                    }
                }
            }

            // Sort by distance
            usort($nearbyBranches, function($a, $b) {
                return $a['distance_km'] <=> $b['distance_km'];
            });

            return [
                'success' => true,
                'data' => $nearbyBranches,
                'count' => count($nearbyBranches)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to find nearby branches: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if customer is within delivery radius of a branch
     */
    public function checkDeliveryAvailability($branchId, $customerLat, $customerLon)
    {
        try {
            $branch = $this->repository->getBranchById($branchId);
            
            if (!$branch || !$branch['latitude'] || !$branch['longitude']) {
                return [
                    'success' => false,
                    'message' => 'Branch location not found'
                ];
            }

            $distance = $this->calculateDistance(
                $customerLat,
                $customerLon,
                $branch['latitude'],
                $branch['longitude']
            );

            $deliveryRadius = $branch['delivery_radius_km'] ?? 5;
            $isAvailable = $distance <= $deliveryRadius;

            return [
                'success' => true,
                'available' => $isAvailable,
                'distance_km' => round($distance, 2),
                'delivery_radius_km' => $deliveryRadius,
                'message' => $isAvailable 
                    ? 'Delivery available' 
                    : 'Location outside delivery radius'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to check delivery availability: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update branch location
     */
    public function updateBranchLocation($branchId, $latitude, $longitude, $deliveryRadius, $tenantId)
    {
        try {
            // Check if branch belongs to tenant
            $stmt = $this->db->prepare("SELECT branch_id FROM branches WHERE branch_id = ? AND tenant_id = ?");
            $stmt->execute([$branchId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Branch not found or does not belong to tenant'
                ];
            }

            $this->repository->updateLocation($branchId, $latitude, $longitude, $deliveryRadius);
            
            return [
                'success' => true,
                'message' => 'Branch location updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update branch location: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get branch location
     */
    public function getBranchLocation($branchId, $tenantId)
    {
        try {
            $branch = $this->repository->getBranchById($branchId);
            
            if (!$branch) {
                return [
                    'success' => false,
                    'message' => 'Branch not found'
                ];
            }

            // Check if branch belongs to tenant
            $stmt = $this->db->prepare("SELECT branch_id FROM branches WHERE branch_id = ? AND tenant_id = ?");
            $stmt->execute([$branchId, $tenantId]);
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Branch does not belong to tenant'
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'branch_id' => $branch['branch_id'],
                    'branch_name' => $branch['branch_name'],
                    'address' => $branch['address'],
                    'latitude' => $branch['latitude'],
                    'longitude' => $branch['longitude'],
                    'delivery_radius_km' => $branch['delivery_radius_km']
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get branch location: ' . $e->getMessage()
            ];
        }
    }
}
