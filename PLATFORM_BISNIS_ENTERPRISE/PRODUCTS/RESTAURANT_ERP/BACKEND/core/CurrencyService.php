<?php

class ExchangeRateService
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Add or update exchange rate
    public function setExchangeRate($tenantId, $fromCurrency, $toCurrency, $rate, $effectiveDate, $userId)
    {
        $sql = "
            INSERT INTO exchange_rates (tenant_id, from_currency, to_currency, rate, effective_date, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rate = VALUES(rate), created_by = VALUES(created_by)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            strtoupper($fromCurrency),
            strtoupper($toCurrency),
            $rate,
            $effectiveDate,
            $userId
        ]);

        return [
            'success' => true,
            'message' => 'Exchange rate set successfully'
        ];
    }

    // Get exchange rate for a specific date
    public function getExchangeRate($tenantId, $fromCurrency, $toCurrency, $date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        // If same currency, rate is 1
        if (strtoupper($fromCurrency) === strtoupper($toCurrency)) {
            return 1.0;
        }

        $sql = "
            SELECT rate 
            FROM exchange_rates 
            WHERE tenant_id = ? 
            AND from_currency = ? 
            AND to_currency = ? 
            AND effective_date <= ?
            ORDER BY effective_date DESC 
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            strtoupper($fromCurrency),
            strtoupper($toCurrency),
            $date
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['rate'];
        }

        // Try reverse rate
        $sql = "
            SELECT rate 
            FROM exchange_rates 
            WHERE tenant_id = ? 
            AND from_currency = ? 
            AND to_currency = ? 
            AND effective_date <= ?
            ORDER BY effective_date DESC 
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            strtoupper($toCurrency),
            strtoupper($fromCurrency),
            $date
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return 1 / $result['rate'];
        }

        throw new Exception("Exchange rate not found for {$fromCurrency} to {$toCurrency} on {$date}");
    }

    // Convert amount from one currency to another
    public function convertCurrency($tenantId, $amount, $fromCurrency, $toCurrency, $date = null)
    {
        if ($amount == 0) {
            return 0;
        }

        $rate = $this->getExchangeRate($tenantId, $fromCurrency, $toCurrency, $date);
        return $amount * $rate;
    }

    // Get all exchange rates for a tenant
    public function getExchangeRates($tenantId, $fromCurrency = null, $toCurrency = null)
    {
        $sql = "SELECT * FROM exchange_rates WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($fromCurrency) {
            $sql .= " AND from_currency = ?";
            $params[] = strtoupper($fromCurrency);
        }

        if ($toCurrency) {
            $sql .= " AND to_currency = ?";
            $params[] = strtoupper($toCurrency);
        }

        $sql .= " ORDER BY effective_date DESC, from_currency, to_currency";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get latest exchange rates for a tenant
    public function getLatestExchangeRates($tenantId)
    {
        $sql = "
            SELECT er.from_currency, er.to_currency, er.rate, er.effective_date
            FROM exchange_rates er
            INNER JOIN (
                SELECT from_currency, to_currency, MAX(effective_date) as max_date
                FROM exchange_rates
                WHERE tenant_id = ?
                GROUP BY from_currency, to_currency
            ) latest ON er.from_currency = latest.from_currency 
                AND er.to_currency = latest.to_currency 
                AND er.effective_date = latest.max_date
            WHERE er.tenant_id = ?
            ORDER BY er.from_currency, er.to_currency
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all available currencies
    public function getCurrencies($activeOnly = true)
    {
        $sql = "SELECT * FROM currencies";
        if ($activeOnly) {
            $sql .= " WHERE is_active = TRUE";
        }
        $sql .= " ORDER BY currency_code";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get currency by code
    public function getCurrency($currencyCode)
    {
        $sql = "SELECT * FROM currencies WHERE currency_code = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([strtoupper($currencyCode)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete exchange rate
    public function deleteExchangeRate($tenantId, $fromCurrency, $toCurrency, $effectiveDate)
    {
        $sql = "
            DELETE FROM exchange_rates 
            WHERE tenant_id = ? 
            AND from_currency = ? 
            AND to_currency = ? 
            AND effective_date = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            strtoupper($fromCurrency),
            strtoupper($toCurrency),
            $effectiveDate
        ]);

        return [
            'success' => true,
            'message' => 'Exchange rate deleted successfully'
        ];
    }

    // Get exchange rate history
    public function getExchangeRateHistory($tenantId, $fromCurrency, $toCurrency, $limit = 30)
    {
        $sql = "
            SELECT * FROM exchange_rates 
            WHERE tenant_id = ? 
            AND from_currency = ? 
            AND to_currency = ? 
            ORDER BY effective_date DESC 
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            strtoupper($fromCurrency),
            strtoupper($toCurrency),
            $limit
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
