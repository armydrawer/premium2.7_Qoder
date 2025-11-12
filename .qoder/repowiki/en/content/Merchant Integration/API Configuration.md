# API Configuration

<cite>
**Referenced Files in This Document**   
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php)
- [merchant001/index.php](file://wp-content/plugins/premiumbox/merchants/merchant001/index.php)
- [aipay/index.php](file://wp-content/plugins/premiumbox/paymerchants/aipay/index.php)
- [aipay/class.php](file://wp-content/plugins/premiumbox/merchants/aipay/class.php)
- [optimoney/class.php](file://wp-content/plugins/premiumbox/merchants/optimoney/class.php)
- [supermoney/class.php](file://wp-content/plugins/premiumbox/merchants/supermoney/class.php)
- [advcash/class.php](file://wp-content/plugins/premiumbox/merchants/advcash/class.php)
- [advcash/index.php](file://wp-content/plugins/premiumbox/paymerchants/advcash/index.php)
- [paymerch_func.php](file://wp-content/plugins/premiumbox/plugin/merchants/paymerch_func.php)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [API Abstraction Layer Architecture](#api-abstraction-layer-architecture)
3. [Core Interface Contracts](#core-interface-contracts)
4. [Authentication Mechanisms](#authentication-mechanisms)
5. [Merchant Implementation Patterns](#merchant-implementation-patterns)
6. [Transaction Lifecycle Management](#transaction-lifecycle-management)
7. [Error Handling and Rate Limiting](#error-handling-and-rate-limiting)
8. [Best Practices for API Integration](#best-practices-for-api-integration)
9. [Conclusion](#conclusion)

## Introduction

The merchant integration system provides a standardized API abstraction layer for communicating with diverse payment providers. This documentation details the implementation of the API configuration system, focusing on the interface contracts, authentication methods, and transaction management patterns used across different merchant implementations. The architecture enables seamless integration with various payment providers while maintaining a consistent interface for balance checking, transaction initiation, and status verification.

## API Abstraction Layer Architecture

The API abstraction layer standardizes communication with payment providers through a consistent interface while accommodating the specific requirements of each merchant. The architecture follows a plugin-based design where each merchant implements a standardized set of methods while handling provider-specific authentication and data formatting.

```mermaid
graph TB
subgraph "Core Plugin Architecture"
API[API Abstraction Layer]
Config[Configuration Manager]
ErrorHandler[Error Handler]
Logger[Transaction Logger]
end
subgraph "Merchant Implementations"
Merchant001[Merchant001]
AIpay[AI-pay]
Optimoney[Optimoney]
Supermoney[Supermoney]
Advcash[Advcash]
end
API --> Config
API --> ErrorHandler
API --> Logger
Merchant001 --> API
AIpay --> API
Optimoney --> API
Supermoney --> API
Advcash --> API
style API fill:#4CAF50,stroke:#388E3C
style Merchant001 fill:#2196F3,stroke:#1976D2
style AIpay fill:#2196F3,stroke:#1976D2
style Optimoney fill:#2196F3,stroke:#1976D2
style Supermoney fill:#2196F3,stroke:#1976D2
style Advcash fill:#2196F3,stroke:#1976D2
```

**Diagram sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L1-L99)
- [aipay/class.php](file://wp-content/plugins/premiumbox/merchants/aipay/class.php#L1-L206)
- [supermoney/class.php](file://wp-content/plugins/premiumbox/merchants/supermoney/class.php#L1-L143)

**Section sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L1-L99)
- [aipay/class.php](file://wp-content/plugins/premiumbox/merchants/aipay/class.php#L1-L206)

## Core Interface Contracts

All merchant classes implement standardized interface contracts that define the essential methods for payment processing. These contracts ensure consistency across different payment providers while allowing for provider-specific implementations.

### Standardized Method Contracts

The following table outlines the core interface contracts that all merchant classes must implement:

| Method | Parameters | Return Value | Purpose | Merchant Examples |
|--------|------------|--------------|---------|-------------------|
| payment_methods() | None | Array of available payment methods | Retrieves available payment methods from the provider | merchant001, aipay, optimoney |
| create_transaction() / create_tx() | Transaction data array | Transaction response object | Initiates a new transaction with the payment provider | merchant001, optimoney, supermoney |
| get_transaction() / get_tx() | Transaction ID | Transaction details object | Retrieves the status and details of a specific transaction | merchant001, optimoney, aipay |
| get_transactions() / get_txs() | Filter parameters | Array of transaction objects | Retrieves multiple transactions with optional filtering | optimoney, supermoney, advcash |
| balance() / get_balance() | None | Balance information object | Retrieves current account balance from the provider | aipay, advcash |

```mermaid
classDiagram
class MerchantInterface {
<<interface>>
+payment_methods() Array
+create_transaction(data) Object
+get_transaction(id) Object
+get_transactions(data) Array
+balance() Object
}
class Merchant001 {
-m_name : string
-m_id : string
-base_url : string
-token : string
+payment_methods() Array
+create_transaction(data) Object
+get_transaction(id) Object
+get_requisites(id) Object
-request(method, data, is_post) Object
}
class Optimoney {
-cfg : array
+get_mid() string
+create_tx(data) string
+get_tx(id) Object
+get_txs(data) Object
-_request(method, path, options) Object
-sign_form(data) string
}
class Supermoney {
-m_name : string
-m_id : string
-base_url : string
-token : string
-sign_secret : string
+transaction_card(data) Object
+transaction_sbp(data) Object
+transaction_account(data) Object
+get_transaction(id) Object
+get_transactions(data) Array
+banks(data) Object
-get_sign(url, request_json, secret, is_post) string
-request(method, data, is_post) Object
}
MerchantInterface <|-- Merchant001
MerchantInterface <|-- Optimoney
MerchantInterface <|-- Supermoney
```

**Diagram sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L1-L99)
- [optimoney/class.php](file://wp-content/plugins/premiumbox/merchants/optimoney/class.php#L1-L206)
- [supermoney/class.php](file://wp-content/plugins/premiumbox/merchants/supermoney/class.php#L1-L143)

**Section sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L1-L99)
- [optimoney/class.php](file://wp-content/plugins/premiumbox/merchants/optimoney/class.php#L1-L206)
- [supermoney/class.php](file://wp-content/plugins/premiumbox/merchants/supermoney/class.php#L1-L143)

## Authentication Mechanisms

The system supports multiple authentication mechanisms to accommodate different payment provider requirements. Each merchant implementation handles its specific authentication method while maintaining a consistent interface.

### API Key Authentication

The Merchant001 implementation uses Bearer token authentication with API keys:

```mermaid
sequenceDiagram
participant System as Payment System
participant Merchant as Merchant001
participant Provider as Payment Provider
System->>Merchant : Initialize with API token
Merchant->>Merchant : Store token in class property
Merchant->>Provider : Send request with Authorization header
Provider->>Merchant : Validate token and process request
Merchant->>System : Return response
```

**Diagram sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L47-L50)

### HMAC Signature Authentication

The Optimoney implementation uses HMAC-SHA256 signatures for request authentication:

```mermaid
sequenceDiagram
participant System as Payment System
participant Merchant as Optimoney
participant Provider as Payment Provider
System->>Merchant : Request with data
Merchant->>Merchant : Create signature string from method, URL, timestamp, API key, and body
Merchant->>Merchant : Generate HMAC-SHA256 signature using secret key
Merchant->>Provider : Send request with x-access-key, x-signature-timestamp, and x-signature headers
Provider->>Provider : Validate signature
Provider->>Merchant : Process request and return response
Merchant->>System : Return response
```

**Diagram sources**
- [optimoney/class.php](file://wp-content/plugins/premiumbox/merchants/optimoney/class.php#L123-L133)

### Multiple Credential Authentication

The Supermoney implementation uses both Bearer token and HMAC signature authentication:

```mermaid
sequenceDiagram
participant System as Payment System
participant Merchant as Supermoney
participant Provider as Payment Provider
System->>Merchant : Initialize with token and sign_secret
Merchant->>Merchant : Store credentials in class properties
Merchant->>Provider : Send request with Authorization : Bearer token header
Merchant->>Provider : Include X-Signature header with HMAC of URL path and query parameters
Provider->>Provider : Validate both token and signature
Provider->>Merchant : Process request and return response
Merchant->>System : Return response
```

**Diagram sources**
- [supermoney/class.php](file://wp-content/plugins/premiumbox/merchants/supermoney/class.php#L89-L100)

### WMID Signature Authentication

The Advcash implementation uses WMID-style signature authentication with API password tokens:

```mermaid
sequenceDiagram
participant System as Payment System
participant Merchant as Advcash
participant Provider as Payment Provider
System->>Merchant : Initialize with API_NAME, ACCOUNT_EMAIL, API_PASSWORD
Merchant->>Provider : Request authentication token using API credentials
Provider->>Merchant : Return authentication token
Merchant->>Provider : Use token in subsequent requests
Provider->>Merchant : Validate token and process request
Merchant->>System : Return response
```

**Diagram sources**
- [advcash/class.php](file://wp-content/plugins/premiumbox/merchants/advcash/class.php#L1677-L1755)

## Merchant Implementation Patterns

Different merchant implementations follow specific patterns based on their API requirements and capabilities. These patterns demonstrate how the core abstraction layer accommodates diverse payment provider interfaces.

### REST API Pattern (Merchant001)

The Merchant001 implementation follows a standard REST API pattern with JSON requests and responses:

```mermaid
flowchart TD
Start([Initialize Merchant001]) --> SetCredentials["Set m_name, m_id, token"]
SetCredentials --> DefineMethods["Define payment_methods(), create_transaction(), get_transaction()"]
DefineMethods --> ImplementRequest["Implement private request() method"]
ImplementRequest --> AddHeaders["Add Authorization: Bearer token header"]
AddHeaders --> HandleResponse["Parse JSON response"]
HandleResponse --> ErrorLogging["Log errors via save_merchant_error action"]
ErrorLogging --> End([Ready for use])
```

**Diagram sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L1-L99)

### Form-Based Payment Pattern (Optimoney)

The Optimoney implementation uses form-based payments with server-side signing:

```mermaid
flowchart TD
Start([Create Transaction]) --> PrepareData["Prepare transaction data"]
PrepareData --> SignData["Sign data with M_SECRET_KEY using HMAC-SHA256"]
SignData --> CreateForm["Create HTML form with hidden inputs"]
CreateForm --> AddSignature["Add signature to form data"]
AddSignature --> ReturnForm["Return complete HTML form"]
ReturnForm --> UserSubmit["User submits form to payment provider"]
UserSubmit --> End([Payment processing begins])
```

**Diagram sources**
- [optimoney/class.php](file://wp-content/plugins/premiumbox/merchants/optimoney/class.php#L59-L83)

### Multi-Endpoint Pattern (Supermoney)

The Supermoney implementation provides specialized methods for different transaction types:

```mermaid
classDiagram
class Supermoney {
+transaction_card(data) Object
+transaction_sbp(data) Object
+transaction_account(data) Object
+get_transaction(id) Object
+get_transactions(data) Array
+banks(data) Object
}
transaction_card --> /v2/merchant/transactions
transaction_sbp --> /v2/merchant/transactions/sbp
transaction_account --> /v2/merchant/transactions/account
get_transaction --> /v2/merchant/transactions/{id}
get_transactions --> /v2/merchant/transactions
banks --> /v2/merchant/banks
```

**Diagram sources**
- [supermoney/class.php](file://wp-content/plugins/premiumbox/merchants/supermoney/class.php#L28-L67)

## Transaction Lifecycle Management

The system manages the complete transaction lifecycle from initiation to status verification, with different patterns for synchronous and asynchronous processing.

### Synchronous Transaction Flow

For merchants that support immediate transaction processing:

```mermaid
sequenceDiagram
participant User as User
participant System as Payment System
participant Merchant as Merchant Class
participant Provider as Payment Provider
User->>System : Request payment
System->>Merchant : Call create_transaction() with payment details
Merchant->>Provider : Send transaction request
Provider->>Provider : Process transaction immediately
Provider->>Merchant : Return transaction status
Merchant->>System : Return transaction result
System->>User : Display payment result
```

**Diagram sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L28-L31)

### Asynchronous Transaction Flow

For merchants that use callback-based processing:

```mermaid
sequenceDiagram
participant User as User
participant System as Payment System
participant Merchant as Merchant Class
participant Provider as Payment Provider
User->>System : Request payment
System->>Merchant : Call create_transaction() with payment details
Merchant->>Provider : Send transaction request
Provider->>Merchant : Return transaction ID
Merchant->>System : Return transaction ID
System->>User : Display payment instructions
Provider->>System : Send callback to webhook URL
System->>Merchant : Route to _webhook_url() method
Merchant->>Provider : Verify transaction status
Provider->>Merchant : Return transaction details
Merchant->>System : Update transaction status
System->>User : Notify of payment completion
```

**Diagram sources**
- [aipay/index.php](file://wp-content/plugins/premiumbox/paymerchants/aipay/index.php#L253-L278)

### Status Verification Flow

The system periodically verifies transaction status for reliability:

```mermaid
flowchart TD
Start([Cron Job Starts]) --> GetPending["Get pending transactions"]
GetPending --> Loop["For each pending transaction"]
Loop --> CallAPI["Call get_transaction() or get_txs()"]
CallAPI --> CheckStatus["Check transaction status"]
CheckStatus --> IsComplete{"Transaction complete?"}
IsComplete --> |Yes| UpdateStatus["Update system status"]
IsComplete --> |No| Continue["Continue to next transaction"]
UpdateStatus --> Next["Process next transaction"]
Continue --> Next
Next --> MoreTransactions{"More transactions?"}
MoreTransactions --> |Yes| Loop
MoreTransactions --> |No| End([Cron job completes])
```

**Diagram sources**
- [aipay/index.php](file://wp-content/plugins/premiumbox/paymerchants/aipay/index.php#L280-L284)

## Error Handling and Rate Limiting

The system implements comprehensive error handling and rate limiting strategies to ensure reliability and prevent service disruption.

### Standardized Error Handling

All merchant implementations follow a consistent error handling pattern:

```mermaid
flowchart TD
Start([API Request]) --> TryBlock["Try block around API call"]
TryBlock --> MakeRequest["Make HTTP request to payment provider"]
MakeRequest --> CheckResponse["Check response status"]
CheckResponse --> HasError{"Error occurred?"}
HasError --> |Yes| CatchBlock["Catch Exception"]
HasError --> |No| ReturnSuccess["Return successful response"]
CatchBlock --> LogError["Log error details"]
LogError --> SetErrorStatus["Set appropriate error status"]
SetErrorStatus --> ReturnError["Return error to system"]
ReturnSuccess --> End([Request completed])
ReturnError --> End
```

**Diagram sources**
- [aipay/index.php](file://wp-content/plugins/premiumbox/paymerchants/aipay/index.php#L187-L221)

### Rate Limiting Implementation

The system handles API rate limiting through WordPress's built-in HTTP API and custom error handling:

```mermaid
flowchart TD
Start([API Request]) --> CheckRateLimit["Check for 429 Too Many Requests"]
CheckRateLimit --> Is429{"Response is 429?"}
Is429 --> |Yes| HandleRateLimit["Implement exponential backoff"]
Is429 --> |No| CheckOtherErrors["Check for other API errors"]
HandleRateLimit --> Wait["Wait with exponential backoff"]
Wait --> Retry["Retry request"]
Retry --> CheckRateLimit
CheckOtherErrors --> HandleError["Handle specific API error"]
HandleError --> End([Error processed])
```

**Diagram sources**
- [wp-includes/Requests/src/Exception/Http/Status429.php](file://wp-includes/Requests/src/Exception/Http/Status429.php#L1-L35)

### Common Error Types

The system handles various common API errors:

| Error Type | Handling Strategy | Example Merchants |
|------------|-------------------|-------------------|
| Authentication Failure | Retry with refreshed credentials or notify administrator | All merchants |
| Rate Limiting (429) | Implement exponential backoff and retry logic | All merchants |
| Connection Timeout | Retry with increased timeout or fail gracefully | All merchants |
| Invalid Parameters | Validate parameters before sending and provide clear error messages | All merchants |
| Insufficient Funds | Check balance before transaction and provide appropriate error | Advcash, Supermoney |
| Transaction Failure | Log detailed error and provide fallback options | All merchants |

## Best Practices for API Integration

Based on the analysis of multiple merchant implementations, the following best practices ensure reliable and maintainable API integrations.

### Configuration Management

Store API credentials securely and provide clear configuration interfaces:

```mermaid
flowchart TD
Start([Define Configuration Map]) --> MapFields["Map configuration fields to merchant requirements"]
MapFields --> HideSensitive["Hide sensitive fields like API keys"]
HideSensitive --> ProvideDefaults["Provide default values where possible"]
ProvideDefaults --> ValidateInput["Validate input before saving"]
ValidateInput --> StoreSecurely["Store credentials securely"]
StoreSecurely --> End([Configuration complete])
```

**Diagram sources**
- [aipay/index.php](file://wp-content/plugins/premiumbox/paymerchants/aipay/index.php#L34-L51)

### Secure Credential Handling

Implement secure handling of API credentials:

```mermaid
flowchart TD
Start([Initialize Merchant]) --> ValidateCredentials["Validate credentials format"]
ValidateCredentials --> TrimWhitespace["Trim whitespace from credentials"]
TrimWhitespace --> StoreLocally["Store credentials in class properties"]
StoreLocally --> UseInRequests["Use credentials in API requests"]
UseInRequests --> AvoidLogging["Ensure credentials are not logged"]
AvoidLogging --> End([Secure initialization complete])
```

**Diagram sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L15-L21)

### Comprehensive Logging

Implement detailed logging for debugging and monitoring:

```mermaid
flowchart TD
Start([API Request]) --> LogRequest["Log request URL, headers, and body"]
LogRequest --> MakeCall["Make API call"]
MakeCall --> LogResponse["Log response and status code"]
LogResponse --> CheckError["Check for errors"]
CheckError --> HasError{"Error occurred?"}
HasError --> |Yes| LogErrorDetails["Log detailed error information"]
HasError --> |No| Continue["Continue processing"]
LogErrorDetails --> NotifyAdmin["Notify administrator if critical"]
Continue --> End([Logging complete])
```

**Diagram sources**
- [merchant001/class.php](file://wp-content/plugins/premiumbox/merchants/merchant001/class.php#L88-L89)

### Fallback Mechanisms

Implement fallback mechanisms for improved reliability:

```mermaid
flowchart TD
Start([Transaction Request]) --> PrimaryMethod["Attempt primary payment method"]
PrimaryMethod --> Success{"Success?"}
Success --> |Yes| Complete["Transaction complete"]
Success --> |No| AlternativeMethod["Try alternative payment method"]
AlternativeMethod --> Success2{"Success?"}
Success2 --> |Yes| Complete
Success2 --> |No| ManualProcessing["Flag for manual processing"]
ManualProcessing --> NotifyAdmin["Notify administrator"]
Complete --> End([Transaction processed])
NotifyAdmin --> End
```

**Diagram sources**
- [paymerch_func.php](file://wp-content/plugins/premiumbox/plugin/merchants/paymerch_func.php#L844-L875)

## Conclusion

The API configuration system for merchant integrations provides a robust and flexible framework for connecting with diverse payment providers. By implementing standardized interface contracts, the system ensures consistency across different merchants while accommodating provider-specific authentication methods and API patterns. The architecture supports various authentication mechanisms including API keys, HMAC signatures, and multi-credential approaches, allowing integration with a wide range of payment providers.

Key strengths of the system include its comprehensive error handling, support for both synchronous and asynchronous transaction processing, and detailed logging capabilities. The implementation patterns demonstrated across different merchants provide valuable insights into best practices for API integration, including secure credential handling, configuration management, and fallback mechanisms.

To maintain reliability, administrators should monitor API rate limits, ensure credentials are kept up to date, and regularly review error logs. When integrating new payment providers, developers should follow the established patterns for method naming, error handling, and logging to ensure consistency with the existing system.