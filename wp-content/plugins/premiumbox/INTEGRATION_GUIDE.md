# PremiumBox API Integration Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [Authentication Setup](#authentication-setup)
3. [Common Integration Scenarios](#common-integration-scenarios)
4. [Code Examples](#code-examples)
5. [Testing Your Integration](#testing-your-integration)
6. [Production Deployment](#production-deployment)
7. [Troubleshooting](#troubleshooting)

## Getting Started

### Prerequisites

- Active PremiumBox user account
- PHP 7.4+ or Python 3.7+ (depending on your integration language)
- cURL extension enabled (for PHP)
- HTTPS-enabled server for callback handling
- Basic understanding of REST APIs and JSON

### Quick Start (5 Minutes)

1. **Generate API Credentials**
   - Log in to your PremiumBox account
   - Navigate to User API settings
   - Click "Create New API Key"
   - Select required permissions
   - Save your API-Login and API-Key

2. **Test Connection**
   ```bash
   php api-examples/test_connection.php
   ```
   
3. **Explore Available Currencies**
   ```bash
   php api-examples/get_currencies.php
   ```

## Authentication Setup

### Step 1: Create API Key

1. Go to your account dashboard
2. Find "API Management" or "User API" section
3. Click "Add New API Key"
4. Select allowed methods:
   - ✓ test (recommended for testing)
   - ✓ get_direction_currencies
   - ✓ get_directions
   - ✓ get_direction
   - ✓ get_calc
   - ✓ create_bid
   - ✓ bid_info
   - ✓ cancel_bid
   
5. Configure IP restrictions (optional but recommended):
   ```
   203.0.113.10
   198.51.100.20
   ```

6. Save and copy your credentials:
   - API-Login: `abc123def456...` (32 characters)
   - API-Key: `xyz789uvw456...`

### Step 2: Configure Your Application

**PHP Configuration:**
```php
<?php
// config.php
define('API_BASE_URL', 'https://your-domain.com/api/v1/');
define('API_LOGIN', 'your_api_login_here');
define('API_KEY', 'your_api_key_here');
?>
```

**Python Configuration:**
```python
# config.py
API_BASE_URL = 'https://your-domain.com/api/v1/'
API_LOGIN = 'your_api_login_here'
API_KEY = 'your_api_key_here'
```

**Environment Variables (Recommended):**
```bash
export PREMIUMBOX_API_URL="https://your-domain.com/api/v1/"
export PREMIUMBOX_API_LOGIN="your_api_login_here"
export PREMIUMBOX_API_KEY="your_api_key_here"
```

## Common Integration Scenarios

### Scenario 1: Display Exchange Calculator

**User Story:** As a website visitor, I want to see how much I'll receive for a given amount.

**Implementation Steps:**

1. Get available directions
2. Display currency pairs to user
3. User selects pair and enters amount
4. Calculate exchange in real-time
5. Display results

**Code Example:**
```php
<?php
require_once 'config.php';

// Get direction details
$direction = api_request('get_direction', [
    'direction_id' => $_GET['direction_id']
]);

// Calculate exchange
$calc = api_request('get_calc', [
    'direction_id' => $_GET['direction_id'],
    'calc_amount' => $_GET['amount'],
    'calc_action' => 1
]);

// Display results
echo "You send: {$calc['data']['sum_give_com']} {$calc['data']['currency_code_give']}\n";
echo "You get: {$calc['data']['sum_get_com']} {$calc['data']['currency_code_get']}\n";
?>
```

### Scenario 2: Automated Exchange Processing

**User Story:** As a service provider, I want to automatically process exchanges when users make payments.

**Implementation Steps:**

1. User initiates exchange on your platform
2. Validate amount and details
3. Create transaction via API
4. Display payment instructions to user
5. Monitor transaction status via callbacks
6. Update order status when complete

**Workflow:**
```
User Request → Validate → API create_bid → Display Instructions
                                              ↓
Transaction Callbacks ← Monitor Status ← User Pays
                ↓
          Update Order
                ↓
          Notify User
```

### Scenario 3: Transaction Status Monitoring

**User Story:** As a user, I want to track the status of my exchange transaction.

**Implementation:**

1. Store transaction ID/hash when created
2. Poll API periodically for status updates
3. Display current status to user
4. Handle status changes (payed, success, cancel)

**Code Example:**
```php
<?php
function check_transaction_status($transaction_id) {
    $result = api_request('bid_info', ['id' => $transaction_id]);
    
    if ($result['error'] == '0') {
        $status = $result['data']['status'];
        $status_title = $result['data']['status_title'];
        
        switch ($status) {
            case 'new':
                return "Awaiting payment";
            case 'payed':
                return "Processing exchange";
            case 'success':
                return "Completed successfully";
            case 'cancel':
                return "Cancelled";
            default:
                return $status_title;
        }
    }
    
    return "Unknown";
}
?>
```

## Code Examples

### Example 1: Complete Exchange Flow

```php
<?php
require_once 'api-examples/config.php';

// Step 1: Get direction details
$direction = api_request('get_direction', [
    'direction_id' => 1
]);

if ($direction['error'] != '0') {
    die("Error: {$direction['error_text']}");
}

// Step 2: Validate amount
$amount = 100;
$min = $direction['data']['min_give'];
$max = $direction['data']['max_give'];

if ($amount < $min || $amount > $max) {
    die("Amount must be between {$min} and {$max}");
}

// Step 3: Calculate exchange
$calc = api_request('get_calc', [
    'direction_id' => 1,
    'calc_amount' => $amount,
    'calc_action' => 1
]);

// Step 4: Create transaction
$transaction = api_request('create_bid', [
    'direction_id' => 1,
    'calc_amount' => $amount,
    'calc_action' => 1,
    'account_give' => 'sender_account',
    'account_get' => 'recipient_account',
    'user_email' => 'user@example.com',
    'callback_url' => 'https://your-site.com/callback.php'
]);

if ($transaction['error'] == '0') {
    echo "Transaction created: {$transaction['data']['id']}\n";
    echo "Status: {$transaction['data']['status']}\n";
    echo "Payment instructions:\n";
    echo $transaction['data']['api_actions']['instruction'];
} else {
    echo "Error: {$transaction['error_text']}\n";
}
?>
```

### Example 2: Callback Handler

```php
<?php
// callback.php
$data = $_POST;

// Validate callback
$transaction_id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$transaction_id || !$status) {
    http_response_code(400);
    exit;
}

// Update your database
// update_transaction_status($transaction_id, $status);

// Send notification based on status
switch ($status) {
    case 'success':
        // Send success email
        // Credit user account
        break;
    case 'cancel':
        // Send cancellation notice
        break;
}

// Acknowledge receipt
http_response_code(200);
echo json_encode(['status' => 'ok']);
?>
```

### Example 3: Error Handling

```php
<?php
function safe_api_request($endpoint, $params = []) {
    try {
        $result = api_request($endpoint, $params);
        
        if ($result['error'] != '0') {
            throw new Exception($result['error_text'], $result['error']);
        }
        
        return $result['data'];
        
    } catch (Exception $e) {
        // Log error
        error_log("API Error [{$endpoint}]: " . $e->getMessage());
        
        // Handle specific errors
        switch ($e->getCode()) {
            case '1':
                // Resource not found
                return null;
            case '2':
                // Access denied / Maintenance
                throw new Exception("Service temporarily unavailable");
            case '3':
                // Validation error
                throw $e;
            default:
                throw new Exception("Unexpected error occurred");
        }
    }
}
?>
```

## Testing Your Integration

### Test Checklist

- [ ] API credentials are correct
- [ ] Test endpoint returns success
- [ ] Can retrieve currency list
- [ ] Can get direction details
- [ ] Calculations are accurate
- [ ] Transaction creation works
- [ ] Callback URL is accessible
- [ ] Error handling is implemented
- [ ] Logging is configured
- [ ] IP whitelist is set (if used)

### Testing Commands

```bash
# Test connection
php api-examples/test_connection.php

# Test currency retrieval
php api-examples/get_currencies.php

# Test calculation
php api-examples/calculate_exchange.php

# Test transaction creation (use test data)
php api-examples/create_transaction.php

# Monitor logs
tail -f api-examples/api_requests.log
```

### Common Test Scenarios

1. **Valid Request**
   - Expected: Success response with data
   
2. **Invalid Credentials**
   - Expected: Error code 2 or no response
   
3. **Disabled Endpoint**
   - Expected: Error code 3 "Method not supported"
   
4. **Invalid Direction ID**
   - Expected: Error code 1 "Direction not found"
   
5. **Amount Below Minimum**
   - Expected: Adjusted amount or validation error
   
6. **Missing Required Fields**
   - Expected: Error code 3 with error_fields

## Production Deployment

### Pre-Deployment Checklist

- [ ] Generate production API credentials
- [ ] Configure production callback URL
- [ ] Set up IP whitelist for production servers
- [ ] Enable HTTPS on callback endpoint
- [ ] Set up error logging and monitoring
- [ ] Configure backup/fallback mechanisms
- [ ] Test in staging environment
- [ ] Document API usage limits
- [ ] Set up alerting for API errors
- [ ] Create runbook for common issues

### Security Recommendations

1. **Credential Management**
   - Use environment variables for credentials
   - Never commit credentials to version control
   - Rotate API keys quarterly
   - Use separate keys for dev/staging/production

2. **Network Security**
   - Always use HTTPS
   - Implement IP whitelisting
   - Use VPN for development access
   - Monitor API access logs

3. **Data Protection**
   - Sanitize logs (remove sensitive data)
   - Encrypt stored transaction data
   - Implement rate limiting
   - Validate all user inputs

### Monitoring Setup

**Log Important Events:**
```php
<?php
function log_api_event($event, $data) {
    $log = [
        'timestamp' => date('c'),
        'event' => $event,
        'data' => $data
    ];
    
    // Send to logging service
    // syslog(LOG_INFO, json_encode($log));
    
    // Or file
    file_put_contents('/var/log/api.log', 
        json_encode($log) . "\n", FILE_APPEND);
}

// Log successful transactions
log_api_event('transaction_created', ['id' => $transaction_id]);

// Log errors
log_api_event('api_error', ['endpoint' => $endpoint, 'error' => $error]);
?>
```

**Set Up Alerts:**
- Alert on authentication failures
- Alert on high error rates (>5% of requests)
- Alert on callback failures
- Alert on API timeouts

## Troubleshooting

### Common Issues

#### Issue: "IP blocked" Error

**Symptoms:**
- Error code 2 with message "IP blocked"

**Solutions:**
1. Check your current IP: `curl ifconfig.me`
2. Add IP to whitelist in API settings
3. If using dynamic IP, leave whitelist empty (less secure)
4. Check if using proxy/load balancer (add its IP)

#### Issue: "Method not supported" Error

**Symptoms:**
- Error code 3 when calling endpoint

**Solutions:**
1. Log in to account
2. Go to API key settings
3. Enable required endpoint
4. Save changes

#### Issue: Transaction Not Created

**Symptoms:**
- Error code 3 with error_fields

**Solutions:**
1. Check error_fields for specific issues
2. Verify all required fields are provided
3. Check field format (email, phone, etc.)
4. Verify amounts are within limits

#### Issue: Callback Not Received

**Symptoms:**
- Transaction status changes but callback not triggered

**Solutions:**
1. Verify callback URL is publicly accessible
2. Check callback endpoint returns 200 OK
3. Review callback logs on your server
4. Test callback URL with curl:
   ```bash
   curl -X POST https://your-site.com/callback.php \
        -d "id=123&status=success"
   ```

### Debug Mode

Enable detailed logging:

```php
<?php
define('API_DEBUG', true);

function api_request($endpoint, $params = []) {
    if (API_DEBUG) {
        echo "[DEBUG] Calling: $endpoint\n";
        echo "[DEBUG] Params: " . print_r($params, true) . "\n";
    }
    
    $result = /* ... make request ... */;
    
    if (API_DEBUG) {
        echo "[DEBUG] Response: " . print_r($result, true) . "\n";
    }
    
    return $result;
}
?>
```

### Getting Help

1. **Check Documentation**
   - API_DOCUMENTATION.md
   - Code examples in api-examples/
   
2. **Review Logs**
   - API request logs
   - Callback logs
   - Application error logs
   
3. **Test with cURL**
   ```bash
   curl -X POST https://your-domain.com/api/v1/test \
        -H "API-Login: your_login" \
        -H "API-Key: your_key"
   ```
   
4. **Contact Support**
   - Include API-Login (NOT API-Key)
   - Provide request/response samples
   - Include error codes and messages
   - Specify endpoint and parameters used

## Appendix

### API Endpoint Quick Reference

| Endpoint | Purpose | Auth Required |
|----------|---------|---------------|
| `test` | Test connection | Yes |
| `get_direction_currencies` | List currencies | Yes |
| `get_directions` | List directions | Yes |
| `get_direction` | Direction details | Yes |
| `get_calc` | Calculate exchange | Yes |
| `create_bid` | Create transaction | Yes |
| `bid_info` | Transaction info | Yes |
| `cancel_bid` | Cancel transaction | Yes |

### Response Status Codes

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | Not found |
| 2 | Access denied / Maintenance |
| 3 | Validation error |

### Transaction Statuses

| Status | Description |
|--------|-------------|
| new | Awaiting payment |
| payed | Processing exchange |
| success | Completed |
| cancel | Cancelled |
| refund | Refunded |

---

**Need Help?** Check the examples in `api-examples/` or refer to `API_DOCUMENTATION.md` for detailed endpoint specifications.
