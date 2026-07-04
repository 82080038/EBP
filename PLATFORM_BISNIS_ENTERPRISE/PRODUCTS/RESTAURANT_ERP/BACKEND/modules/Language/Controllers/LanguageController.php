<?php

namespace App\Modules\Language\Controllers;

use App\Core\BaseController;
use App\Modules\Language\Models\Language;
use App\Modules\Language\Models\Translation;
use App\Modules\Language\Models\UserLanguagePreference;
use App\Modules\Language\Models\RestaurantLanguageSetting;
use App\Modules\Language\Services\LanguageService;
use App\Core\Auth;

class LanguageController extends BaseController
{
    private $languageService;

    public function __construct()
    {
        parent::__construct();
        $this->languageService = new LanguageService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get available languages
     * GET /api/languages
     */
    public function getLanguages()
    {
        $languageModel = new Language();
        $languages = $languageModel->getActive();
        
        $this->jsonResponse($languages);
    }

    /**
     * Get translations for a language
     * GET /api/languages/{languageCode}/translations
     */
    public function getTranslations($languageCode)
    {
        $context = $this->request->get('context', null);
        
        $translations = $this->languageService->getTranslations($languageCode, $context);
        
        $this->jsonResponse($translations);
    }

    /**
     * Get user language preference
     * GET /api/languages/preference
     */
    public function getUserPreference()
    {
        $userId = Auth::user()->id;
        
        $preference = $this->languageService->getUserPreference($userId);
        
        $this->jsonResponse($preference);
    }

    /**
     * Set user language preference
     * POST /api/languages/preference
     */
    public function setUserPreference()
    {
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->languageService->setUserPreference($userId, $data->language_code);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get restaurant language settings
     * GET /api/languages/restaurant-settings
     */
    public function getRestaurantSettings()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $settings = $this->languageService->getRestaurantSettings($restaurantId);
        
        $this->jsonResponse($settings);
    }

    /**
     * Update restaurant language settings
     * PUT /api/languages/restaurant-settings
     */
    public function updateRestaurantSettings()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->languageService->updateRestaurantSettings($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Add translation
     * POST /api/languages/translations
     */
    public function addTranslation()
    {
        $this->requirePermission('can_manage_settings');
        
        $data = $this->request->getJSON();
        
        $result = $this->languageService->addTranslation($data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update translation
     * PUT /api/languages/translations/{id}
     */
    public function updateTranslation($id)
    {
        $this->requirePermission('can_manage_settings');
        
        $data = $this->request->getJSON();
        
        $result = $this->languageService->updateTranslation($id, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Delete translation
     * DELETE /api/languages/translations/{id}
     */
    public function deleteTranslation($id)
    {
        $this->requirePermission('can_manage_settings');
        
        $result = $this->languageService->deleteTranslation($id);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Translation deleted successfully']);
    }

    /**
     * Get translation groups
     * GET /api/languages/groups
     */
    public function getTranslationGroups()
    {
        $groups = $this->languageService->getTranslationGroups();
        
        $this->jsonResponse($groups);
    }

    /**
     * Import translations
     * POST /api/languages/import
     */
    public function importTranslations()
    {
        $this->requirePermission('can_manage_settings');
        
        $data = $this->request->getJSON();
        
        $result = $this->languageService->importTranslations($data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Export translations
     * GET /api/languages/export
     */
    public function exportTranslations()
    {
        $this->requirePermission('can_manage_settings');
        
        $languageCode = $this->request->get('language_code', null);
        $context = $this->request->get('context', null);
        
        $result = $this->languageService->exportTranslations($languageCode, $context);
        
        $this->jsonResponse($result);
    }

    /**
     * Clear translation cache
     * POST /api/languages/clear-cache
     */
    public function clearCache()
    {
        $this->requirePermission('can_manage_settings');
        
        $languageCode = $this->request->get('language_code', null);
        
        $result = $this->languageService->clearCache($languageCode);
        
        $this->jsonResponse($result);
    }
}
