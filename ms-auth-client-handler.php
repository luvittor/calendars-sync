<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;

// Carrega as variáveis do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function getClient() {
    $clientSecretAuth = $_ENV['CLIENT_SECRET_AUTH'] == "TRUE";

    $tokenFile = $clientSecretAuth ? 'ms-auth-client-secret-token.json' : 'ms-auth-client-assertion-token.json';
    $scriptFile = $clientSecretAuth ? 'ms-auth-client-secret.php' : 'ms-auth-client-assertion.php';
    $tokenIsValid = false;
    $tokenData = null;

    if (file_exists($tokenFile)) {
        $tokenData = json_decode(file_get_contents($tokenFile), true);
        
        // Verifica se o token tem um campo 'expires_in' e se ele ainda é válido
        if (isset($tokenData['expires_in'])) {
            $tokenAcquiredAt = filemtime($tokenFile); // Tempo em que o json foi modificado/criado
            $currentTime = time();
            $tokenIsValid = ($tokenAcquiredAt + $tokenData['expires_in']) > $currentTime;
        }
    } else {
        echo "Token não encontrado. Por favor, faça login usando $scriptFile.\n";
        exit(1);
    }

    // Se o token não for válido, tenta renová-lo usando o refresh_token
    if (!$tokenIsValid) {
        if (isset($tokenData['refresh_token'])) {
            try {
                // Configura o cliente HTTP para solicitar um novo access_token usando o refresh_token
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

                // Verifica se o novo token foi obtido com sucesso
                if (isset($newTokenData['access_token'])) {
                    // Armazena o novo token e atualiza o tokenData
                    file_put_contents($tokenFile, json_encode($newTokenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    $tokenData = $newTokenData;
                    echo "Token renovado com sucesso.\n";
                } else {
                    throw new Exception("Falha ao renovar o token: " . json_encode($newTokenData));
                }
            } catch (\Exception $e) {
                echo "Erro ao tentar renovar o token: " . $e->getMessage() . "\n";
                exit(1);
            }
        } else {
            echo "Token expirado e nenhum refresh_token disponível. Por favor, faça login novamente usando $scriptFile.\n";
            exit(1);
        }
    }

    $accessToken = $tokenData['access_token'];

    // Configuração do cliente HTTP
    return new Client([
        'base_uri' => 'https://graph.microsoft.com/v1.0/',
        'headers' => [
            'Authorization' => "Bearer $accessToken",
            'Accept' => 'application/json',
        ],
    ]);
}
