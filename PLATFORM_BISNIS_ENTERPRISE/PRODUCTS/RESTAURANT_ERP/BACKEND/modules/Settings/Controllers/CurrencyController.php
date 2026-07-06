<?php

if (!class_exists('CurrencyService')) {
    require_once __DIR__ . '/../Services/CurrencyService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class CurrencyController
{
    private $service;

    public function __construct()
    {
        $this->service = new CurrencyService();
    }

    public function addCurrency($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->addCurrency($data);

        if ($result['success']) {
            Response::success($result['message'], ['currency_id' => $result['currency_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateExchangeRate($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $currencyId = $request['params']['id'] ?? null;
        $exchangeRate = $request['body']['exchange_rate'] ?? null;

        if (!$currencyId || !$exchangeRate) {
            Response::error('Currency ID and exchange rate are required');
            return;
        }

        $result = $this->service->updateExchangeRate($currencyId, $exchangeRate);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCurrencies($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getCurrencies();

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function convertCurrency($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $amount = $request['params']['amount'] ?? null;
        $fromCurrency = $request['params']['from'] ?? null;
        $toCurrency = $request['params']['to'] ?? null;

        if (!$amount || !$fromCurrency || !$toCurrency) {
            Response::error('Amount, from currency, and to currency are required');
            return;
        }

        $result = $this->service->convertCurrency($amount, $fromCurrency, $toCurrency);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
