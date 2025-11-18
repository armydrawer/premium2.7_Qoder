<?php
/**
 * PremiumBox API Configuration
 * 
 * Store your API credentials here.
 * IMPORTANT: Keep this file secure and never commit to public repositories!
 */

// API Base URL (replace with your actual domain)
define('API_BASE_URL', 'https://your-domain.com/api/v1/');

// API Credentials (get these from your account dashboard)
define('API_LOGIN', 'your_api_login_here');
define('API_KEY', 'your_api_key_here');

// Optional: Language for API responses
define('API_LANG', 'en');

// Optional: Callback URL for transaction status updates
define('CALLBACK_URL', 'https://your-site.com/api/callback.php');

/**
 * Make API Request
 * 
 * @param string $endpoint API endpoint name
 * @param array $params Request parameters
 * @return array API response
 */
function api_request($endpoint, $params = []) {
    $url = API_BASE_URL . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if (!empty($params)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'API-Login: ' . API_LOGIN,
        'API-Key: ' . API_KEY,
        'API-Lang: ' . API_LANG
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'error' => 'curl_error',
            'error_text' => $error,
            'data' => []
        ];
    }
    
    if ($http_code !== 200) {
        return [
            'error' => 'http_error',
            'error_text' => "HTTP {$http_code}",
            'data' => []
        ];
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'error' => 'json_error',
            'error_text' => json_last_error_msg(),
            'data' => []
        ];
    }
    
    return $result;
}

/**
 * Log API Request and Response
 * 
 * @param string $endpoint Endpoint name
 * @param array $params Request parameters
 * @param array $response API response
 */
function log_api_request($endpoint, $params, $response) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'params' => $params,
        'response' => $response
    ];
    
    // Sanitize sensitive data
    if (isset($log_entry['params']['account_give'])) {
        $log_entry['params']['account_give'] = '***';
    }
    if (isset($log_entry['params']['account_get'])) {
        $log_entry['params']['account_get'] = '***';
    }
    
    $log_file = __DIR__ . '/api_requests.log';
    file_put_contents(
        $log_file,
        json_encode($log_entry, JSON_PRETTY_PRINT) . "\n\n",
        FILE_APPEND
    );
}
