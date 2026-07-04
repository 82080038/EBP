<?php

namespace App\Modules\Franchise\Services;

use App\Modules\Franchise\Models\Franchisee;
use App\Modules\Franchise\Models\FranchiseAgreement;
use App\Modules\Franchise\Models\FranchisePerformance;
use App\Modules\Franchise\Models\FranchiseRoyalty;
use App\Core\Database;

class FranchiseService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get franchisees
     */
    public function getFranchisees($restaurantId, $status)
    {
        $franchiseeModel = new Franchisee();
        return $franchiseeModel->getByRestaurant($restaurantId, $status);
    }

    /**
     * Create franchisee
     */
    public function createFranchisee($restaurantId, $data)
    {
        $franchiseeModel = new Franchisee();
        
        $franchiseeData = [
            'restaurant_id' => $restaurantId,
            'franchisee_name' => $data->franchisee_name,
            'contact_person' => $data->contact_person,
            'email' => $data->email,
            'phone' => $data->phone,
            'address' => $data->address,
            'city' => $data->city,
            'state' => $data->state ?? null,
            'country' => $data->country,
            'postal_code' => $data->postal_code,
            'business_name' => $data->business_name,
            'tax_id' => $data->tax_id ?? null,
            'business_license' => $data->business_license ?? null,
            'franchisee_status' => 'prospect',
            'notes' => $data->notes ?? null,
            'assigned_manager' => $data->assigned_manager ?? null
        ];
        
        $franchiseeId = $franchiseeModel->create($franchiseeData);
        
        if (!$franchiseeId) {
            return ['success' => false, 'message' => 'Failed to create franchisee'];
        }
        
        return ['success' => true, 'message' => 'Franchisee created', 'franchisee_id' => $franchiseeId];
    }

    /**
     * Get agreements
     */
    public function getAgreements($restaurantId, $franchiseeId, $status)
    {
        $agreementModel = new FranchiseAgreement();
        return $agreementModel->getByRestaurant($restaurantId, $franchiseeId, $status);
    }

    /**
     * Create agreement
     */
    public function createAgreement($restaurantId, $userId, $data)
    {
        $agreementModel = new FranchiseAgreement();
        
        $agreementData = [
            'restaurant_id' => $restaurantId,
            'franchisee_id' => $data->franchisee_id,
            'agreement_type' => $data->agreement_type,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'territory_description' => $data->territory_description ?? null,
            'territory_exclusive' => $data->territory_exclusive ?? false,
            'franchise_fee' => $data->franchise_fee,
            'royalty_rate' => $data->royalty_rate ?? 0.00,
            'marketing_fee_rate' => $data->marketing_fee_rate ?? 0.00,
            'agreement_terms' => $data->agreement_terms,
            'agreement_document_url' => $data->agreement_document_url ?? null,
            'created_by' => $userId
        ];
        
        $agreementId = $agreementModel->create($agreementData);
        
        if (!$agreementId) {
            return ['success' => false, 'message' => 'Failed to create agreement'];
        }
        
        return ['success' => true, 'message' => 'Agreement created', 'agreement_id' => $agreementId];
    }

    /**
     * Get performance
     */
    public function getPerformance($restaurantId, $franchiseeId)
    {
        $performanceModel = new FranchisePerformance();
        return $performanceModel->getByRestaurant($restaurantId, $franchiseeId);
    }

    /**
     * Get royalties
     */
    public function getRoyalties($restaurantId, $franchiseeId, $status)
    {
        $royaltyModel = new FranchiseRoyalty();
        return $royaltyModel->getByRestaurant($restaurantId, $franchiseeId, $status);
    }

    /**
     * Create royalty
     */
    public function createRoyalty($restaurantId, $data)
    {
        $royaltyModel = new FranchiseRoyalty();
        
        $royaltyAmount = $data->gross_revenue * ($data->royalty_rate / 100);
        $marketingFee = $data->gross_revenue * ($data->marketing_fee_rate / 100);
        $totalDue = $royaltyAmount + $marketingFee;
        
        $royaltyData = [
            'restaurant_id' => $restaurantId,
            'franchisee_id' => $data->franchisee_id,
            'agreement_id' => $data->agreement_id,
            'royalty_period_start' => $data->royalty_period_start,
            'royalty_period_end' => $data->royalty_period_end,
            'gross_revenue' => $data->gross_revenue,
            'royalty_amount' => $royaltyAmount,
            'marketing_fee_amount' => $marketingFee,
            'total_due' => $totalDue,
            'payment_status' => 'pending',
            'notes' => $data->notes ?? null
        ];
        
        $royaltyId = $royaltyModel->create($royaltyData);
        
        if (!$royaltyId) {
            return ['success' => false, 'message' => 'Failed to create royalty record'];
        }
        
        return ['success' => true, 'message' => 'Royalty record created', 'royalty_id' => $royaltyId];
    }

    /**
     * Get support tickets
     */
    public function getSupportTickets($restaurantId, $franchiseeId, $status)
    {
        $sql = "SELECT fst.*, f.franchisee_name, u1.username as created_by_name, u2.username as assigned_to_name 
                FROM franchise_support_tickets fst
                LEFT JOIN franchisees f ON fst.franchisee_id = f.id
                LEFT JOIN users u1 ON fst.created_by = u1.id
                LEFT JOIN users u2 ON fst.assigned_to = u2.id
                WHERE fst.restaurant_id = ?";
        $params = [$restaurantId];
        
        if ($franchiseeId) {
            $sql .= " AND fst.franchisee_id = ?";
            $params[] = $franchiseeId;
        }
        
        if ($status) {
            $sql .= " AND fst.ticket_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY fst.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Create support ticket
     */
    public function createSupportTicket($restaurantId, $userId, $data)
    {
        $sql = "INSERT INTO franchise_support_tickets (restaurant_id, franchisee_id, subject, description, category, priority, ticket_status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, 'open', ?)";
        
        $inserted = $this->db->query($sql, [
            $restaurantId,
            $data->franchisee_id,
            $data->subject,
            $data->description,
            $data->category,
            $data->priority ?? 'medium',
            $userId
        ]);
        
        if (!$inserted) {
            return ['success' => false, 'message' => 'Failed to create support ticket'];
        }
        
        return ['success' => true, 'message' => 'Support ticket created'];
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $franchiseeModel = new Franchisee();
        $agreementModel = new FranchiseAgreement();
        $royaltyModel = new FranchiseRoyalty();
        
        // Total franchisees
        $totalFranchisees = $franchiseeModel->countByRestaurant($restaurantId);
        
        // Active franchisees
        $activeFranchisees = $franchiseeModel->countByStatus($restaurantId, 'active');
        
        // Active agreements
        $activeAgreements = $agreementModel->countByStatus($restaurantId, 'active');
        
        // Pending royalties
        $pendingRoyalties = $royaltyModel->countByStatus($restaurantId, 'pending');
        
        // Total due
        $totalDue = $royaltyModel->getTotalDue($restaurantId);
        
        return [
            'total_franchisees' => $totalFranchisees,
            'active_franchisees' => $activeFranchisees,
            'active_agreements' => $activeAgreements,
            'pending_royalties' => $pendingRoyalties,
            'total_due' => $totalDue
        ];
    }
}
