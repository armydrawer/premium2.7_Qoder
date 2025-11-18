<?php
/**
 * Test API Connection
 * 
 * This script tests the connection to the PremiumBox API
 * and verifies that your credentials are working correctly.
 */

require_once 'config.php';

echo "Testing PremiumBox API Connection...\n";
echo str_repeat("-", 50) . "\n\n";

// Make test request
$response = api_request('test', ['partner_id' => 0]);

// Log the request
log_api_request('test', [], $response);

// Display results
if ($response['error'] == '0') {
    echo "✓ Connection successful!\n\n";
    echo "Response Data:\n";
    echo "- IP Address: {$response['data']['ip']}\n";
    echo "- User ID: {$response['data']['user_id']}\n";
    echo "- Locale: {$response['data']['locale']}\n";
    echo "- Partner ID: {$response['data']['partner_id']}\n";
} else {
    echo "✗ Connection failed!\n\n";
    echo "Error Code: {$response['error']}\n";
    echo "Error Message: {$response['error_text']}\n";
    
    // Provide troubleshooting tips
    echo "\nTroubleshooting:\n";
    if ($response['error'] == 'curl_error') {
        echo "- Check your internet connection\n";
        echo "- Verify the API base URL is correct\n";
        echo "- Ensure SSL certificates are valid\n";
    } elseif ($response['error'] == '2') {
        echo "- Your IP address may be blocked\n";
        echo "- Check IP whitelist settings in your API configuration\n";
    } elseif ($response['error'] == '3') {
        echo "- The 'test' endpoint is not enabled for your API key\n";
        echo "- Enable it in your API key settings\n";
    } else {
        echo "- Verify your API-Login and API-Key are correct\n";
        echo "- Check that the API is enabled on your account\n";
    }
}

echo "\n" . str_repeat("-", 50) . "\n";
