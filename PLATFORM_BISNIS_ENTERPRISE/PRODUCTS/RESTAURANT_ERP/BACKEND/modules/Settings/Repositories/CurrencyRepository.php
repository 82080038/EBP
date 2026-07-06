<?php



class CurrencyRepository
{
    private $db;

    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            $host = 'localhost';
            $dbname = 'ebp_restaurant_db';
            $username = 'ebp_app';
            $password = 'ebp_secure_password_2026';
            $socket = '/opt/lampp/var/mysql/mysql.sock';

            $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function createCurrency($data)
    {
        $sql = "INSERT INTO currencies (currency_code, currency_name, symbol, exchange_rate, is_base, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['currency_code'],
            $data['currency_name'],
            $data['symbol'],
            $data['exchange_rate'] ?? 1.0,
            $data['is_base'] ? 1 : 0,
            $data['is_active'] ? 1 : 0
        ]);
        return $this->db->lastInsertId();
    }

    public function updateExchangeRate($currencyId, $exchangeRate)
    {
        $sql = "UPDATE currencies SET exchange_rate = ?, updated_at = CURRENT_TIMESTAMP WHERE currency_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exchangeRate, $currencyId]);
    }

    public function getCurrencies()
    {
        $sql = "SELECT * FROM currencies WHERE is_active = TRUE ORDER BY is_base DESC, currency_code ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        $sql = "SELECT exchange_rate FROM currencies WHERE currency_code = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fromCurrency]);
        $fromRate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$fromRate) {
            throw new Exception('Source currency not found');
        }

        $stmt->execute([$toCurrency]);
        $toRate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$toRate) {
            throw new Exception('Target currency not found');
        }

        // Convert: (amount * from_rate) / to_rate
        $convertedAmount = ($amount * $fromRate['exchange_rate']) / $toRate['exchange_rate'];
        
        return [
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'amount' => $amount,
            'converted_amount' => $convertedAmount,
            'exchange_rate' => $toRate['exchange_rate'] / $fromRate['exchange_rate']
        ];
    }
}
