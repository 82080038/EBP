<?php

namespace App\Modules\International\Services;

use App\Modules\International\Models\Currency;
use App\Modules\International\Models\Language;
use App\Modules\International\Models\Country;
use App\Modules\International\Models\RestaurantCountry;
use App\Modules\International\Models\Translation;
use App\Core\Database;

class InternationalService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get currencies
     */
    public function getCurrencies()
    {
        $currencyModel = new Currency();
        return $currencyModel->getActive();
    }

    /**
     * Get languages
     */
    public function getLanguages()
    {
        $languageModel = new Language();
        return $languageModel->getActive();
    }

    /**
     * Get countries
     */
    public function getCountries()
    {
        $countryModel = new Country();
        return $countryModel->getActive();
    }

    /**
     * Get restaurant countries
     */
    public function getRestaurantCountries($restaurantId)
    {
        $restaurantCountryModel = new RestaurantCountry();
        return $restaurantCountryModel->getByRestaurant($restaurantId);
    }

    /**
     * Add restaurant country
     */
    public function addRestaurantCountry($restaurantId, $data)
    {
        $restaurantCountryModel = new RestaurantCountry();
        
        $countryData = [
            'restaurant_id' => $restaurantId,
            'country_id' => $data->country_id,
            'is_primary' => $data->is_primary ?? false,
            'local_currency_id' => $data->local_currency_id,
            'local_language_id' => $data->local_language_id,
            'tax_registration_number' => $data->tax_registration_number ?? null,
            'vat_number' => $data->vat_number ?? null,
            'legal_entity_name' => $data->legal_entity_name ?? null,
            'business_address' => $data->business_address ?? null,
            'is_active' => true
        ];
        
        $restaurantCountryId = $restaurantCountryModel->create($countryData);
        
        if (!$restaurantCountryId) {
            return ['success' => false, 'message' => 'Failed to add country'];
        }
        
        return ['success' => true, 'message' => 'Country added', 'restaurant_country_id' => $restaurantCountryId];
    }

    /**
     * Get translations
     */
    public function getTranslations($languageCode, $category)
    {
        $translationModel = new Translation();
        return $translationModel->getByLanguage($languageCode, $category);
    }

    /**
     * Create translation
     */
    public function createTranslation($userId, $data)
    {
        $translationModel = new Translation();
        
        $translationData = [
            'translation_key_id' => $data->translation_key_id,
            'language_id' => $data->language_id,
            'translated_value' => $data->translated_value,
            'is_approved' => false,
            'translated_by' => $userId
        ];
        
        $translationId = $translationModel->create($translationData);
        
        if (!$translationId) {
            return ['success' => false, 'message' => 'Failed to create translation'];
        }
        
        return ['success' => true, 'message' => 'Translation created', 'translation_id' => $translationId];
    }

    /**
     * Convert currency
     */
    public function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        $currencyModel = new Currency();
        
        $fromRate = $currencyModel->getExchangeRate($fromCurrency);
        $toRate = $currencyModel->getExchangeRate($toCurrency);
        
        if (!$fromRate || !$toRate) {
            return ['success' => false, 'message' => 'Invalid currency code'];
        }
        
        // Convert to base currency (USD) first, then to target
        $inBase = $amount / $fromRate;
        $converted = $inBase * $toRate;
        
        return [
            'success' => true,
            'amount' => $amount,
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'converted_amount' => round($converted, 2),
            'exchange_rate' => round($toRate / $fromRate, 6)
        ];
    }

    /**
     * Get exchange rates
     */
    public function getExchangeRates($baseCurrency)
    {
        $currencyModel = new Currency();
        $currencies = $currencyModel->getActive();
        $baseRate = $currencyModel->getExchangeRate($baseCurrency);
        
        if (!$baseRate) {
            return ['success' => false, 'message' => 'Invalid base currency'];
        }
        
        $rates = [];
        foreach ($currencies as $currency) {
            $rate = $currency['exchange_rate'] / $baseRate;
            $rates[] = [
                'currency_code' => $currency['currency_code'],
                'currency_name' => $currency['currency_name'],
                'currency_symbol' => $currency['currency_symbol'],
                'exchange_rate' => round($rate, 6)
            ];
        }
        
        return [
            'success' => true,
            'base_currency' => $baseCurrency,
            'rates' => $rates
        ];
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $restaurantCountryModel = new RestaurantCountry();
        
        // Active countries
        $activeCountries = $restaurantCountryModel->countByRestaurant($restaurantId);
        
        // Get currencies
        $currencyModel = new Currency();
        $currencies = $currencyModel->getActive();
        
        // Get languages
        $languageModel = new Language();
        $languages = $languageModel->getActive();
        
        return [
            'active_countries' => $activeCountries,
            'available_currencies' => count($currencies),
            'available_languages' => count($languages)
        ];
    }
}
