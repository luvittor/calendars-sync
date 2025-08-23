<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use Dotenv\Dotenv;

// Loads environment variables from the .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function getClient() {
    $clientSecretAuth = $_ENV['CLIENT_SECRET_AUTH'] == "TRUE";

    $tokenFile = $clientSecretAuth ? 'ms-auth-client-secret-token.json' : 'ms-auth-client-assertion-token.json';
    $scriptFile = $clientSecretAuth ? 'ms-auth-client-secret.php' : 'ms-auth-client-assertion.php';
    $tokenIsValid = false;
    $tokenData = null;

    if (file_exists($tokenFile)) {
        $tokenData = json_decode(file_get_contents($tokenFile), true);
        
        // Checks if the token has an 'expires_in' field and if it is still valid
        if (isset($tokenData['expires_in'])) {
            $tokenAcquiredAt = filemtime($tokenFile); // Time when the json was modified/created
            $currentTime = time();
            $tokenIsValid = ($tokenAcquiredAt + $tokenData['expires_in']) > $currentTime;
        }
    } else {
        echo "Token not found. Please log in using $scriptFile.\n";
        exit(1);
    }

    // If the token is not valid, try to renew it using the refresh_token
    if (!$tokenIsValid) {
        if (isset($tokenData['refresh_token'])) {
            try {
                // Configures the HTTP client to request a new access_token using the refresh_token
                $client = new Client();
                $response = $client->post("https://login.microsoftonline.com/{$_ENV['TENANT_ID']}/oauth2/v2.0/token", [
                    'form_params' => [
                        'client_id' => $_ENV['CLIENT_ID'],
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $tokenData['refresh_token'],
                        'scope' => $_ENV['SCOPES'],
                    ],
                ]);

                $newTokenData = json_decode($response->getBody(), true);

                // Checks if the new token was successfully obtained
                if (isset($newTokenData['access_token'])) {
                    // Stores the new token and updates tokenData
                    file_put_contents($tokenFile, json_encode($newTokenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    $tokenData = $newTokenData;
                    echo "Token successfully renewed.\n";
                } else {
                    throw new Exception("Failed to renew token: " . json_encode($newTokenData));
                }
            } catch (\Exception $e) {
                echo "Error trying to renew the token: " . $e->getMessage() . "\n";
                exit(1);
            }
        } else {
            echo "Token expired and no refresh_token available. Please log in again using $scriptFile.\n";
            exit(1);
        }
    }

    $accessToken = $tokenData['access_token'];

    // HTTP client configuration
    return new Client([
        'base_uri' => 'https://graph.microsoft.com/v1.0/',
        'headers' => [
            'Authorization' => "Bearer $accessToken",
            'Accept' => 'application/json',
        ],
    ]);
}
