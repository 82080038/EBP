<?php

namespace App\Modules\Feedback\Controllers;

use App\Core\BaseController;
use App\Modules\Feedback\Models\Review;
use App\Modules\Feedback\Models\ReviewCategory;
use App\Modules\Feedback\Models\ReviewResponse;
use App\Modules\Feedback\Models\Feedback;
use App\Modules\Feedback\Services\FeedbackService;
use App\Core\Auth;

class FeedbackController extends BaseController
{
    private $feedbackService;

    public function __construct()
    {
        parent::__construct();
        $this->feedbackService = new FeedbackService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get reviews
     * GET /api/reviews
     */
    public function getReviews()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $rating = $this->request->get('rating', null);
        $source = $this->request->get('source', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->feedbackService->getReviews($restaurantId, $status, $rating, $source, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single review
     * GET /api/reviews/{id}
     */
    public function getReview($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $review = $this->feedbackService->getReview($id, $restaurantId);
        
        if (!$review) {
            $this->jsonResponse(['error' => 'Review not found'], 404);
            return;
        }
        
        $this->jsonResponse($review);
    }

    /**
     * Create review
     * POST /api/reviews
     */
    public function createReview()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->feedbackService->createReview($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update review status
     * PATCH /api/reviews/{id}/status
     */
    public function updateReviewStatus($id)
    {
        $this->requirePermission('can_manage_feedback');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->feedbackService->updateReviewStatus($id, $restaurantId, $data->status);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Respond to review
     * POST /api/reviews/{id}/respond
     */
    public function respondToReview($id)
    {
        $this->requirePermission('can_manage_feedback');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->feedbackService->respondToReview($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get review categories
     * GET /api/reviews/categories
     */
    public function getReviewCategories()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $categories = $this->feedbackService->getReviewCategories($restaurantId);
        
        $this->jsonResponse($categories);
    }

    /**
     * Create review category
     * POST /api/reviews/categories
     */
    public function createReviewCategory()
    {
        $this->requirePermission('can_manage_feedback');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->feedbackService->createReviewCategory($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get feedback
     * GET /api/feedback
     */
    public function getFeedback()
    {
        $this->requirePermission('can_manage_feedback');
        
        $restaurantId = Auth::user()->restaurant_id;
        $type = $this->request->get('type', null);
        $status = $this->request->get('status', null);
        $priority = $this->request->get('priority', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->feedbackService->getFeedback($restaurantId, $type, $status, $priority, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single feedback
     * GET /api/feedback/{id}
     */
    public function getFeedbackItem($id)
    {
        $this->requirePermission('can_manage_feedback');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $feedback = $this->feedbackService->getFeedbackItem($id, $restaurantId);
        
        if (!$feedback) {
            $this->jsonResponse(['error' => 'Feedback not found'], 404);
            return;
        }
        
        $this->jsonResponse($feedback);
    }

    /**
     * Create feedback
     * POST /api/feedback
     */
    public function createFeedback()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->feedbackService->createFeedback($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update feedback status
     * PATCH /api/feedback/{id}/status
     */
    public function updateFeedbackStatus($id)
    {
        $this->requirePermission('can_manage_feedback');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->feedbackService->updateFeedbackStatus($id, $restaurantId, $userId, $data->status);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Add feedback comment
     * POST /api/feedback/{id}/comments
     */
    public function addFeedbackComment($id)
    {
        $this->requirePermission('can_manage_feedback');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->feedbackService->addFeedbackComment($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get feedback statistics
     * GET /api/feedback/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $stats = $this->feedbackService->getStatistics($restaurantId);
        
        $this->jsonResponse($stats);
    }
}
