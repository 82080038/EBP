<?php

namespace App\Modules\International\Controllers;

use App\Core\BaseController;
use App\Modules\International\Models\Currency;
use App\Modules\International\Models\Language;
use App\Modules\International\Models\Country;
use App\Modules\International\Models\RestaurantCountry;
use App\Modules\International\Models\Translation;
use App\Modules\International\Services\InternationalService;
use App\Core\Auth;

class InternationalController extends BaseController
{
    private $internationalService;

    public function __construct()
    {
        parent::__construct();
        $this->internationalService = new InternationalService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get currencies
     * GET /api/international/currencies
     */
    public function getCurrencies()
    {
        $currencies = $this->internationalService->getCurrencies();
        
        $this->jsonResponse($currencies);
    }

    /**
     * Get languages
     * GET /api/international/languages
     */
    public function getLanguages()
    {
        $languages = $this->internationalService->getLanguages();
        
        $this->jsonResponse($languages);
    }

    /**
     * Get countries
     * GET /api/international/countries
     */
    public function getCountries()
    {
        $countries = $this->internationalService->getCountries();
        
        $this->jsonResponse($countries);
    }

    /**
     * Get restaurant countries
     * GET /api/international/restaurant-countries
     */
    public function getRestaurantCountries()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $countries = $this->internationalService->getRestaurantCountries($restaurantId);
        
        $this->jsonResponse($countries);
    }

    /**
     * Add restaurant country
     * POST /api/international/restaurant-countries
     */
    public function addRestaurantCountry()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->internationalService->addRestaurantCountry($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get translations
     * GET /api/international/translations
     */
    public function getTranslations()
    {
        $languageCode = $this->request->get('language', 'en');
        $category = $this->request->get('category', null);
        
        $translations = $this->internationalService->getTranslations($languageCode, $category);
        
        $this->jsonResponse($translations);
    }

    /**
     * Create translation
     * POST /api/international/translations
     */
    public function createTranslation()
    {
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->internationalService->createTranslation($userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Convert currency
     * POST /api/international/currency-convert
     */
    public function convertCurrency()
    {
        $data = $this->request->getJSON();
        
        $result = $this->internationalService->convertCurrency($data->amount, $data->from_currency, $data->to_currency);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get exchange rates
     * GET /api/international/exchange-rates
     */
    public function getExchangeRates()
    {
        $baseCurrency = $this->request->get('base', 'USD');
        
        $rates = $this->internationalService->getExchangeRates($baseCurrency);
        
        $this->jsonResponse($rates);
    }

    /**
     * Get international summary
     * GET /api/international/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->internationalService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
