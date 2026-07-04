<?php

if (!class_exists('ReservationRepository')) {
    require_once __DIR__ . '/../Repositories/ReservationRepository.php';
}



class ReservationService
{
    private $reservationRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->reservationRepository = new ReservationRepository();
        $this->transaction = new Transaction();
        $this->audit = new Audit();
    }

    public function getAllReservations(int $tenantId, ?int $branchId = null): array
    {
        $reservations = $this->reservationRepository->findAll($tenantId, $branchId);
        return array_map(function($r) { return $r->toArray(); }, $reservations);
    }

    public function getReservationsByDate(int $tenantId, int $branchId, string $date): array
    {
        $reservations = $this->reservationRepository->findByDate($tenantId, $branchId, $date);
        return array_map(function($r) { return $r->toArray(); }, $reservations);
    }

    public function getReservation(int $tenantId, int $reservationId): ?array
    {
        $reservation = $this->reservationRepository->findById($tenantId, $reservationId);
        return $reservation ? $reservation->toArray() : null;
    }

    public function checkAvailability(int $tenantId, int $branchId, string $date, string $time, int $partySize): bool
    {
        return $this->reservationRepository->checkAvailability($tenantId, $branchId, $date, $time, $partySize);
    }

    public function createReservation(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            
            // Generate reservation number
            $data['reservation_number'] = $this->reservationRepository->generateReservationNumber(
                $tenantId,
                $data['branch_id']
            );
            
            $reservation = new \Modules\Reservation\Models\Reservation($data);
            
            // Check availability before creating
            $isAvailable = $this->reservationRepository->checkAvailability(
                $tenantId,
                $reservation->branch_id,
                $reservation->reservation_date,
                $reservation->reservation_time,
                $reservation->party_size
            );
            
            if (!$isAvailable) {
                $this->transaction->rollback();
                return false;
            }
            
            $result = $this->reservationRepository->create($reservation);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'RESERVATION',
                    'action' => 'CREATE_RESERVATION',
                    'record_id' => $this->transaction->getLastInsertId(),
                    'table_name' => 'reservations',
                    'new_values' => json_encode($data)
                ]);
                
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

    public function updateReservation(int $tenantId, int $reservationId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldReservation = $this->reservationRepository->findById($tenantId, $reservationId);
            
            $data['tenant_id'] = $tenantId;
            $data['reservation_id'] = $reservationId;
            $reservation = new \Modules\Reservation\Models\Reservation($data);
            
            // If date or time changed, check availability
            if (isset($data['reservation_date']) || isset($data['reservation_time'])) {
                $checkDate = $data['reservation_date'] ?? $oldReservation->reservation_date;
                $checkTime = $data['reservation_time'] ?? $oldReservation->reservation_time;
                
                $isAvailable = $this->reservationRepository->checkAvailability(
                    $tenantId,
                    $reservation->branch_id,
                    $checkDate,
                    $checkTime,
                    $reservation->party_size
                );
                
                if (!$isAvailable) {
                    $this->transaction->rollback();
                    return false;
                }
            }
            
            $result = $this->reservationRepository->update($reservation);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'RESERVATION',
                    'action' => 'UPDATE_RESERVATION',
                    'record_id' => $reservationId,
                    'table_name' => 'reservations',
                    'old_values' => json_encode($oldReservation->toArray()),
                    'new_values' => json_encode($data)
                ]);
                
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

    public function updateReservationStatus(int $tenantId, int $reservationId, string $status): bool
    {
        $this->transaction->begin();
        
        try {
            $oldReservation = $this->reservationRepository->findById($tenantId, $reservationId);
            
            $result = $this->reservationRepository->updateStatus($tenantId, $reservationId, $status);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'RESERVATION',
                    'action' => 'UPDATE_RESERVATION_STATUS',
                    'record_id' => $reservationId,
                    'table_name' => 'reservations',
                    'old_values' => json_encode(['status' => $oldReservation->status]),
                    'new_values' => json_encode(['status' => $status])
                ]);
                
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

    public function deleteReservation(int $tenantId, int $reservationId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldReservation = $this->reservationRepository->findById($tenantId, $reservationId);
            
            $result = $this->reservationRepository->delete($tenantId, $reservationId);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'RESERVATION',
                    'action' => 'DELETE_RESERVATION',
                    'record_id' => $reservationId,
                    'table_name' => 'reservations',
                    'old_values' => json_encode($oldReservation->toArray())
                ]);
                
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
