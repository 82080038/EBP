# ESAMF Shared Engine Extraction

**Document ID:** ESAMF-EXTRACTION-002

**Version:** 1.0

**Purpose:** Define the methodology for extracting Shared Engines from repositories

---

# Overview

Shared Engine Extraction is the process of isolating widely applicable components from repositories and preparing them for migration to EBP Shared Engines (07_SHARED_ENGINES). This guide provides a systematic methodology for extracting Shared Engines.

---

# Shared Engine Characteristics

Before extraction, verify the component meets Shared Engine criteria:

- **Widely Applicable**: Used by MANY products (typically 3+)
- **Mostly Generic**: Minimal industry-specific logic
- **Configurable**: Can be configured for different contexts
- **Complex**: Contains significant business logic
- **Valuable**: Provides substantial value to products

---

# Extraction Process

## Phase 1: Preparation

### Step 1: Component Identification

```
Component: [Component Name]
Location: [Component Location]
Files:
- File 1: [Path]
- File 2: [Path]
- File 3: [Path]
```

### Step 2: Usage Analysis

```
Current Usage:
- Product 1: [How it's used, configuration]
- Product 2: [How it's used, configuration]
- Product 3: [How it's used, configuration]

Potential Usage:
- Product 4: [How it could be used]
- Product 5: [How it could be used]
```

### Step 3: Dependency Analysis

```
External Dependencies:
- [Dependency 1: Version, Purpose]
- [Dependency 2: Version, Purpose]

Internal Dependencies:
- [Internal Dependency 1: Purpose]
- [Internal Dependency 2: Purpose]
```

### Step 4: Create Extraction Branch

```bash
git checkout -b extract/[component-name]
```

---

## Phase 2: Isolation

### Step 1: Copy Component Files

```bash
# Create temporary directory
mkdir -p temp/[component-name]

# Copy component files
cp [component-location]/* temp/[component-name]/
```

### Step 2: Identify Industry-Specific Code

```
Industry-Specific Code:
- [Code snippet 1: Location, Industry, Reason]
- [Code snippet 2: Location, Industry, Reason]
- [Code snippet 3: Location, Industry, Reason]
```

### Step 3: Identify Configuration Points

```
Configuration Points:
- [Configuration 1: Current value, Should be configurable]
- [Configuration 2: Current value, Should be configurable]
- [Configuration 3: Current value, Should be configurable]
```

### Step 4: Identify Extension Points

```
Extension Points:
- [Extension 1: Current implementation, Should be extensible]
- [Extension 2: Current implementation, Should be extensible]
- [Extension 3: Current implementation, Should be extensible]
```

---

## Phase 3: Decoupling

### Step 1: Remove Industry-Specific Code

**Before:**
```php
class NotificationService {
    public function sendNotification($message, $recipient) {
        if ($this->isRestaurantContext()) {
            // Restaurant-specific notification format
            $message = $this->formatRestaurantMessage($message);
        }
        // Send notification
    }
}
```

**After:**
```php
class NotificationService {
    public function sendNotification($message, $recipient, $formatter = null) {
        if ($formatter) {
            $message = $formatter->format($message);
        }
        // Send notification
    }
}
```

### Step 2: Extract Configuration

**Before:**
```php
class NotificationService {
    private $emailProvider = 'sendgrid';
    private $smsProvider = 'twilio';
    private $retryAttempts = 3;
}
```

**After:**
```php
class NotificationService {
    private $config;
    
    public function __construct(array $config = []) {
        $this->config = array_merge([
            'email_provider' => 'sendgrid',
            'sms_provider' => 'twilio',
            'retry_attempts' => 3,
            'retry_delay' => 1000,
            'timeout' => 30
        ], $config);
    }
}
```

### Step 3: Create Extension Interfaces

```php
interface NotificationFormatterInterface {
    public function format($message);
}

interface NotificationProviderInterface {
    public function send($message, $recipient);
}
```

