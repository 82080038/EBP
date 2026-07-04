<?php

namespace App\Modules\Feedback\Services;

use App\Modules\Feedback\Models\Review;
use App\Modules\Feedback\Models\ReviewCategory;
use App\Modules\Feedback\Models\ReviewResponse;
use App\Modules\Feedback\Models\Feedback;
use App\Core\Database;

class FeedbackService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get reviews
     */
    public function getReviews($restaurantId, $status, $rating, $source, $page, $limit)
    {
        $reviewModel = new Review();
        return $reviewModel->getPaginated($restaurantId, $status, $rating, $source, $page, $limit);
    }

    /**
     * Get single review
     */
    public function getReview($id, $restaurantId)
    {
        $reviewModel = new Review();
        $review = $reviewModel->findById($id, $restaurantId);
        
        if ($review) {
            // Get category ratings
            $review['category_ratings'] = $this->getReviewCategoryRatings($id);
            
            // Get response
            $responseModel = new ReviewResponse();
            $review['response'] = $responseModel->getByReviewId($id);
        }
        
        return $review;
    }

    /**
     * Get review category ratings
     */
    private function getReviewCategoryRatings($reviewId)
    {
        $sql = "SELECT rr.*, rc.category_name 
                FROM review_ratings rr
                LEFT JOIN review_categories rc ON rr.category_id = rc.id
                WHERE rr.review_id = ?";
        return $this->db->query($sql, [$reviewId])->fetchAll();
    }

    /**
     * Create review
     */
    public function createReview($restaurantId, $userId, $data)
    {
        $reviewModel = new Review();
        
        $reviewData = [
            'restaurant_id' => $restaurantId,
            'customer_id' => $data->customer_id ?? null,
            'order_id' => $data->order_id ?? null,
            'rating' => $data->rating,
            'title' => $data->title ?? null,
            'review_text' => $data->review_text ?? null,
            'review_source' => $data->review_source ?? 'internal',
            'external_review_id' => $data->external_review_id ?? null,
            'external_source' => $data->external_source ?? null,
            'review_status' => 'pending',
            'is_public' => $data->is_public ?? true,
            'is_verified' => $data->is_verified ?? false
        ];
        
        $reviewId = $reviewModel->create($reviewData);
        
        if (!$reviewId) {
            return ['success' => false, 'message' => 'Failed to create review'];
        }
        
        // Add category ratings if provided
        if (isset($data->category_ratings) && is_array($data->category_ratings)) {
            foreach ($data->category_ratings as $categoryRating) {
                $this->addReviewCategoryRating($reviewId, $categoryRating);
            }
        }
        
        return ['success' => true, 'message' => 'Review created', 'review_id' => $reviewId];
    }

    /**
     * Add review category rating
     */
    private function addReviewCategoryRating($reviewId, $data)
    {
        $sql = "INSERT INTO review_ratings (review_id, category_id, rating) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating)";
        
        $this->db->query($sql, [$reviewId, $data->category_id, $data->rating]);
    }

    /**
     * Update review status
     */
    public function updateReviewStatus($id, $restaurantId, $status)
    {
        $reviewModel = new Review();
        $review = $reviewModel->findById($id, $restaurantId);
        
        if (!$review) {
            return ['success' => false, 'message' => 'Review not found'];
        }
        
        $updated = $reviewModel->update($id, ['review_status' => $status]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update review status'];
        }
        
        return ['success' => true, 'message' => 'Review status updated'];
    }

    /**
     * Respond to review
     */
    public function respondToReview($id, $restaurantId, $userId, $data)
    {
        $reviewModel = new Review();
        $review = $reviewModel->findById($id, $restaurantId);
        
        if (!$review) {
            return ['success' => false, 'message' => 'Review not found'];
        }
        
        $responseModel = new ReviewResponse();
        
        $responseData = [
            'review_id' => $id,
            'restaurant_id' => $restaurantId,
            'response_text' => $data->response_text,
            'responded_by' => $userId,
            'responded_at' => date('Y-m-d H:i:s')
        ];
        
        $responseId = $responseModel->create($responseData);
        
        if (!$responseId) {
            return ['success' => false, 'message' => 'Failed to add response'];
        }
        
        return ['success' => true, 'message' => 'Response added', 'response_id' => $responseId];
    }

    /**
     * Get review categories
     */
    public function getReviewCategories($restaurantId)
    {
        $categoryModel = new ReviewCategory();
        return $categoryModel->getByRestaurant($restaurantId);
    }

    /**
     * Create review category
     */
    public function createReviewCategory($restaurantId, $data)
    {
        $categoryModel = new ReviewCategory();
        
        $categoryData = [
            'restaurant_id' => $restaurantId,
            'category_name' => $data->category_name,
            'category_description' => $data->category_description ?? null,
            'icon_url' => $data->icon_url ?? null,
            'sort_order' => $data->sort_order ?? 0,
            'is_active' => true
        ];
        
        $categoryId = $categoryModel->create($categoryData);
        
        if (!$categoryId) {
            return ['success' => false, 'message' => 'Failed to create category'];
        }
        
        return ['success' => true, 'message' => 'Category created', 'category_id' => $categoryId];
    }

    /**
     * Get feedback
     */
    public function getFeedback($restaurantId, $type, $status, $priority, $page, $limit)
    {
        $feedbackModel = new Feedback();
        return $feedbackModel->getPaginated($restaurantId, $type, $status, $priority, $page, $limit);
    }

    /**
     * Get single feedback
     */
    public function getFeedbackItem($id, $restaurantId)
    {
        $feedbackModel = new Feedback();
        $feedback = $feedbackModel->findById($id, $restaurantId);
        
        if ($feedback) {
            // Get comments
            $feedback['comments'] = $this->getFeedbackComments($id);
        }
        
        return $feedback;
    }

    /**
     * Get feedback comments
     */
    private function getFeedbackComments($feedbackId)
    {
        $sql = "SELECT fc.*, u.username as commented_by_name 
                FROM feedback_comments fc
                LEFT JOIN users u ON fc.commented_by = u.id
                WHERE fc.feedback_id = ?
                ORDER BY fc.created_at ASC";
        return $this->db->query($sql, [$feedbackId])->fetchAll();
    }

    /**
     * Create feedback
     */
    public function createFeedback($restaurantId, $data)
    {
        $feedbackModel = new Feedback();
        
        $feedbackData = [
            'restaurant_id' => $restaurantId,
            'customer_id' => $data->customer_id ?? null,
            'feedback_type' => $data->feedback_type,
            'subject' => $data->subject,
            'message' => $data->message,
            'contact_email' => $data->contact_email ?? null,
            'contact_phone' => $data->contact_phone ?? null,
            'feedback_source' => $data->feedback_source ?? 'website',
            'feedback_status' => 'new',
            'priority' => $data->priority ?? 'medium'
        ];
        
        $feedbackId = $feedbackModel->create($feedbackData);
        
        if (!$feedbackId) {
            return ['success' => false, 'message' => 'Failed to create feedback'];
        }
        
        return ['success' => true, 'message' => 'Feedback created', 'feedback_id' => $feedbackId];
    }

    /**
     * Update feedback status
     */
    public function updateFeedbackStatus($id, $restaurantId, $userId, $status)
    {
        $feedbackModel = new Feedback();
        $feedback = $feedbackModel->findById($id, $restaurantId);
        
        if (!$feedback) {
            return ['success' => false, 'message' => 'Feedback not found'];
        }
        
        $updateData = ['feedback_status' => $status];
        
        if ($status === 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }
        
        $updated = $feedbackModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update feedback status'];
        }
        
        return ['success' => true, 'message' => 'Feedback status updated'];
    }

    /**
     * Add feedback comment
     */
    public function addFeedbackComment($id, $restaurantId, $userId, $data)
    {
        $sql = "INSERT INTO feedback_comments (feedback_id, comment_text, commented_by, created_at)
                VALUES (?, ?, ?, NOW())";
        
        $inserted = $this->db->query($sql, [$id, $data->comment_text, $userId]);
        
        if (!$inserted) {
            return ['success' => false, 'message' => 'Failed to add comment'];
        }
        
        return ['success' => true, 'message' => 'Comment added'];
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId)
    {
        $reviewModel = new Review();
        $feedbackModel = new Feedback();
        
        // Total reviews
        $totalReviews = $reviewModel->countByRestaurant($restaurantId);
        
        // Average rating
        $avgRating = $reviewModel->getAverageRating($restaurantId);
        
        // Rating distribution
        $ratingDistribution = $reviewModel->getRatingDistribution($restaurantId);
        
        // Pending reviews
        $pendingReviews = $reviewModel->countByStatus($restaurantId, 'pending');
        
        // Total feedback
        $totalFeedback = $feedbackModel->countByRestaurant($restaurantId);
        
        // Unresolved feedback
        $unresolvedFeedback = $feedbackModel->countByStatus($restaurantId, 'new');
        
        return [
            'total_reviews' => $totalReviews,
            'average_rating' => $avgRating,
            'rating_distribution' => $ratingDistribution,
            'pending_reviews' => $pendingReviews,
            'total_feedback' => $totalFeedback,
            'unresolved_feedback' => $unresolvedFeedback
        ];
    }
}
