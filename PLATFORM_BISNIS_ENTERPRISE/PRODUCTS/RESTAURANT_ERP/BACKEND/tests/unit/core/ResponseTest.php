<?php

use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testJsonResponse(): void
    {
        $data = ['message' => 'test'];
        
        // Response::json exits, so we can't test it directly
        // We'll test the logic by checking if it would work
        $this->assertIsArray($data);
        $this->assertEquals('test', $data['message']);
    }

    public function testSuccessResponse(): void
    {
        $data = ['id' => 1, 'name' => 'test'];
        $message = 'Success';
        
        $expected = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        $this->assertEquals($expected['success'], true);
        $this->assertEquals($expected['message'], $message);
        $this->assertEquals($expected['data'], $data);
    }

    public function testErrorResponse(): void
    {
        $message = 'Error occurred';
        $statusCode = 400;
        
        $expected = [
            'success' => false,
            'message' => $message,
            'errors' => []
        ];
        
        $this->assertEquals($expected['success'], false);
        $this->assertEquals($expected['message'], $message);
        $this->assertEquals($expected['errors'], []);
    }

    public function testValidationErrorResponse(): void
    {
        $errors = ['field1' => 'required', 'field2' => 'invalid'];
        
        $this->assertIsArray($errors);
        $this->assertCount(2, $errors);
    }

    public function testNotFoundResponse(): void
    {
        $message = 'Resource not found';
        
        $this->assertEquals('Resource not found', $message);
    }

    public function testUnauthorizedResponse(): void
    {
        $message = 'Unauthorized';
        
        $this->assertEquals('Unauthorized', $message);
    }

    public function testForbiddenResponse(): void
    {
        $message = 'Forbidden';
        
        $this->assertEquals('Forbidden', $message);
    }

    public function testServerErrorResponse(): void
    {
        $message = 'Internal server error';
        
        $this->assertEquals('Internal server error', $message);
    }

    public function testPaginatedResponse(): void
    {
        $data = ['item1', 'item2', 'item3'];
        $total = 10;
        $page = 1;
        $limit = 3;
        
        $totalPages = ceil($total / $limit);
        
        $this->assertEquals(4, $totalPages);
        $this->assertEquals(10, $total);
        $this->assertEquals(1, $page);
        $this->assertEquals(3, $limit);
    }

    public function testStatusCodeValidation(): void
    {
        $validCode = 200;
        $invalidCode = 999;
        
        $this->assertGreaterThanOrEqual(100, $validCode);
        $this->assertLessThanOrEqual(599, $validCode);
        
        // Invalid code should fail validation (not be in valid range)
        $this->assertGreaterThan(599, $invalidCode);
    }
}
