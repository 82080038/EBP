<?php

/**
 * EBP Core - JWT (JSON Web Token) Implementation
 * 
 * This is a core component of the Enterprise Business Platform
 * Used for authentication and authorization across all EBP products
 * 
 * @package EBP\Core\Authentication
 * @version 1.0.0
 */

class JWT
{
    private $secret;
    private $algorithm;

    public function __construct($secret = null, $algorithm = 'HS256')
    {
        $this->secret = $secret ?? getenv('JWT_SECRET') ?? 'ebp_secret_key_change_in_production';
        $this->algorithm = $algorithm;
    }

    /**
     * Encode payload to JWT token
     * 
     * @param array $payload Data to encode
     * @return string JWT token
     */
    public function encode($payload)
    {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ]);

        $header = $this->base64UrlEncode($header);
        $payload = json_encode($payload);
        $payload = $this->base64UrlEncode($payload);

        $signature = hash_hmac(
            'sha256',
            $header . "." . $payload,
            $this->secret,
            true
        );

        $signature = $this->base64UrlEncode($signature);

        return $header . "." . $payload . "." . $signature;
    }

    /**
     * Decode JWT token
     * 
     * @param string $token JWT token to decode
     * @return array|false Decoded payload or false on failure
     */
    public function decode($token)
    {
        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $tokenParts;

        $validSignature = hash_hmac(
            'sha256',
            $header . "." . $payload,
            $this->secret,
            true
        );

        $validSignature = $this->base64UrlEncode($validSignature);

        if ($signature !== $validSignature) {
            return false;
        }

        $payload = $this->base64UrlDecode($payload);
        $decoded = json_decode($payload, true);

        // Check expiration if present
        if (isset($decoded['exp']) && $decoded['exp'] < time()) {
            return false;
        }

        return $decoded;
    }

    /**
     * Validate JWT token
     * 
     * @param string $token JWT token to validate
     * @return bool True if valid, false otherwise
     */
    public function validate($token)
    {
        return $this->decode($token) !== false;
    }

    /**
     * Base64 URL encode
     * 
     * @param string $data Data to encode
     * @return string Encoded data
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     * 
     * @param string $data Data to decode
     * @return string Decoded data
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
