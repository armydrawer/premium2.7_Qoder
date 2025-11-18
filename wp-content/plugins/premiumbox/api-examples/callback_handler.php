<?php
/**
 * Transaction Status Callback Handler
 * 
 * This script receives and processes transaction status updates
 * from the PremiumBox API callback system.
 * 
 * Setup:
 * 1. Deploy this file to a publicly accessible URL
 * 2. Use that URL as the callback_url when creating transactions
 * 3. Ensure the file has write permissions for logging
 */

// Configuration
define('CALLBACK_LOG_FILE', __DIR__ . '/callback_logs.txt');
define('TRANSACTIONS_DB_FILE', __DIR__ . '/transactions.json');

/**
 * Log callback data
 */
function log_callback($data) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'data' => $data
    ];
    
    file_put_contents(
        CALLBACK_LOG_FILE,
        json_encode($log_entry, JSON_PRETTY_PRINT) . "\n\n",
        FILE_APPEND
    );
}

/**
 * Update transaction in local database
 */
function update_transaction($transaction_id, $status, $data) {
    // Load existing transactions
    $transactions = [];
    if (file_exists(TRANSACTIONS_DB_FILE)) {
        $transactions = json_decode(file_get_contents(TRANSACTIONS_DB_FILE), true) ?? [];
    }
    
    // Update or add transaction
    $transactions[$transaction_id] = [
        'id' => $transaction_id,
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s'),
        'data' => $data
    ];
    
    // Save transactions
    file_put_contents(
        TRANSACTIONS_DB_FILE,
        json_encode($transactions, JSON_PRETTY_PRINT)
    );
}

/**
 * Send notification (email, SMS, webhook, etc.)
 */
function send_notification($transaction_id, $status) {
    // Example: Send email notification
    // mail('admin@example.com', "Transaction {$transaction_id} status: {$status}", ...);
    
    // Example: Call webhook
    // $webhook_url = 'https://your-app.com/webhook';
    // $ch = curl_init($webhook_url);
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['transaction_id' => $transaction_id, 'status' => $status]));
    // curl_exec($ch);
    // curl_close($ch);
}

// Main callback handler
try {
    // Get callback data
    $raw_post = file_get_contents('php://input');
    $callback_data = $_POST;
    
    // Log the callback
    log_callback([
        'post' => $callback_data,
        'raw' => $raw_post
    ]);
    
    // Extract transaction information
    // Note: Actual callback format should be verified with platform documentation
    $transaction_id = $callback_data['transaction_id'] ?? $callback_data['id'] ?? null;
    $transaction_hash = $callback_data['transaction_hash'] ?? $callback_data['hash'] ?? null;
    $status = $callback_data['status'] ?? null;
    
    if (!$transaction_id && !$transaction_hash) {
        throw new Exception('Missing transaction identifier');
    }
    
    if (!$status) {
        throw new Exception('Missing transaction status');
    }
    
    // Validate callback authenticity (recommended)
    // Example: Verify signature if provided
    // $signature = $callback_data['signature'] ?? null;
    // if (!verify_signature($callback_data, $signature)) {
    //     throw new Exception('Invalid signature');
    // }
    
    // Update transaction in your database
    update_transaction($transaction_id, $status, $callback_data);
    
    // Process based on status
    switch ($status) {
        case 'new':
            // Transaction created, waiting for payment
            break;
            
        case 'payed':
            // Payment received, processing exchange
            send_notification($transaction_id, 'Payment received');
            break;
            
        case 'success':
            // Transaction completed successfully
            send_notification($transaction_id, 'Transaction completed');
            // Update order status, credit user account, etc.
            break;
            
        case 'cancel':
            // Transaction cancelled
            send_notification($transaction_id, 'Transaction cancelled');
            // Refund or notify user
            break;
            
        case 'refund':
            // Payment refunded
            send_notification($transaction_id, 'Payment refunded');
            break;
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Callback processed'
    ]);
    
} catch (Exception $e) {
    // Log error
    log_callback([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
