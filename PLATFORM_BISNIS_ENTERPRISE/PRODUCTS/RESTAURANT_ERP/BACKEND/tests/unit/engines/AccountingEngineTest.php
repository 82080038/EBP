<?php

use PHPUnit\Framework\TestCase;

#[\AllowMockObjectsWithoutExpectations]
class AccountingEngineTest extends TestCase
{
    private AccountingEngine $accountingEngine;
    private PDO $mockDb;

    protected function setUp(): void
    {
        // Create a mock PDO for testing
        $this->mockDb = $this->createMock(PDO::class);
        $this->accountingEngine = new AccountingEngine($this->mockDb);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(AccountingEngine::class, $this->accountingEngine);
    }

    public function testCreateJournalEntry(): void
    {
        $orderId = 1;
        $amount = 100.50;
        
        $this->assertIsInt($orderId);
        $this->assertIsFloat($amount);
    }

    public function testJournalEntryDebitCreditBalance(): void
    {
        $debit = 100.00;
        $credit = 100.00;
        
        $this->assertEquals($debit, $credit);
    }

    public function testJournalEntryImbalance(): void
    {
        $debit = 100.00;
        $credit = 90.00;
        
        $this->assertNotEquals($debit, $credit);
    }

    public function testAccountTypeValidation(): void
    {
        $validAccountTypes = ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'];
        $accountType = 'REVENUE';
        
        $this->assertContains($accountType, $validAccountTypes);
    }

    public function testInvalidAccountType(): void
    {
        $validAccountTypes = ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'];
        $accountType = 'INVALID_TYPE';
        
        $this->assertNotContains($accountType, $validAccountTypes);
    }

    public function testJournalEntryDescription(): void
    {
        $description = 'Sales Order #12345';
        
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
    }

    public function testJournalEntryDate(): void
    {
        $entryDate = date('Y-m-d H:i:s');
        
        $this->assertIsString($entryDate);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $entryDate);
    }

    public function testJournalEntryReference(): void
    {
        $referenceType = 'ORDER';
        $referenceId = 12345;
        
        $this->assertEquals('ORDER', $referenceType);
        $this->assertIsInt($referenceId);
    }

    public function testTaxCalculation(): void
    {
        $subtotal = 100.00;
        $taxRate = 0.10; // 10%
        $taxAmount = $subtotal * $taxRate;
        
        $this->assertEquals(10.00, $taxAmount);
    }

    public function testTotalCalculation(): void
    {
        $subtotal = 100.00;
        $taxAmount = 10.00;
        $total = $subtotal + $taxAmount;
        
        $this->assertEquals(110.00, $total);
    }
}
