# MODUL 38 — TESTING GUIDE

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Panduan testing lengkap untuk aplikasi Tour Guide dengan test cases per modul.

---

## 2. TYPES OF TESTING

### 2.1 Unit Testing

Testing individual components/functions in isolation.

### 2.2 Integration Testing

Testing how different modules work together.

### 2.3 Functional Testing

Testing against business requirements.

### 2.4 End-to-End Testing

Testing complete user flows.

### 2.5 Performance Testing

Testing system performance under load.

### 2.6 Security Testing

Testing for vulnerabilities.

---

## 3. AUTHENTICATION TEST CASES

### 3.1 Login Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| AUTH-001 | Valid login | Enter valid email and password | Login successful, redirect to dashboard |
| AUTH-002 | Invalid email | Enter invalid email | Error message "Invalid credentials" |
| AUTH-003 | Invalid password | Enter valid email, invalid password | Error message "Invalid credentials" |
| AUTH-004 | Empty fields | Submit empty form | Validation error for both fields |
| AUTH-005 | Account locked | Login with locked account | Error message "Account locked" |
| AUTH-006 | SQL injection attempt | Enter `' OR '1'='1` as password | Error message, no SQL error |
| AUTH-007 | XSS attempt | Enter `<script>alert(1)</script>` as email | Input sanitized, no script execution |

### 3.2 Registration Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| AUTH-008 | Valid registration | Fill all valid fields | Registration successful, email sent |
| AUTH-009 | Duplicate email | Register with existing email | Error message "Email already exists" |
| AUTH-010 | Weak password | Enter password < 8 chars | Validation error "Password too short" |
| AUTH-011 | Password mismatch | Enter different passwords | Validation error "Passwords do not match" |
| AUTH-012 | Invalid email format | Enter invalid email | Validation error "Invalid email format" |
| AUTH-013 | Empty required fields | Submit with empty fields | Validation errors for required fields |

### 3.3 Password Reset Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| AUTH-014 | Valid reset request | Enter valid email | Reset email sent |
| AUTH-015 | Invalid email | Enter non-existent email | Error message "Email not found" |
| AUTH-016 | Expired token | Use expired reset token | Error message "Token expired" |
| AUTH-017 | Valid password reset | Enter new password | Password updated, can login |
| AUTH-018 | Weak new password | Enter weak new password | Validation error |

---

## 4. BOOKING TEST CASES

### 4.1 Create Booking Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| BOOK-001 | Valid booking | Select guide, date, guests | Booking created, status pending |
| BOOK-002 | Past date booking | Select past date | Validation error "Invalid date" |
| BOOK-003 | Zero guests | Enter 0 guests | Validation error "At least 1 guest" |
| BOOK-004 | Guide unavailable | Select unavailable guide | Error message "Guide not available" |
| BOOK-005 | Duplicate booking | Same guide, same date | Error message "Already booked" |
| BOOK-006 | Max guests exceeded | Enter > max guests | Validation error |
| BOOK-007 | Unauthenticated booking | Try booking without login | Redirect to login |

### 4.2 Booking Status Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| BOOK-008 | Guide accepts booking | Guide clicks accept | Status changes to confirmed |
| BOOK-009 | Guide rejects booking | Guide clicks reject | Status changes to rejected |
| BOOK-010 | User cancels booking | User clicks cancel | Status changes to cancelled |
| BOOK-011 | Auto-cancel pending | Wait 24 hours | Status auto-cancelled |
| BOOK-012 | Complete booking | Mark as completed | Status changes to completed |

### 4.3 Payment Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| BOOK-013 | Valid payment | Complete payment | Status changes to paid |
| BOOK-014 | Payment failed | Simulate payment failure | Status remains pending |
| BOOK-015 | Refund booking | Request refund | Refund processed |
| BOOK-016 | Partial refund | Request partial refund | Partial refund processed |

---

## 5. DESTINATION TEST CASES

### 5.1 Destination Listing Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| DEST-001 | List all destinations | View destination list | All destinations displayed |
| DEST-002 | Filter by category | Select category | Filtered results shown |
| DEST-003 | Search by name | Enter search term | Matching results shown |
| DEST-004 | Sort by price | Sort by price low-high | Results sorted correctly |
| DEST-005 | Pagination | Navigate pages | Correct pagination |
| DEST-006 | Empty results | Search non-existent term | "No results" message |

