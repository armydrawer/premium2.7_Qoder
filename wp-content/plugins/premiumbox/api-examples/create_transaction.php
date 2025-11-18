<?php
/**
 * Create Exchange Transaction
 * 
 * This example demonstrates how to create a new exchange transaction
 * with proper validation and error handling.
 */

require_once 'config.php';

/**
 * Create Exchange Transaction
 * 
 * @param int $direction_id Direction ID
 * @param float $amount Transaction amount
 * @param int $calc_action 1 = give amount, 2 = get amount
 * @param array $fields Additional required fields
 * @return array Transaction result
 */
function create_transaction($direction_id, $amount, $calc_action, $fields = []) {
    // Prepare parameters
    $params = array_merge([
        'direction_id' => $direction_id,
        'calc_amount' => $amount,
        'calc_action' => $calc_action,
        'callback_url' => CALLBACK_URL
    ], $fields);
    
    // Make API request
    $response = api_request('create_bid', $params);
    
    // Log the request (without sensitive data)
    log_api_request('create_bid', array_merge($params, [
        'account_give' => '***',
        'account_get' => '***'
    ]), $response);
    
    return $response;
}

// Example Usage
echo "Creating Exchange Transaction...\n";
echo str_repeat("-", 50) . "\n\n";

// Step 1: Get direction details first (recommended)
echo "Step 1: Getting direction details...\n";
$direction_response = api_request('get_direction', [
    'direction_id' => 1  // Replace with your direction ID
]);

if ($direction_response['error'] != '0') {
    echo "Error getting direction: {$direction_response['error_text']}\n";
    exit;
}

$direction = $direction_response['data'];
echo "Direction: {$direction['currency_code_give']} → {$direction['currency_code_get']}\n";
echo "Min amount: {$direction['min_give']} {$direction['currency_code_give']}\n";
echo "Max amount: {$direction['max_give']} {$direction['currency_code_give']}\n\n";

// Step 2: Calculate exchange
echo "Step 2: Calculating exchange...\n";
$calc_response = api_request('get_calc', [
    'direction_id' => 1,
    'calc_amount' => 100,
    'calc_action' => 1
]);

if ($calc_response['error'] != '0') {
    echo "Error calculating: {$calc_response['error_text']}\n";
    exit;
}

$calc = $calc_response['data'];
echo "You send: {$calc['sum_give_com']} {$calc['currency_code_give']}\n";
echo "You get: {$calc['sum_get_com']} {$calc['currency_code_get']}\n";
echo "Rate: {$calc['course_give']}\n\n";

// Step 3: Create transaction
echo "Step 3: Creating transaction...\n";

$transaction = create_transaction(
    1,      // direction_id
    100,    // amount
    1,      // calc_action (1 = give)
    [
        // Add required fields based on direction
        'account_give' => 'sender_account_number',
        'account_get' => 'recipient_account_number',
        'user_email' => 'user@example.com',
        'user_phone' => '+1234567890',
        'first_name' => 'John',
        'last_name' => 'Doe'
        // Add more fields as required by the direction
    ]
);

// Display results
if ($transaction['error'] == '0') {
    $data = $transaction['data'];
    
    echo "✓ Transaction created successfully!\n\n";
    echo "Transaction Details:\n";
    echo "- ID: {$data['id']}\n";
    echo "- Hash: {$data['hash']}\n";
    echo "- Status: {$data['status_title']}\n";
    echo "- URL: {$data['url']}\n\n";
    
    echo "Payment Information:\n";
    echo "- Amount to send: {$data['amount_give']} {$data['currency_code_give']}\n";
    echo "- Amount to receive: {$data['amount_get']} {$data['currency_code_get']}\n\n";
    
    echo "Next Steps:\n";
    echo $data['api_actions']['instruction'] . "\n\n";
    
    // Save transaction ID for later use
    $transaction_file = __DIR__ . '/last_transaction.txt';
    file_put_contents($transaction_file, json_encode([
        'id' => $data['id'],
        'hash' => $data['hash'],
        'created_at' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT));
    
} elseif ($transaction['error'] == '3') {
    echo "✗ Validation failed!\n\n";
    echo "Error: {$transaction['error_text']}\n\n";
    
    if (!empty($transaction['error_fields'])) {
        echo "Field Errors:\n";
        foreach ($transaction['error_fields'] as $field => $error) {
            echo "- {$field}: {$error}\n";
        }
    }
    
} else {
    echo "✗ Transaction failed!\n\n";
    echo "Error Code: {$transaction['error']}\n";
    echo "Error Message: {$transaction['error_text']}\n";
}

echo "\n" . str_repeat("-", 50) . "\n";