### Step 4: Implement Strategy Pattern

**Before:**
```php
class NotificationService {
    public function sendEmail($message, $recipient) {
        // SendGrid-specific code
    }
    
    public function sendSMS($message, $recipient) {
        // Twilio-specific code
    }
}
```

**After:**
```php
class NotificationService {
    private $emailProvider;
    private $smsProvider;
    
    public function __construct(
        NotificationProviderInterface $emailProvider,
        NotificationProviderInterface $smsProvider
    ) {
        $this->emailProvider = $emailProvider;
        $this->smsProvider = $smsProvider;
    }
    
    public function sendEmail($message, $recipient) {
        $this->emailProvider->send($message, $recipient);
    }
    
    public function sendSMS($message, $recipient) {
        $this->smsProvider->send($message, $recipient);
    }
}
```

---

## Phase 4: Generalization

### Step 1: Add Plugin System

```php
class NotificationService {
    private $plugins = [];
    
    public function registerPlugin(NotificationPluginInterface $plugin) {
        $this->plugins[] = $plugin;
    }
    
    public function sendNotification($message, $recipient) {
        foreach ($this->plugins as $plugin) {
            $plugin->beforeSend($message, $recipient);
        }
        
        // Send notification
        
        foreach ($this->plugins as $plugin) {
            $plugin->afterSend($message, $recipient);
        }
    }
}
```

### Step 2: Add Template System

```php
class NotificationService {
    private $templates = [];
    
    public function addTemplate($name, $template) {
        $this->templates[$name] = $template;
    }
    
    public function sendTemplate($templateName, $data, $recipient) {
        $template = $this->templates[$templateName];
        $message = $this->renderTemplate($template, $data);
        $this->sendNotification($message, $recipient);
    }
}
```

### Step 3: Add Context Support

```php
class NotificationService {
    public function sendNotification($message, $recipient, $context = []) {
        // Context can include:
        // - priority
        // - channels (email, sms, push)
        // - scheduling
        // - metadata
        $priority = $context['priority'] ?? 'normal';
        $channels = $context['channels'] ?? ['email'];
        
        // Send based on context
    }
}
```

---

## Phase 5: Standardization

### Step 1: Apply EBP Coding Standards

- **PSR-12**: Follow PSR-12 coding standards
- **Naming**: Follow EBP naming conventions
- **Documentation**: Add PHPDoc blocks
- **Comments**: Add inline comments for complex logic

### Step 2: Apply EBP Architecture Standards

- **Dependency Injection**: Use dependency injection
- **Interfaces**: Implement interfaces
- **Strategy Pattern**: Use strategy pattern for providers
- **Plugin System**: Implement plugin system for extensibility

### Step 3: Apply EBP Security Standards

- **Input Validation**: Validate all inputs
- **Output Encoding**: Encode all outputs
- **Rate Limiting**: Implement rate limiting
- **Authentication**: Secure provider credentials

### Step 4: Apply EBP Performance Standards

- **Caching**: Implement caching where appropriate
- **Queueing**: Use queue for async operations
- **Batching**: Implement batching for bulk operations
- **Monitoring**: Add performance monitoring

---

## Phase 6: Testing

### Step 1: Create Unit Tests

```php
class NotificationServiceTest extends TestCase {
    public function testSendEmail() {
        $provider = $this->createMock(NotificationProviderInterface::class);
        $provider->expects($this->once())
                 ->method('send')
                 ->with($this->equalTo('test message'), $this->equalTo('test@example.com'));
        
        $service = new NotificationService($provider, $provider);
        $service->sendEmail('test message', 'test@example.com');
    }
    
    public function testSendWithFormatter() {
        $provider = $this->createMock(NotificationProviderInterface::class);
        $formatter = $this->createMock(NotificationFormatterInterface::class);
        $formatter->expects($this->once())
                 ->method('format')
                 ->with($this->equalTo('test message'))
                 ->willReturn('formatted message');
        
        $service = new NotificationService($provider, $provider);
        $service->sendNotification('test message', 'test@example.com', $formatter);
    }
}
```