### 5.2 Destination Detail Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| DEST-007 | View destination | Click destination | Detail page shown |
| DEST-008 | Invalid destination ID | Access invalid ID | 404 error |
| DEST-009 | View reviews | Scroll to reviews | Reviews displayed |
| DEST-010 | View gallery | Click gallery | Images displayed |

---

## 6. TICKET TEST CASES

### 6.1 Ticket Purchase Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| TICK-001 | Valid purchase | Select ticket, quantity | Ticket purchased, QR generated |
| TICK-002 | Insufficient quota | Exceed daily quota | Error message "Sold out" |
| TICK-003 | Invalid date | Select past date | Validation error |
| TICK-004 | Zero quantity | Enter 0 quantity | Validation error |
| TICK-005 | Max quantity exceeded | Enter > max quantity | Validation error |
| TICK-006 | Payment failed | Simulate payment failure | Order cancelled |

### 6.2 Ticket Verification Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| TICK-007 | Valid QR scan | Scan valid QR | Ticket verified, marked used |
| TICK-008 | Invalid QR scan | Scan invalid QR | Error message "Invalid ticket" |
| TICK-009 | Used ticket scan | Scan used ticket | Error message "Already used" |
| TICK-010 | Expired ticket scan | Scan expired ticket | Error message "Ticket expired" |
| TICK-011 | Wrong date scan | Scan wrong date ticket | Error message "Invalid date" |

---

## 7. HOTEL TEST CASES

### 7.1 Hotel Search Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| HOTL-001 | Search by location | Enter location | Hotels in location shown |
| HOTL-002 | Filter by price | Set price range | Filtered results shown |
| HOTL-003 | Filter by amenities | Select amenities | Filtered results shown |
| HOTL-004 | Check availability | Select dates | Available rooms shown |
| HOTL-005 | No availability | Select sold-out dates | "No availability" message |

### 7.2 Hotel Booking Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| HOTL-006 | Valid booking | Select room, dates | Booking created |
| HOTL-007 | Invalid dates | Select check-out before check-in | Validation error |
| HOTL-008 | Room unavailable | Select unavailable room | Error message |
| HOTL-009 | Max guests exceeded | Exceed room capacity | Validation error |

---

## 8. RESTAURANT TEST CASES

### 8.1 Restaurant Search Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| REST-001 | Search by location | Enter location | Restaurants shown |
| REST-002 | Filter by cuisine | Select cuisine | Filtered results shown |
| REST-003 | View menu | Click restaurant | Menu displayed |
| REST-004 | Search menu item | Search item | Matching items shown |

### 8.2 Order Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| REST-005 | Add to cart | Add item to cart | Item added |
| REST-006 | Update quantity | Change quantity | Quantity updated |
| REST-007 | Remove item | Remove from cart | Item removed |
| REST-008 | Empty cart checkout | Try checkout empty cart | Error message |
| REST-009 | Valid order | Complete order | Order created |
| REST-010 | Out of stock | Order out of stock item | Error message |

---

## 9. EVENT TEST CASES

### 9.1 Event Listing Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| EVNT-001 | List events | View event list | All events shown |
| EVNT-002 | Filter by date | Select date range | Filtered results shown |
| EVNT-003 | Filter by category | Select category | Filtered results shown |
| EVNT-004 | View event detail | Click event | Detail page shown |

### 9.2 Event Registration Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| EVNT-005 | Valid registration | Register for event | Registration successful |
| EVNT-006 | Full event | Register for full event | Error message "Sold out" |
| EVNT-007 | Duplicate registration | Register twice | Error message "Already registered" |
| EVNT-008 | Past event | Register for past event | Error message "Event ended" |
| EVNT-009 | Cancel registration | Cancel registration | Registration cancelled |

---

## 10. NOTIFICATION TEST CASES

### 10.1 Notification Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| NOTF-001 | Booking notification | Create booking | Notification sent |
| NOTF-002 | Payment notification | Complete payment | Notification sent |
| NOTF-003 | Event reminder | Event H-1 | Reminder sent |
| NOTF-004 | Mark as read | Click notification | Marked as read |
| NOTF-005 | Mark all as read | Click mark all | All marked as read |
| NOTF-006 | Unread count | Check badge | Correct count shown |

