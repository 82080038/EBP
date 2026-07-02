<?php

if (!class_exists('CurrencyRepository')) {
    require_once __DIR__ . '/../Repositories/CurrencyRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class CurrencyService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CurrencyRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function addCurrency($data)
    {
        try {
            if (empty($data['currency_code']) || empty($data['currency_name']) || empty($data['symbol'])) {
                return [
                    'success' => false,
                    'message' => 'Currency code, name, and symbol are required'
                ];
            }

            $currencyId = $this->repository->createCurrency($data);

            return [
                'success' => true,
                'message' => 'Currency added successfully',
                'currency_id' => $currencyId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add currency: ' . $e->getMessage()
            ];
        }
    }

    public function updateExchangeRate($currencyId, $exchangeRate)
    {
        try {
            $this->repository->updateExchangeRate($currencyId, $exchangeRate);

            return [
                'success' => true,
                'message' => 'Exchange rate updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update exchange rate: ' . $e->getMessage()
            ];
        }
    }

    public function getCurrencies()
    {
        try {
            $currencies = $this->repository->getCurrencies();
            
            return [
                'success' => true,
                'message' => 'Currencies retrieved successfully',
                'data' => $currencies
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get currencies: ' . $e->getMessage()
            ];
        }
    }

    public function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        try {
            $result = $this->repository->convertCurrency($amount, $fromCurrency, $toCurrency);
            
            return [
                'success' => true,
                'message' => 'Currency converted successfully',
                'data' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to convert currency: ' . $e->getMessage()
            ];
        }
    }
}
