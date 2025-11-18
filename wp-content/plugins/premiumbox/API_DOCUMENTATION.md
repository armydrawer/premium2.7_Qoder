# PremiumBox API Integration Guide

## Introduction

This document provides comprehensive guidance for integrating with the PremiumBox cryptocurrency exchange platform API. The API enables external applications to interact with the exchange system programmatically, allowing automated currency exchange operations, transaction management, and real-time data retrieval.

The PremiumBox API follows RESTful principles and uses JSON for data exchange. All endpoints require authentication via API credentials and support optional IP-based access restrictions for enhanced security.

### Target Audience

This guide is intended for developers, system integrators, and technical personnel responsible for integrating third-party applications with the PremiumBox platform.

### API Overview

The API provides access to core exchange functionality including:

- Currency and exchange direction retrieval
- Real-time exchange rate calculations
- Transaction creation and management
- Transaction status monitoring
- Partner program operations

## Authentication

### Authentication Mechanism

The PremiumBox API uses header-based authentication with two required credentials:

| Header Name | Description | Format |
|-------------|-------------|--------|
| `API-Login` | Unique login identifier for API access | Alphanumeric string (32 characters) |
| `API-Key` | Secret key for authentication | Alphanumeric string (variable length) |
| `API-Lang` | Optional language code for localized responses | ISO 639-1 code (e.g., `en`, `ru`) |

### Obtaining API Credentials

Users must generate API credentials through their account dashboard:

1. Navigate to the User API management page in your account
2. Create a new API key by selecting allowed methods
3. Configure IP address restrictions (optional but recommended)
4. System generates unique `API-Login` and `API-Key` pair
5. Store credentials securely for use in API requests

### IP Address Restrictions

For enhanced security, API access can be restricted to specific IP addresses:

- Multiple IP addresses supported (one per line)
- Requests from non-whitelisted IPs receive error code 2 with message "IP blocked"
- Leave empty to allow access from any IP address

### Authentication Error Responses

| Error Code | Error Message | Description |
|------------|---------------|-------------|
| 2 | IP blocked | Request originated from non-whitelisted IP address |
| 3 | Method not supported | Requested endpoint is not enabled for this API key |

## Base URL and Request Format

### API Base URL

All API requests should be directed to:

```
https://your-domain.com/api/v1/{endpoint}
```

Replace `your-domain.com` with your actual PremiumBox installation domain and `{endpoint}` with the specific method name.

### Request Requirements

All API requests must satisfy the following requirements:

| Requirement | Value |
|-------------|-------|
| HTTP Method | POST |
| Content-Type | application/x-www-form-urlencoded or application/json |
| Character Encoding | UTF-8 |
| Authentication Headers | API-Login and API-Key must be present |

## Response Format

### Standard Response Structure

All API responses follow a consistent JSON structure:

| Field | Type | Description |
|-------|------|-------------|
| `error` | String/Integer | Error code - "0" indicates success, non-zero indicates error |
| `error_text` | String | Human-readable error message (empty on success) |
| `data` | Object/Array | Response data payload (varies by endpoint) |
| `error_fields` | Array | Field-specific validation errors (for transaction creation) |

### Success Response Example

```json
{
  "error": "0",
  "error_text": "",
  "data": {
    // Endpoint-specific data
  }
}
```

### Error Response Example

```json
{
  "error": "1",
  "error_text": "Direction not found",
  "data": {}
}
```

### Common Error Codes

| Error Code | Meaning | Typical Scenarios |
|------------|---------|-------------------|
| 0 | Success | Request completed successfully |
| 1 | Resource Not Found | Direction, transaction, or currency not found |
| 2 | Access Denied | Maintenance mode, IP blocked, or authentication failure |
| 3 | Validation Error | Invalid parameters or field validation failures |

## Available API Endpoints

### Endpoint Categories

The API provides endpoints organized into the following functional categories:

**Exchange Information:**
- `get_direction_currencies` - Retrieve available currencies
- `get_directions` - List all exchange directions
- `get_direction` - Get specific direction details
- `get_exchanges` - Get exchange history

