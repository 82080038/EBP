<?php

if (!class_exists('CurrencyRepository')) {
    require_once __DIR__ . '/../Repositories/CurrencyRepository.php';
}


class CurrencyService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CurrencyRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