### Step 2: Create Integration Tests

```php
class NotificationServiceIntegrationTest extends TestCase {
    public function testSendEmailWithRealProvider() {
        $emailProvider = new SendGridProvider($this->config);
        $smsProvider = new TwilioProvider($this->config);
        
        $service = new NotificationService($emailProvider, $smsProvider);
        $result = $service->sendEmail('test message', 'test@example.com');
        
        $this->assertTrue($result->success);
    }
}
```

### Step 3: Create Provider Tests

```php
class SendGridProviderTest extends TestCase {
    public function testSendEmail() {
        $provider = new SendGridProvider($this->config);
        $result = $provider->send('test message', 'test@example.com');
        
        $this->assertTrue($result->success);
    }
}
```

### Step 4: Achieve Test Coverage

- **Target**: > 80% code coverage
- **Critical Paths**: 100% coverage
- **Provider Tests**: Test all providers
- **Error Paths**: Test all error paths

---

## Phase 7: Documentation

### Step 1: Create README

```markdown
# [Component Name] Engine

## Purpose
[Brief description of engine purpose]

## Installation
```bash
composer require ebp/[component-name]-engine
```

## Usage
```php
use EBP\SharedEngines\[Component]\[ComponentName]Engine;

$engine = new [ComponentName]Engine($config);
$result = $engine->method($params);
```

## Configuration
[Configuration options]

## Providers
[List available providers]

## Extensibility
[How to extend the engine]

## API
[API documentation]
```

### Step 2: Add PHPDoc Blocks

```php
/**
 * Notification Engine
 *
 * Provides notification delivery across multiple channels (email, SMS, push).
 *
 * @package EBP\SharedEngines\Notification
 * @author Petrick Software
 * @version 1.0.0
 */
class NotificationEngine {
    /**
     * Send notification to recipient
     *
     * @param string $message Message to send
     * @param string $recipient Recipient address
     * @param array $context Additional context (priority, channels, etc.)
     * @return NotificationResult Notification result
     * @throws NotificationException If notification fails
     */
    public function sendNotification($message, $recipient, $context = []) {
        // Implementation
    }
}
```

### Step 3: Create Provider Documentation

```markdown
# Providers

## Email Providers
- SendGrid
- Mailgun
- SMTP

## SMS Providers
- Twilio
- Nexmo
- AWS SNS

## Push Providers
- Firebase
- OneSignal
- AWS SNS
```

### Step 4: Create Migration Guide

```markdown
# Migration Guide

## Changes from Original
- [Change 1]
- [Change 2]
- [Change 3]

## Breaking Changes
- [Breaking change 1]
- [Breaking change 2]

## Migration Steps
1. [Step 1]
2. [Step 2]
3. [Step 3]

## Configuration Migration
[How to migrate configuration]
```

---

## Phase 8: Code Review

### Step 1: Self-Review

- [ ] Code follows EBP standards
- [ ] Code is well-documented
- [ ] Tests are comprehensive
- [ ] No security vulnerabilities
- [ ] No performance issues

### Step 2: Peer Review

- [ ] Peer review completed
- [ ] Reviewer feedback addressed
- [ ] Changes approved

### Step 3: Architecture Review

- [ ] Architecture review completed
- [ ] Architecture concerns addressed
- [ ] Changes approved

### Step 4: Security Review

- [ ] Security review completed
- [ ] Security concerns addressed
- [ ] Changes approved

---

## Phase 9: Publication

### Step 1: Create Release

```bash
# Tag release
git tag v1.0.0
git push origin v1.0.0
```

### Step 2: Publish to Package Registry

```bash
# Publish to Composer
composer publish
```

### Step 3: Update Documentation

- Update EBP documentation
- Update Software Asset Inventory
- Update Enterprise Knowledge Graph