---

## 11. REVIEW TEST CASES

### 11.1 Review Submission Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| REVW-001 | Valid review | Submit valid review | Review submitted |
| REVW-002 | Invalid rating | Submit rating > 5 | Validation error |
| REVW-003 | Empty comment | Submit empty comment | Validation error |
| REVW-004 | Duplicate review | Submit twice | Error message |
| REVW-005 | Uncompleted booking | Review without booking | Error message |

---

## 12. ADMIN TEST CASES

### 12.1 User Management Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| ADMN-001 | Add user | Create new user | User created |
| ADMN-002 | Edit user | Update user info | Info updated |
| ADMN-003 | Ban user | Ban user | User banned |
| ADMN-004 | Unban user | Unban user | User unbanned |
| ADMN-005 | Delete user | Delete user | User deleted |

### 12.2 Guide Approval Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| ADMN-006 | Approve guide | Approve pending guide | Guide approved |
| ADMN-007 | Reject guide | Reject pending guide | Guide rejected |
| ADMN-008 | View documents | Click view documents | Documents shown |

### 12.3 Report Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| ADMN-009 | Generate report | Select date range | Report generated |
| ADMN-010 | Export CSV | Click export | CSV downloaded |
| ADMN-011 | Filter report | Apply filters | Filtered results shown |

---

## 13. SECURITY TEST CASES

### 13.1 SQL Injection Test Cases

| ID | Test Case | Input | Expected Result |
|----|-----------|-------|-----------------|
| SEC-001 | SQL injection login | `' OR '1'='1` | Error, no injection |
| SEC-002 | SQL injection search | `'; DROP TABLE users; --` | Error, no injection |
| SEC-003 | SQL injection ID | `1 UNION SELECT * FROM users` | Error, no injection |

### 13.2 XSS Test Cases

| ID | Test Case | Input | Expected Result |
|----|-----------|-------|-----------------|
| SEC-004 | XSS in name | `<script>alert(1)</script>` | Input sanitized |
| SEC-005 | XSS in comment | `<img src=x onerror=alert(1)>` | Input sanitized |
| SEC-006 | XSS in search | `<script>document.cookie</script>` | Input sanitized |

### 13.3 CSRF Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| SEC-007 | CSRF protection | Submit without token | Error "Invalid CSRF token" |
| SEC-008 | Valid token | Submit with valid token | Request accepted |

### 13.4 Rate Limiting Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| SEC-009 | Exceed rate limit | Make 11 requests in 1 minute | Error "Rate limit exceeded" |
| SEC-010 | Within limit | Make 5 requests in 1 minute | All requests accepted |

---

## 14. API TEST CASES

### 14.1 Authentication API Test Cases

| ID | Test Case | Endpoint | Expected Result |
|----|-----------|----------|-----------------|
| API-001 | Valid login | POST /api/auth/login | 200, token returned |
| API-002 | Invalid login | POST /api/auth/login | 401, error message |
| API-003 | Missing token | GET /api/user/profile | 401, unauthorized |
| API-004 | Invalid token | GET /api/user/profile | 401, invalid token |

### 14.2 Booking API Test Cases

| ID | Test Case | Endpoint | Expected Result |
|----|-----------|----------|-----------------|
| API-005 | Create booking | POST /api/bookings | 201, booking created |
| API-006 | Get bookings | GET /api/bookings/my | 200, bookings list |
| API-007 | Invalid data | POST /api/bookings | 400, validation error |
| API-008 | Unauthorized | POST /api/bookings (no token) | 401, unauthorized |

---

## 15. PERFORMANCE TEST CASES

### 15.1 Load Test Cases

| ID | Test Case | Load | Expected Result |
|----|-----------|------|-----------------|
| PERF-001 | Page load | 1 user | < 2 seconds |
| PERF-002 | Concurrent users | 100 users | < 3 seconds |
| PERF-003 | Peak load | 500 users | < 5 seconds |
| PERF-004 | Stress test | 1000 users | No crashes |

### 15.2 Database Performance Test Cases

