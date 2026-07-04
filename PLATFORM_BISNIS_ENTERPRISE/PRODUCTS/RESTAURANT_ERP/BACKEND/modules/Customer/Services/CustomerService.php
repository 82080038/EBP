<?php

namespace App\Modules\Customer\Services;

use App\Modules\Customer\Models\Customer;
use App\Modules\Customer\Models\CustomerPreference;
use App\Modules\Customer\Models\CustomerAddress;
use App\Modules\Customer\Models\CustomerNote;
use App\Modules\Customer\Models\CustomerTag;
use App\Modules\Customer\Models\CustomerVisit;
use App\Core\Database;

class CustomerService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get customers
     */
    public function getCustomers($restaurantId, $search, $isVip, $tagId, $page, $limit)
    {
        $customerModel = new Customer();
        return $customerModel->getPaginated($restaurantId, $search, $isVip, $tagId, $page, $limit);
    }

    /**
     * Get single customer
     */
    public function getCustomer($id, $restaurantId)
    {
        $customerModel = new Customer();
        $customer = $customerModel->findById($id, $restaurantId);
        
        if ($customer) {
            // Get preferences
            $preferenceModel = new CustomerPreference();
            $customer['preferences'] = $preferenceModel->getByCustomer($id, $restaurantId);
            
            // Get addresses
            $addressModel = new CustomerAddress();
            $customer['addresses'] = $addressModel->getByCustomer($id);
            
            // Get tags
            $customer['tags'] = $this->getCustomerTags($id);
            
            // Get visit summary
            $visitModel = new CustomerVisit();
            $customer['visit_summary'] = $visitModel->getSummary($id, $restaurantId);
        }
        
        return $customer;
    }

    /**
     * Get customer tags
     */
    private function getCustomerTags($customerId)
    {
        $sql = "SELECT ct.* FROM customer_tags ct
                INNER JOIN customer_tag_assignments cta ON ct.id = cta.tag_id
                WHERE cta.customer_id = ? AND ct.is_active = TRUE
                ORDER BY ct.sort_order ASC";
        return $this->db->query($sql, [$customerId])->fetchAll();
    }

    /**
     * Create customer
     */
    public function createCustomer($restaurantId, $userId, $data)
    {
        $customerModel = new Customer();
        
        $customerData = [
            'restaurant_id' => $restaurantId,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email ?? null,
            'phone' => $data->phone ?? null,
            'address_line1' => $data->address_line1 ?? null,
            'address_line2' => $data->address_line2 ?? null,
            'city' => $data->city ?? null,
            'state' => $data->state ?? null,
            'postal_code' => $data->postal_code ?? null,
            'country' => $data->country ?? 'Indonesia',
            'preferred_language' => $data->preferred_language ?? 'id',
            'dietary_preferences' => json_encode($data->dietary_preferences ?? []),
            'favorite_items' => json_encode($data->favorite_items ?? []),
            'is_active' => true,
            'is_vip' => $data->is_vip ?? false,
            'email_subscribed' => $data->email_subscribed ?? false,
            'sms_subscribed' => $data->sms_subscribed ?? false
        ];
        
        $customerId = $customerModel->create($customerData);
        
        if (!$customerId) {
            return ['success' => false, 'message' => 'Failed to create customer'];
        }
        
        // Create preferences if provided
        if (isset($data->preferences)) {
            $this->createPreferences($customerId, $restaurantId, $data->preferences);
        }
        
        // Create address if provided
        if (isset($data->address)) {
            $this->addAddress($customerId, $data->address);
        }
        
        // Assign tags if provided
        if (isset($data->tag_ids) && is_array($data->tag_ids)) {
            foreach ($data->tag_ids as $tagId) {
                $this->assignTagToCustomer($customerId, $tagId, $userId);
            }
        }
        
        return ['success' => true, 'message' => 'Customer created', 'customer_id' => $customerId];
    }

    /**
     * Create preferences
     */
    private function createPreferences($customerId, $restaurantId, $data)
    {
        $preferenceModel = new CustomerPreference();
        
        $preferenceData = [
            'customer_id' => $customerId,
            'restaurant_id' => $restaurantId,
            'preferred_table_type' => $data->preferred_table_type ?? 'any',
            'preferred_area' => $data->preferred_area ?? null,
            'meal_type_preference' => json_encode($data->meal_type_preference ?? []),
            'spice_level' => $data->spice_level ?? null,
            'service_level' => $data->service_level ?? 'standard',
            'allergies' => json_encode($data->allergies ?? []),
            'special_requests' => $data->special_requests ?? null
        ];
        
        $preferenceModel->create($preferenceData);
    }

    /**
     * Update customer
     */
    public function updateCustomer($id, $restaurantId, $data)
    {
        $customerModel = new Customer();
        $customer = $customerModel->findById($id, $restaurantId);
        
        if (!$customer) {
            return ['success' => false, 'message' => 'Customer not found'];
        }
        
        $updateData = [];
        
        if (isset($data->first_name)) {
            $updateData['first_name'] = $data->first_name;
        }
        if (isset($data->last_name)) {
            $updateData['last_name'] = $data->last_name;
        }
        if (isset($data->email)) {
            $updateData['email'] = $data->email;
        }
        if (isset($data->phone)) {
            $updateData['phone'] = $data->phone;
        }
        if (isset($data->is_vip)) {
            $updateData['is_vip'] = $data->is_vip;
        }
        if (isset($data->email_subscribed)) {
            $updateData['email_subscribed'] = $data->email_subscribed;
        }
        if (isset($data->sms_subscribed)) {
            $updateData['sms_subscribed'] = $data->sms_subscribed;
        }
        
        $updated = $customerModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update customer'];
        }
        
        return ['success' => true, 'message' => 'Customer updated'];
    }

    /**
     * Get preferences
     */
    public function getPreferences($customerId, $restaurantId)
    {
        $preferenceModel = new CustomerPreference();
        return $preferenceModel->getByCustomer($customerId, $restaurantId);
    }

    /**
     * Update preferences
     */
    public function updatePreferences($customerId, $restaurantId, $data)
    {
        $preferenceModel = new CustomerPreference();
        $preference = $preferenceModel->getByCustomer($customerId, $restaurantId);
        
        $updateData = [];
        
        if (isset($data->preferred_table_type)) {
            $updateData['preferred_table_type'] = $data->preferred_table_type;
        }
        if (isset($data->preferred_area)) {
            $updateData['preferred_area'] = $data->preferred_area;
        }
        if (isset($data->spice_level)) {
            $updateData['spice_level'] = $data->spice_level;
        }
        if (isset($data->service_level)) {
            $updateData['service_level'] = $data->service_level;
        }
        if (isset($data->allergies)) {
            $updateData['allergies'] = json_encode($data->allergies);
        }
        if (isset($data->special_requests)) {
            $updateData['special_requests'] = $data->special_requests;
        }
        
        if ($preference) {
            $preferenceModel->update($preference['id'], $updateData);
        } else {
            $updateData['customer_id'] = $customerId;
            $updateData['restaurant_id'] = $restaurantId;
            $preferenceModel->create($updateData);
        }
        
        return ['success' => true, 'message' => 'Preferences updated'];
    }

    /**
     * Get addresses
     */
    public function getAddresses($customerId)
    {
        $addressModel = new CustomerAddress();
        return $addressModel->getByCustomer($customerId);
    }

    /**
     * Add address
     */
    public function addAddress($customerId, $data)
    {
        $addressModel = new CustomerAddress();
        
        $addressData = [
            'customer_id' => $customerId,
            'address_type' => $data->address_type ?? 'home',
            'address_label' => $data->address_label ?? null,
            'address_line1' => $data->address_line1,
            'address_line2' => $data->address_line2 ?? null,
            'city' => $data->city,
            'state' => $data->state ?? null,
            'postal_code' => $data->postal_code,
            'country' => $data->country ?? 'Indonesia',
            'is_default' => $data->is_default ?? false,
            'delivery_notes' => $data->delivery_notes ?? null
        ];
        
        $addressId = $addressModel->create($addressData);
        
        if (!$addressId) {
            return ['success' => false, 'message' => 'Failed to add address'];
        }
        
        return ['success' => true, 'message' => 'Address added', 'address_id' => $addressId];
    }

    /**
     * Get notes
     */
    public function getNotes($customerId, $restaurantId)
    {
        $noteModel = new CustomerNote();
        return $noteModel->getByCustomer($customerId, $restaurantId);
    }

    /**
     * Add note
     */
    public function addNote($customerId, $restaurantId, $userId, $data)
    {
        $noteModel = new CustomerNote();
        
        $noteData = [
            'customer_id' => $customerId,
            'restaurant_id' => $restaurantId,
            'note_type' => $data->note_type ?? 'general',
            'note_text' => $data->note_text,
            'created_by' => $userId,
            'is_internal' => $data->is_internal ?? true
        ];
        
        $noteId = $noteModel->create($noteData);
        
        if (!$noteId) {
            return ['success' => false, 'message' => 'Failed to add note'];
        }
        
        return ['success' => true, 'message' => 'Note added', 'note_id' => $noteId];
    }

    /**
     * Get tags
     */
    public function getTags($restaurantId)
    {
        $tagModel = new CustomerTag();
        return $tagModel->getByRestaurant($restaurantId);
    }

    /**
     * Create tag
     */
    public function createTag($restaurantId, $data)
    {
        $tagModel = new CustomerTag();
        
        $tagData = [
            'restaurant_id' => $restaurantId,
            'tag_name' => $data->tag_name,
            'tag_color' => $data->tag_color ?? null,
            'tag_description' => $data->tag_description ?? null,
            'sort_order' => $data->sort_order ?? 0,
            'is_active' => true
        ];
        
        $tagId = $tagModel->create($tagData);
        
        if (!$tagId) {
            return ['success' => false, 'message' => 'Failed to create tag'];
        }
        
        return ['success' => true, 'message' => 'Tag created', 'tag_id' => $tagId];
    }

    /**
     * Assign tag to customer
     */
    public function assignTag($customerId, $restaurantId, $userId, $data)
    {
        $tagModel = new CustomerTag();
        $tag = $tagModel->findById($data->tag_id, $restaurantId);
        
        if (!$tag) {
            return ['success' => false, 'message' => 'Tag not found'];
        }
        
        $assigned = $this->assignTagToCustomer($customerId, $data->tag_id, $userId);
        
        if (!$assigned) {
            return ['success' => false, 'message' => 'Failed to assign tag'];
        }
        
        return ['success' => true, 'message' => 'Tag assigned'];
    }

    /**
     * Assign tag to customer
     */
    private function assignTagToCustomer($customerId, $tagId, $userId)
    {
        $sql = "INSERT INTO customer_tag_assignments (customer_id, tag_id, assigned_at, assigned_by)
                VALUES (?, ?, NOW(), ?)
                ON DUPLICATE KEY UPDATE assigned_at = VALUES(assigned_at), assigned_by = VALUES(assigned_by)";
        
        return $this->db->query($sql, [$customerId, $tagId, $userId]);
    }

    /**
     * Get visits
     */
    public function getVisits($customerId, $restaurantId, $page, $limit)
    {
        $visitModel = new CustomerVisit();
        return $visitModel->getPaginated($customerId, $restaurantId, $page, $limit);
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId)
    {
        $customerModel = new Customer();
        $visitModel = new CustomerVisit();
        
        // Total customers
        $totalCustomers = $customerModel->countByRestaurant($restaurantId);
        
        // VIP customers
        $vipCustomers = $customerModel->countVip($restaurantId);
        
        // Active customers (visited in last 30 days)
        $activeCustomers = $customerModel->countActive($restaurantId);
        
        // Total visits this month
        $visitsThisMonth = $visitModel->countByMonth($restaurantId);
        
        // Average visit value
        $avgVisitValue = $visitModel->getAverageValue($restaurantId);
        
        return [
            'total_customers' => $totalCustomers,
            'vip_customers' => $vipCustomers,
            'active_customers' => $activeCustomers,
            'visits_this_month' => $visitsThisMonth,
            'average_visit_value' => $avgVisitValue
        ];
    }
}