---

# Extraction Checklist

## Preparation
- [ ] Component identified
- [ ] Usage analyzed
- [ ] Dependencies analyzed
- [ ] Extraction branch created

## Isolation
- [ ] Component files copied
- [ ] Industry-specific code identified
- [ ] Configuration points identified
- [ ] Extension points identified

## Decoupling
- [ ] Industry-specific code removed
- [ ] Configuration extracted
- [ ] Extension interfaces created
- [ ] Strategy pattern implemented

## Generalization
- [ ] Plugin system added
- [ ] Template system added
- [ ] Context support added

## Standardization
- [ ] EBP coding standards applied
- [ ] EBP architecture standards applied
- [ ] EBP security standards applied
- [ ] EBP performance standards applied

## Testing
- [ ] Unit tests created
- [ ] Integration tests created
- [ ] Provider tests created
- [ ] Test coverage > 80%

## Documentation
- [ ] README created
- [ ] PHPDoc blocks added
- [ ] Provider documentation created
- [ ] Migration guide created

## Code Review
- [ ] Self-review completed
- [ ] Peer review completed
- [ ] Architecture review completed
- [ ] Security review completed

## Publication
- [ ] Release created
- [ ] Published to package registry
- [ ] Documentation updated

---

# Common Extraction Patterns

## Pattern 1: Provider Abstraction

**Problem:** Component tied to specific provider

**Solution:** Create provider interface

```php
// Before
class NotificationService {
    public function sendEmail($message, $recipient) {
        // SendGrid-specific code
    }
}

// After
interface EmailProviderInterface {
    public function send($message, $recipient);
}

class SendGridProvider implements EmailProviderInterface {
    public function send($message, $recipient) {
        // SendGrid implementation
    }
}
```

## Pattern 2: Configuration Strategy

**Problem:** Hard-coded configuration for different contexts

**Solution:** Configuration profiles

```php
// Before
class NotificationService {
    private $config = [
        'email_provider' => 'sendgrid',
        'sms_provider' => 'twilio'
    ];
}

// After
class NotificationService {
    private $profiles = [
        'restaurant' => [
            'email_provider' => 'sendgrid',
            'sms_provider' => 'twilio'
        ],
        'hotel' => [
            'email_provider' => 'mailgun',
            'sms_provider' => 'nexmo'
        ]
    ];
    
    public function __construct($profile = 'default') {
        $this->config = $this->profiles[$profile] ?? $this->profiles['default'];
    }
}
```

## Pattern 3: Template System

**Problem:** Hard-coded message formats

**Solution:** Template system

```php
// Before
class NotificationService {
    public function sendOrderConfirmation($order) {
        $message = "Order #{$order->id} confirmed";
        $this->send($message, $order->customer->email);
    }
}

// After
class NotificationService {
    private $templates = [
        'order_confirmation' => 'Order #{{order_id}} confirmed'
    ];
    
    public function sendTemplate($templateName, $data, $recipient) {
        $template = $this->templates[$templateName];
        $message = $this->renderTemplate($template, $data);
        $this->send($message, $recipient);
    }
}
```

## Pattern 4: Plugin System

**Problem:** Cannot extend functionality without modifying code

**Solution:** Plugin system

```php
// Before
class NotificationService {
    public function sendNotification($message, $recipient) {
        // Send notification
    }
}

// After
class NotificationService {
    private $plugins = [];
    
    public function registerPlugin(NotificationPluginInterface $plugin) {
        $this->plugins[] = $plugin;
    }
    
    public function sendNotification($message, $recipient) {
        foreach ($this->plugins as $plugin) {
            $plugin->beforeSend($message, $recipient);
        }
        
        // Send notification
        
        foreach ($this->plugins as $plugin) {
            $plugin->afterSend($message, $recipient);
        }
    }
}
```

---

# Document End

**Document ID:** ESAMF-EXTRACTION-002

**Version:** 1.0
