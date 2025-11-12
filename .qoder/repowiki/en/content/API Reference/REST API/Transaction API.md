# Transaction API

<cite>
**Referenced Files in This Document**   
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php)
- [class.php](file://wp-content/plugins/premiumbox/merchants/exnode/class.php)
- [index.php](file://wp-content/plugins/premiumbox/paymerchants/exnode/index.php)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [API Endpoints](#api-endpoints)
3. [Transaction Creation](#transaction-creation)
4. [Transaction Status Retrieval](#transaction-status-retrieval)
5. [Transaction Management](#transaction-management)
6. [Response Structure](#response-structure)
7. [Error Handling](#error-handling)
8. [Security Considerations](#security-considerations)

## Introduction
The Transaction API provides comprehensive functionality for managing exchange transactions within the Premium Exchanger platform. This API enables users to create, track, and manage transactions between different currencies through various payment systems. The system supports both incoming and outgoing transactions with detailed status tracking and callback mechanisms.

The API is designed to facilitate seamless integration with external services and payment processors, allowing for automated transaction processing, status updates, and reconciliation. Key features include transaction creation with customizable parameters, real-time status tracking, and comprehensive error handling for various transaction scenarios.

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L1-L800)

## API Endpoints
The Transaction API provides a comprehensive set of endpoints for managing exchange transactions. These endpoints follow a consistent pattern and are accessible through the platform's API system.

### Base URL
All API endpoints are accessed through the platform's API interface with the following base pattern:
```
POST /wp-content/plugins/premiumbox/moduls/api/user_api.php
```

### Available Endpoints
The following endpoints are available for transaction management:

| Endpoint | HTTP Method | Description |
|---------|------------|-------------|
| create_bid | POST | Create a new exchange transaction |
| bid_info | POST | Retrieve information about a specific transaction |
| cancel_bid | POST | Cancel an existing transaction |
| pay_bid | POST | Mark a transaction as paid |
| success_bid | POST | Mark a transaction as successfully completed |
| get_calc | POST | Calculate exchange rates and fees |

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L1-L800)

## Transaction Creation
The transaction creation process allows users to initiate exchange transactions between different currencies with specified parameters.

### Request Parameters
To create a transaction, the following parameters must be provided:

| Parameter | Type | Required | Description |
|---------|------|----------|-------------|
| api_login | string | Yes | Authentication identifier for API access |
| calc_amount | float | Yes | Amount to exchange |
| calc_action | integer | Yes | Direction of calculation (1: give amount, 2: get amount) |
| callback_url | string | No | URL to receive status updates |

### Example Request
```json
{
  "api_login": "your_api_key",
  "calc_amount": 100.00,
  "calc_action": 1,
  "callback_url": "https://yourdomain.com/transaction-callback"
}
```

### Response
Upon successful transaction creation, the API returns transaction details including the transaction ID, hash, and status.

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L404-L486)

## Transaction Status Retrieval
The API provides functionality to retrieve the current status of transactions by ID or hash.

### Request Parameters
To retrieve transaction status, provide one of the following identifiers:

| Parameter | Type | Required | Description |
|---------|------|----------|-------------|
| id | integer | Conditional | Transaction ID |
| hash | string | Conditional | Transaction hash |
| api_login | string | Yes | Authentication identifier for API access |

### Example Request
```json
{
  "api_login": "your_api_key",
  "id": 12345
}
```

### Response Structure
The response includes comprehensive transaction information:

| Field | Description |
|------|-------------|
| url | Public URL for the transaction |
| id | Transaction ID |
| hash | Transaction hash identifier |
| status | Current transaction status |
| status_title | Human-readable status description |
| psys_give | Payment system for outgoing funds |
| psys_get | Payment system for incoming funds |
| currency_code_give | Currency code for outgoing funds |
| currency_code_get | Currency code for incoming funds |
| amount_give | Amount to be sent |
| amount_get | Amount to be received |
| course_give | Exchange rate for outgoing currency |
| course_get | Exchange rate for incoming currency |

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L489-L578)

## Transaction Management
The API provides endpoints for managing the lifecycle of transactions, including cancellation, payment confirmation, and completion.

### Available Actions
#### Cancel Transaction
Marks a transaction as cancelled.

**Endpoint**: `cancel_bid`

**Parameters**:
- id or hash: Transaction identifier
- api_login: API authentication key

#### Mark as Paid
Updates transaction status to indicate payment has been made.

**Endpoint**: `pay_bid`

**Parameters**:
- id or hash: Transaction identifier
- api_login: API authentication key

#### Mark as Success
Completes the transaction process.

**Endpoint**: `success_bid`

**Parameters**:
- id or hash: Transaction identifier
- api_login: API authentication key

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L581-L800)

## Response Structure
All API responses follow a consistent structure with standardized fields for error handling and data transmission.

### Standard Response Format
```json
{
  "error": "0",
  "error_text": "",
  "data": {
    // Transaction-specific data
  }
}
```

### Error Response Format
```json
{
  "error": "1",
  "error_text": "Error description",
  "error_fields": [],
  "data": {}
}
```

### Error Codes
| Code | Description |
|------|-------------|
| 0 | Success |
| 1 | General error or resource not found |
| 2 | System maintenance or disabled |
| 3 | Validation error |

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L471-L486)

## Error Handling
The API implements comprehensive error handling to address various transaction scenarios and potential issues.

### Common Error Scenarios
#### System Maintenance
When the system is under maintenance or the API is disabled, requests return error code 2 with appropriate messaging.

#### Invalid Transaction
Attempts to access non-existent transactions return error code 1 with "no bid exists" message.

#### Validation Errors
Invalid parameters or business rule violations return error code 3 with specific error details.

#### Action Restrictions
Certain actions may be disabled based on transaction status or system configuration, returning appropriate error messages.

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L471-L486)

## Security Considerations
The Transaction API implements several security measures to protect transaction integrity and prevent abuse.

### Authentication
All API requests require authentication via the `api_login` parameter, which serves as an API key for access control.

### Duplicate Submission Protection
The system prevents duplicate transaction creation through unique transaction identifiers and hash validation.

### Data Validation
All input parameters are validated for type, format, and business logic compliance to prevent invalid transactions.

### Secure Data Handling
Transaction data is stored securely with appropriate access controls and is transmitted over secure connections.

### Callback Security
Callback URLs are validated and can be configured to receive transaction status updates securely.

**Section sources**
- [methods.php](file://wp-content/plugins/premiumbox/moduls/api/methods.php#L404-L800)