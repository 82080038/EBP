<?php

use PHPUnit\Framework\TestCase;

class JWTTest extends TestCase
{
    private JWT $jwt;
    private string $testSecret = 'test_secret_key';

    protected function setUp(): void
    {
        $this->jwt = new JWT($this->testSecret);
    }

    public function testEncode(): void
    {
        $payload = [
            'user_id' => 1,
            'username' => 'testuser',
            'role' => 'admin'
        ];

        $token = $this->jwt->encode($payload);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token);
    }

    public function testDecodeValidToken(): void
    {
        $payload = [
            'user_id' => 1,
            'username' => 'testuser',
            'role' => 'admin'
        ];

        $token = $this->jwt->encode($payload);
        $decoded = $this->jwt->decode($token);
        
        $this->assertIsArray($decoded);
        $this->assertEquals($payload['user_id'], $decoded['user_id']);
        $this->assertEquals($payload['username'], $decoded['username']);
        $this->assertEquals($payload['role'], $decoded['role']);
    }

    public function testDecodeInvalidToken(): void
    {
        $invalidToken = 'invalid.token.here';
        $decoded = $this->jwt->decode($invalidToken);
        
        $this->assertFalse($decoded);
    }

    public function testDecodeMalformedToken(): void
    {
        $malformedToken = 'only.two.parts';
        $decoded = $this->jwt->decode($malformedToken);
        
        $this->assertFalse($decoded);
    }

    public function testValidateValidToken(): void
    {
        $payload = [
            'user_id' => 1,
            'username' => 'testuser'
        ];

        $token = $this->jwt->encode($payload);
        $isValid = $this->jwt->validate($token);
        
        $this->assertTrue($isValid);
    }

    public function testValidateInvalidToken(): void
    {
        $invalidToken = 'invalid.token.here';
        $isValid = $this->jwt->validate($invalidToken);
        
        $this->assertFalse($isValid);
    }

    public function testTokenExpiration(): void
    {
        $payload = [
            'user_id' => 1,
            'exp' => time() - 3600 // Expired 1 hour ago
        ];

        $token = $this->jwt->encode($payload);
        $decoded = $this->jwt->decode($token);
        
        $this->assertFalse($decoded);
    }

    public function testTokenNotExpired(): void
    {
        $payload = [
            'user_id' => 1,
            'exp' => time() + 3600 // Expires in 1 hour
        ];

        $token = $this->jwt->encode($payload);
        $decoded = $this->jwt->decode($token);
        
        $this->assertIsArray($decoded);
        $this->assertEquals(1, $decoded['user_id']);
    }

    public function testDifferentSecretFails(): void
    {
        $payload = ['user_id' => 1];
        
        $jwt1 = new JWT('secret1');
        $token = $jwt1->encode($payload);
        
        $jwt2 = new JWT('secret2');
        $decoded = $jwt2->decode($token);
        
        $this->assertFalse($decoded);
    }
}
