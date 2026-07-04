<?php

if (!class_exists('DeliveryRepository')) {
    require_once __DIR__ . '/../Repositories/DeliveryRepository.php';
}


class DeliveryService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new DeliveryRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createDeliveryOrder($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['delivery_type']) || empty($data['customer_name']) || empty($data['delivery_address'])) {
                return [
                    'success' => false,
                    'message' => 'Delivery type, customer name, and address are required'
                ];
            }

            $deliveryData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'order_id' => $data['order_id'] ?? null,
                'delivery_type' => $data['delivery_type'],
                'external_order_id' => $data['external_order_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'delivery_address' => $data['delivery_address'],
                'delivery_lat' => $data['delivery_lat'] ?? null,
                'delivery_lng' => $data['delivery_lng'] ?? null,
                'estimated_distance_km' => $data['estimated_distance_km'] ?? 0,
                'estimated_time_minutes' => $data['estimated_time_minutes'] ?? 0,
                'delivery_fee' => $data['delivery_fee'] ?? 0,
                'status' => 'PENDING'
            ];

            $deliveryOrderId = $this->repository->create($deliveryData);

            return [
                'success' => true,
                'message' => 'Delivery order created successfully',
                'delivery_order_id' => $deliveryOrderId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create delivery order: ' . $e->getMessage()
            ];
        }
    }

    public function assignDriver($deliveryOrderId, $driverId, $tenantId)
    {
        try {
            $this->repository->update($deliveryOrderId, [
                'driver_id' => $driverId,
                'status' => 'ASSIGNED'
            ]);

            return [
                'success' => true,
                'message' => 'Driver assigned successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to assign driver: ' . $e->getMessage()
            ];
        }
    }

    public function updateStatus($deliveryOrderId, $status, $tenantId)
    {
        try {
            $updateData = ['status' => $status];
            
            if ($status === 'PICKED_UP') {
                $updateData['pickup_time'] = date('Y-m-d H:i:s');
            } elseif ($status === 'DELIVERED') {
                $updateData['delivery_time'] = date('Y-m-d H:i:s');
            }

            $this->repository->update($deliveryOrderId, $updateData);

            return [
                'success' => true,
                'message' => 'Status updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    public function getDeliveryOrders($tenantId, $branchId = null)
    {
        try {
            $orders = $this->repository->getByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Delivery orders retrieved successfully',
                'data' => $orders
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get delivery orders: ' . $e->getMessage()
            ];
        }
    }
}
