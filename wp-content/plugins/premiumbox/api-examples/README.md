# PremiumBox API Code Examples

This directory contains practical code examples for integrating with the PremiumBox API in various programming languages.

## Available Examples

- **PHP** - Complete PHP integration examples
- **Python** - Python integration using requests library
- **JavaScript** - Node.js integration examples
- **cURL** - Command-line examples for testing

## Quick Reference

### Authentication Headers

All requests require these headers:

```
API-Login: your_api_login_32_characters
API-Key: your_api_key_here
API-Lang: en (optional)
```

### Base URL

```
https://your-domain.com/api/v1/{endpoint}
```

### HTTP Method

All API requests use **POST** method.

## Examples by Use Case

### 1. Testing Connection
- `test_connection.php`
- `test_connection.py`
- `test_connection.js`

### 2. Getting Exchange Information
- `get_currencies.php`
- `get_directions.php`
- `get_direction_details.php`

### 3. Calculating Exchange
- `calculate_exchange.php`
- `calculate_exchange.py`

### 4. Creating Transactions
- `create_transaction.php`
- `create_transaction_with_validation.php`

### 5. Managing Transactions
- `check_transaction_status.php`
- `cancel_transaction.php`
- `transaction_lifecycle.php`

### 6. Callback Handling
- `callback_handler.php`
- `callback_handler.py`

## Setup Instructions

1. Copy example files to your project
2. Update configuration with your API credentials
3. Replace `your-domain.com` with your actual domain
4. Test with the `test_connection` example first
5. Review error handling in each example

## Configuration

Create a config file with your credentials:

```php
<?php
// config.php
define('API_BASE_URL', 'https://your-domain.com/api/v1/');
define('API_LOGIN', 'your_api_login_here');
define('API_KEY', 'your_api_key_here');
define('API_LANG', 'en');
?>
```

## Support

For questions or issues:
- Review the main API documentation
- Check error codes and messages
- Test with the `test` endpoint
- Contact technical support

## License

These examples are provided as-is for integration purposes.