**Calculations:**
- `get_calc` - Calculate exchange amounts

**Transaction Management:**
- `create_bid` - Create new transaction
- `bid_info` - Get transaction information
- `cancel_bid` - Cancel transaction
- `pay_bid` - Confirm payment
- `success_bid` - Mark as successful

**Partner Program:**
- `get_partner_info` - Get partner information
- `get_partner_links` - Get referral links
- `get_partner_exchanges` - Get referred exchanges
- `get_partner_payouts` - Get payout history
- `add_partner_payout` - Request payout

**Testing:**
- `test` - Test API connectivity

## Quick Start Examples

### Example 1: Test API Connection

```php
<?php
$api_url = 'https://your-domain.com/api/v1/test';
$api_login = 'your_api_login_here';
$api_key = 'your_api_key_here';

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'API-Login: ' . $api_login,
    'API-Key: ' . $api_key,
    'API-Lang: en'
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
?>
```

### Example 2: Get Available Currencies

```php
<?php
$api_url = 'https://your-domain.com/api/v1/get_direction_currencies';
$api_login = 'your_api_login_here';
$api_key = 'your_api_key_here';

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'API-Login: ' . $api_login,
    'API-Key: ' . $api_key
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['error'] == '0') {
    echo "Available source currencies:\n";
    foreach ($result['data']['give'] as $currency) {
        echo "- {$currency['title']} (ID: {$currency['id']})\n";
    }
}
?>
```

### Example 3: Calculate Exchange Rate

```php
<?php
$api_url = 'https://your-domain.com/api/v1/get_calc';
$api_login = 'your_api_login_here';
$api_key = 'your_api_key_here';

$params = [
    'direction_id' => 1,
    'calc_amount' => 100,
    'calc_action' => 1  // 1 = calculate from send amount
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'API-Login: ' . $api_login,
    'API-Key: ' . $api_key
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['error'] == '0') {
    $data = $result['data'];
    echo "You send: {$data['sum_give']} {$data['currency_code_give']}\n";
    echo "You get: {$data['sum_get']} {$data['currency_code_get']}\n";
    echo "Exchange rate: {$data['course_give']}\n";
}
?>
```

### Example 4: Create Transaction

```php
<?php
$api_url = 'https://your-domain.com/api/v1/create_bid';
$api_login = 'your_api_login_here';
$api_key = 'your_api_key_here';

$params = [
    'direction_id' => 1,
    'calc_amount' => 100,
    'calc_action' => 1,
    'account_give' => 'sender_account_number',
    'account_get' => 'recipient_account_number',
    'user_email' => 'user@example.com',
    'callback_url' => 'https://your-site.com/api/callback'
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'API-Login: ' . $api_login,
    'API-Key: ' . $api_key
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['error'] == '0') {
    echo "Transaction created successfully!\n";
    echo "Transaction ID: {$result['data']['id']}\n";
    echo "Transaction URL: {$result['data']['url']}\n";
    echo "Status: {$result['data']['status']}\n";
    echo "Payment instruction:\n{$result['data']['api_actions']['instruction']}\n";
} else {
    echo "Error: {$result['error_text']}\n";
    if (!empty($result['error_fields'])) {
        print_r($result['error_fields']);
    }
}
?>
```

### Example 5: Check Transaction Status

```php
<?php
$api_url = 'https://your-domain.com/api/v1/bid_info';
$api_login = 'your_api_login_here';
$api_key = 'your_api_key_here';

$params = [
    'id' => 12345  // Or use 'hash' => 'transaction_hash'
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'API-Login: ' . $api_login,
    'API-Key: ' . $api_key
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['error'] == '0') {
    $data = $result['data'];
    echo "Transaction Status: {$data['status_title']}\n";
    echo "Amount to send: {$data['amount_give']} {$data['currency_code_give']}\n";
    echo "Amount to receive: {$data['amount_get']} {$data['currency_code_get']}\n";
}
?>
```

## Integration Best Practices

### Security Recommendations

