<?php
// Loads Composer's autoloader to use installed libraries
require 'vendor/autoload.php';

// Loads the variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuration of required parameters
$clientId = $_ENV['CLIENT_ID'];
$tenantId = $_ENV['TENANT_ID'];
$scopes = $_ENV['SCOPES'] . ' offline_access'; // Adds offline_access to the scope
$clientSecret = $_ENV['CLIENT_SECRET'];

// Endpoint URL to request the device code
$deviceCodeEndpoint = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/devicecode";
// Endpoint URL to obtain the access token
$tokenEndpoint = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";

// Requests the device code
echo "Requesting device code...\n";
$client = new \GuzzleHttp\Client();

try {
    $response = $client->post($deviceCodeEndpoint, [
        'form_params' => [
            'client_id' => $clientId,
            'scope' => $scopes,
        ],
    ]);

    $deviceCodeResponse = json_decode($response->getBody(), true);

    // Displays instructions for the user to authenticate on another device
    echo "Device code obtained successfully.\n";
    echo "Please visit " . $deviceCodeResponse['verification_uri'] . "\n";
    echo "And enter the code: " . $deviceCodeResponse['user_code'] . "\n";

} catch (\Exception $e) {
    echo "Error requesting device code: " . $e->getMessage() . "\n";
    exit(1);
}

// Loop to check if authorization is complete
echo "Waiting for user authorization...\n";
do {
    try {
    // Waits the defined interval plus a few extra seconds before trying again
    sleep($deviceCodeResponse['interval'] + 5); // Adds 5 extra seconds

    // Tries to obtain the access token
    echo "Trying to obtain the access token...\n";
        $tokenResponse = $client->post($tokenEndpoint, [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
                'device_code' => $deviceCodeResponse['device_code'],
            ],
        ]);

        $token = json_decode($tokenResponse->getBody(), true);

        // Checks if the token was obtained successfully
        if (isset($token['access_token'])) {
            echo "Access token obtained successfully.\n";
            break;
        }
        
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $responseBody = $e->getResponse()->getBody()->getContents();
        echo "Error trying to obtain the access token: " . $responseBody . "\n";

        // Checks if the exception is due to pending authorization
        if ($e->getResponse()->getStatusCode() !== 400 || strpos($responseBody, 'authorization_pending') === false) {
            throw $e; // If not "authorization_pending", rethrow the exception
        }

        // Otherwise, keep trying until authorization is complete
        echo "Authorization still pending, trying again...\n";
    }
} while (true);

// Stores the access token in a file for later use
if (isset($token['access_token'])) {
    echo "Storing the access token...\n";
    file_put_contents('ms-auth-client-secret-token.json', json_encode($token, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    echo "Access token received and stored in 'ms-auth-client-secret-token.json'.\n";
} else {
    echo "Error obtaining the access token: " . $token['error_description'] . "\n";
}
