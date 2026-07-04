<?php

namespace App\Modules\Language\Services;

use App\Modules\Language\Models\Language;
use App\Modules\Language\Models\Translation;
use App\Modules\Language\Models\UserLanguagePreference;
use App\Modules\Language\Models\RestaurantLanguageSetting;
use App\Core\Database;

class LanguageService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get translations for a language
     */
    public function getTranslations($languageCode, $context = null)
    {
        $translationModel = new Translation();
        return $translationModel->getByLanguage($languageCode, $context);
    }

    /**
     * Get user language preference
     */
    public function getUserPreference($userId)
    {
        $preferenceModel = new UserLanguagePreference();
        $preference = $preferenceModel->getByUser($userId);
        
        if ($preference) {
            return [
                'language_code' => $preference['language_code'],
                'is_primary' => $preference['is_primary']
            ];
        }
        
        // Return default language
        return [
            'language_code' => 'id',
            'is_primary' => true
        ];
    }

    /**
     * Set user language preference
     */
    public function setUserPreference($userId, $languageCode)
    {
        $preferenceModel = new UserLanguagePreference();
        
        // Check if language exists
        $languageModel = new Language();
        $language = $languageModel->findByCode($languageCode);
        
        if (!$language || !$language['is_active']) {
            return ['success' => false, 'message' => 'Invalid or inactive language'];
        }
        
        // Check if preference already exists
        $existing = $preferenceModel->getByUser($userId);
        
        if ($existing) {
            // Update existing preference
            $updated = $preferenceModel->update($existing['id'], [
                'language_code' => $languageCode,
                'is_primary' => true
            ]);
            
            if ($updated) {
                return ['success' => true, 'message' => 'Language preference updated'];
            }
            
            return ['success' => false, 'message' => 'Failed to update preference'];
        }
        
        // Create new preference
        $preferenceId = $preferenceModel->create([
            'user_id' => $userId,
            'language_code' => $languageCode,
            'is_primary' => true
        ]);
        
        if ($preferenceId) {
            return ['success' => true, 'message' => 'Language preference set'];
        }
        
        return ['success' => false, 'message' => 'Failed to set preference'];
    }

    /**
     * Get restaurant language settings
     */
    public function getRestaurantSettings($restaurantId)
    {
        $settingModel = new RestaurantLanguageSetting();
        return $settingModel->getByRestaurant($restaurantId);
    }

    /**
     * Update restaurant language settings
     */
    public function updateRestaurantSettings($restaurantId, $data)
    {
        $settingModel = new RestaurantLanguageSetting();
        
        $result = ['success' => true, 'message' => 'Settings updated'];
        
        foreach ($data->settings as $setting) {
            $existing = $settingModel->findByRestaurantAndLanguage($restaurantId, $setting->language_code);
            
            if ($existing) {
                $settingModel->update($existing['id'], [
                    'is_primary' => $setting->is_primary ?? false,
                    'is_enabled' => $setting->is_enabled ?? true
                ]);
            } else {
                $settingModel->create([
                    'restaurant_id' => $restaurantId,
                    'language_code' => $setting->language_code,
                    'is_primary' => $setting->is_primary ?? false,
                    'is_enabled' => $setting->is_enabled ?? true
                ]);
            }
        }
        
        // Clear cache for this restaurant
        $this->clearCacheForRestaurant($restaurantId);
        
        return $result;
    }

    /**
     * Add translation
     */
    public function addTranslation($data)
    {
        $translationModel = new Translation();
        
        // Check if language exists
        $languageModel = new Language();
        $language = $languageModel->findByCode($data->language_code);
        
        if (!$language) {
            return ['success' => false, 'message' => 'Invalid language code'];
        }
        
        $translationId = $translationModel->create([
            'language_code' => $data->language_code,
            'translation_key' => $data->translation_key,
            'translation_value' => $data->translation_value,
            'context' => $data->context ?? null,
            'is_active' => true
        ]);
        
        if (!$translationId) {
            return ['success' => false, 'message' => 'Failed to add translation'];
        }
        
        // Clear cache
        $this->clearCache($data->language_code);
        
        return ['success' => true, 'message' => 'Translation added', 'translation_id' => $translationId];
    }

    /**
     * Update translation
     */
    public function updateTranslation($id, $data)
    {
        $translationModel = new Translation();
        $translation = $translationModel->findById($id);
        
        if (!$translation) {
            return ['success' => false, 'message' => 'Translation not found'];
        }
        
        $updateData = [];
        
        if (isset($data->translation_value)) {
            $updateData['translation_value'] = $data->translation_value;
        }
        if (isset($data->context)) {
            $updateData['context'] = $data->context;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        
        $updated = $translationModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update translation'];
        }
        
        // Clear cache
        $this->clearCache($translation['language_code']);
        
        return ['success' => true, 'message' => 'Translation updated'];
    }

    /**
     * Delete translation
     */
    public function deleteTranslation($id)
    {
        $translationModel = new Translation();
        $translation = $translationModel->findById($id);
        
        if (!$translation) {
            return ['success' => false, 'message' => 'Translation not found'];
        }
        
        $deleted = $translationModel->delete($id);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to delete translation'];
        }
        
        // Clear cache
        $this->clearCache($translation['language_code']);
        
        return ['success' => true, 'message' => 'Translation deleted'];
    }

    /**
     * Get translation groups
     */
    public function getTranslationGroups()
    {
        $sql = "SELECT * FROM translation_groups WHERE is_active = TRUE ORDER BY sort_order ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Import translations
     */
    public function importTranslations($data)
    {
        $translationModel = new Translation();
        
        $imported = 0;
        $failed = 0;
        
        foreach ($data->translations as $translation) {
            // Check if translation already exists
            $existing = $translationModel->findByKeyAndLanguage(
                $translation->translation_key,
                $translation->language_code,
                $translation->context ?? null
            );
            
            if ($existing) {
                // Update existing
                $updated = $translationModel->update($existing['id'], [
                    'translation_value' => $translation->translation_value,
                    'is_active' => true
                ]);
                
                if ($updated) {
                    $imported++;
                } else {
                    $failed++;
                }
            } else {
                // Create new
                $created = $translationModel->create([
                    'language_code' => $translation->language_code,
                    'translation_key' => $translation->translation_key,
                    'translation_value' => $translation->translation_value,
                    'context' => $translation->context ?? null,
                    'is_active' => true
                ]);
                
                if ($created) {
                    $imported++;
                } else {
                    $failed++;
                }
            }
        }
        
        // Clear cache for all affected languages
        $languages = array_unique(array_column($data->translations, 'language_code'));
        foreach ($languages as $languageCode) {
            $this->clearCache($languageCode);
        }
        
        return [
            'success' => true,
            'message' => 'Import completed',
            'imported' => $imported,
            'failed' => $failed
        ];
    }

    /**
     * Export translations
     */
    public function exportTranslations($languageCode = null, $context = null)
    {
        $translationModel = new Translation();
        
        if ($languageCode) {
            $translations = $translationModel->getByLanguage($languageCode, $context);
        } else {
            $translations = $translationModel->getAll($context);
        }
        
        return [
            'success' => true,
            'data' => $translations,
            'count' => count($translations)
        ];
    }

    /**
     * Clear translation cache
     */
    public function clearCache($languageCode = null)
    {
        if ($languageCode) {
            $sql = "DELETE FROM translation_cache WHERE language_code = ?";
            $this->db->query($sql, [$languageCode]);
        } else {
            $sql = "DELETE FROM translation_cache";
            $this->db->query($sql);
        }
        
        return ['success' => true, 'message' => 'Cache cleared'];
    }

    /**
     * Clear cache for restaurant
     */
    private function clearCacheForRestaurant($restaurantId)
    {
        // In real implementation, clear restaurant-specific cache
        // For now, clear all cache
        $this->clearCache();
    }

    /**
     * Get translation with caching
     */
    public function getTranslation($languageCode, $key, $context = null)
    {
        // Check cache first
        $cacheKey = $this->generateCacheKey($languageCode, $key, $context);
        $cached = $this->getFromCache($cacheKey);
        
        if ($cached) {
            return $cached;
        }
        
        // Get from database
        $translationModel = new Translation();
        $translation = $translationModel->findByKeyAndLanguage($key, $languageCode, $context);
        
        if ($translation) {
            // Cache the result
            $this->setCache($cacheKey, $translation['translation_value'], $languageCode);
            return $translation['translation_value'];
        }
        
        // Return key if not found
        return $key;
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey($languageCode, $key, $context)
    {
        return $languageCode . ':' . $key . ':' . ($context ?? 'default');
    }

    /**
     * Get from cache
     */
    private function getFromCache($cacheKey)
    {
        $sql = "SELECT cache_value FROM translation_cache 
                WHERE cache_key = ? AND expires_at > NOW()";
        $result = $this->db->query($sql, [$cacheKey])->fetch();
        
        if ($result) {
            return $result['cache_value'];
        }
        
        return null;
    }

    /**
     * Set cache
     */
    private function setCache($cacheKey, $value, $languageCode)
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "INSERT INTO translation_cache (language_code, cache_key, cache_value, expires_at)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE cache_value = VALUES(cache_value), expires_at = VALUES(expires_at)";
        
        $this->db->query($sql, [$languageCode, $cacheKey, $value, $expiresAt]);
    }
}