**Credential Management:**
- Store API credentials in secure environment variables or encrypted configuration
- Never expose API keys in client-side code or public repositories
- Rotate API keys periodically
- Use separate API keys for different applications or environments

**Network Security:**
- Always use HTTPS for API requests
- Implement IP whitelisting when possible
- Monitor API access logs for suspicious activity
- Implement rate limiting in client applications

**Error Handling:**
- Implement proper error handling for all API responses
- Log errors for debugging but sanitize sensitive information
- Provide user-friendly error messages in applications
- Handle network timeouts and connection failures gracefully

### Performance Optimization

**Request Efficiency:**
- Cache currency and direction data when appropriate
- Minimize redundant API calls by storing frequently accessed data locally
- Implement request queuing for bulk operations
- Use pagination for large datasets

**Response Handling:**
- Parse JSON responses efficiently
- Validate response structure before processing
- Implement timeout handling for long-running requests
- Consider asynchronous processing for non-blocking operations

### Callback Implementation

**Transaction Status Callbacks:**

When creating transactions, provide a `callback_url` to receive automated status updates.

**Callback Handling:**
- Implement endpoint to receive callback POST requests
- Validate callback authenticity (verify transaction hash)
- Update local transaction status based on callback data
- Return success response to acknowledge callback receipt
- Implement retry logic for failed callback processing

## Troubleshooting

### Common Issues and Solutions

**Authentication Failures:**

| Problem | Cause | Solution |
|---------|-------|----------|
| No response / generic error | Invalid credentials | Verify API-Login and API-Key are correct |
| Error 2: IP blocked | IP not whitelisted | Add client IP to allowed IPs in API settings |
| Error 3: Method not supported | Endpoint not enabled | Enable required methods in API key configuration |

**Transaction Creation Errors:**

| Problem | Cause | Solution |
|---------|-------|----------|
| Error 1: Direction not found | Invalid direction_id | Verify direction exists and is active |
| Error 3 with error_fields | Validation failure | Check error_fields for specific field errors |
| Error 2: Maintenance | System maintenance | Wait and retry, check system status |

**Calculation Issues:**

| Problem | Cause | Solution |
|---------|-------|----------|
| Unexpected amounts | Min/max limit adjustment | Check `changed` flag in response |
| Error 1: Direction not found | Direction inactive or deleted | Refresh direction list |
| Incorrect calculations | Invalid calc_action | Use 1 for give, 2 for get |

### Debug Checklist

When experiencing API issues:

1. Verify authentication headers are present and correct
2. Confirm request method is POST
3. Check request parameters match endpoint requirements
4. Validate JSON response parsing
5. Review error code and error_text
6. Check API access logs if available
7. Verify network connectivity and DNS resolution
8. Test with `test` endpoint to isolate authentication issues
9. Confirm direction/currency IDs are valid and active
10. Review callback URL configuration if using callbacks

## API Reference

For detailed information about each endpoint, including request parameters, response structures, and use cases, please refer to the complete API documentation at:

https://your-domain.com/api-docs

Or contact technical support for assistance.

## Appendix

### Transaction Statuses

| Status | Description |
|--------|-------------|
| `new` | Transaction created, awaiting payment |
| `techpay` | Technical payment processing |
| `coldpay` | Payment in cold storage processing |
| `payed` | Payment received, processing exchange |
| `success` | Transaction completed successfully |
| `cancel` | Transaction cancelled |
| `refund` | Payment refunded |

### Glossary

| Term | Definition |
|------|------------|
| Direction | An exchange pair defining source and destination currencies with associated rates and limits |
| Bid | A transaction or exchange order |
| Give | Source currency/amount (what user sends) |
| Get | Destination currency/amount (what user receives) |
| Course | Exchange rate |
| Reserve | Available balance for a currency |
| Commission | Fee charged for exchange operation |
| Hash | Unique transaction identifier string |
| Merchant | Payment system or gateway |
| API-Login | Unique identifier for API access |
| API-Key | Secret authentication credential |

---

**Version:** 1.0  
**Last Updated:** November 2025  
**Support:** For technical support, contact your platform administrator