| ID | Test Case | Query | Expected Result |
|----|-----------|-------|-----------------|
| PERF-005 | Simple query | SELECT * FROM users WHERE id = 1 | < 10ms |
| PERF-006 | Join query | SELECT * FROM bookings JOIN guides | < 50ms |
| PERF-007 | Complex query | SELECT with multiple JOINs | < 100ms |
| PERF-008 | Large result | SELECT * FROM bookings LIMIT 1000 | < 200ms |

---

## 16. AUTOMATION TESTING

### 16.1 PHPUnit Setup

**Install PHPUnit:**

```bash
composer require --dev phpunit/phpunit
```

**phpunit.xml:**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         verbose="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### 16.2 Sample Unit Test

```php
// tests/Unit/AuthServiceTest.php
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase {
    private $authService;
    
    protected function setUp(): void {
        $this->authService = new AuthService();
    }
    
    public function testValidLogin() {
        $result = $this->authService->login('test@example.com', 'password123');
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('token', $result);
    }
    
    public function testInvalidLogin() {
        $result = $this->authService->login('test@example.com', 'wrongpassword');
        $this->assertFalse($result['success']);
    }
}
```

### 16.3 Sample Integration Test

```php
// tests/Integration/BookingTest.php
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase {
    private $db;
    
    protected function setUp(): void {
        $this->db = Database::getInstance();
    }
    
    public function testCreateBooking() {
        $bookingService = new BookingService();
        $result = $bookingService->createBooking([
            'guide_id' => 1,
            'user_id' => 1,
            'booking_date' => '2026-07-01',
            'guests' => 2
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('booking_id', $result);
        
        // Verify in database
        $booking = $this->db->query(
            "SELECT * FROM bookings WHERE id = ?",
            [$result['booking_id']]
        )->fetch();
        
        $this->assertNotNull($booking);
        $this->assertEquals('pending', $booking['status']);
    }
}
```

---

## 17. TESTING CHECKLIST

### 17.1 Pre-Release Checklist

- [ ] All unit tests pass
- [ ] All integration tests pass
- [ ] Security tests pass
- [ ] Performance tests meet targets
- [ ] Manual testing completed
- [ ] Cross-browser testing completed
- [ ] Mobile testing completed
- [ ] Accessibility testing completed

### 17.2 Regression Testing

- [ ] Re-test all critical paths
- [ ] Re-test all bug fixes
- [ ] Re-test all new features
- [ ] Re-test integrations
- [ ] Re-test payment flows

---

## 18. TEST REPORTING

### 18.1 Test Report Template

```markdown
# Test Report

**Date:** 2026-06-30
**Tester:** [Name]
**Environment:** Staging

## Summary
- Total Tests: 150
- Passed: 145
- Failed: 5
- Blocked: 0

## Failed Tests
1. AUTH-005: Account locked - Expected error not shown
2. BOOK-004: Guide unavailable - Wrong error message
3. TICK-007: Valid QR scan - QR not recognized
4. SEC-009: Rate limit - Limit not enforced
5. API-005: Create booking - 500 error

## Recommendations
1. Fix account lock error message
2. Update guide availability check
3. Debug QR scanner
4. Verify rate limit configuration
5. Investigate API error
```

---

## 19. CONTINUOUS INTEGRATION

### 19.1 GitHub Actions Setup

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
    
    - name: Install dependencies
      run: composer install --no-interaction
    
    - name: Run PHPUnit
      run: vendor/bin/phpunit
    
    - name: Run Security Scan
      run: vendor/bin/security-checker security:check
```

---

## 20. RESOURCES

### 20.1 Testing Tools

- **PHPUnit**: Unit testing framework
- **Codeception**: Full-stack testing framework
- **Selenium**: Browser automation
- **Puppeteer**: Headless Chrome automation
- **JMeter**: Load testing
- **OWASP ZAP**: Security testing

### 20.2 Documentation

- PHPUnit Docs: https://phpunit.de/documentation.html
- Codeception Docs: https://codeception.com/docs
- Selenium Docs: https://www.selenium.dev/documentation/
- OWASP Testing Guide: https://owasp.org/www-project-web-security-testing-guide/

---

> **Modul Selanjutnya:** `39_AUTOMATION_TESTING_GUIDE.md`
