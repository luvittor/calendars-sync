<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use GuzzleHttp\Client;

// Carrega as variáveis do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuração dos parâmetros necessários
$clientId = $_ENV['CLIENT_ID'];
$tenantId = $_ENV['TENANT_ID'];
$audience = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
$scopes = $_ENV['SCOPES'];

// Carrega a chave privada para assinar o JWT
$privateKey = file_get_contents('cert/private_key.pem');

// Gera o JWT (client_assertion) assinado
$now = time();
$exp = $now + 3600; // Token válido por 1 hora

$token = [
    'aud' => $audience,
    'iss' => $clientId,
    'sub' => $clientId,
    'jti' => bin2hex(random_bytes(16)),
    'nbf' => $now,
    'exp' => $exp,
];

$clientAssertion = JWT::encode($token, $privateKey, 'RS256');

echo "Client Assertion gerado com sucesso: $clientAssertion.\n";

// URL do endpoint para solicitar o código do dispositivo
$deviceCodeEndpoint = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/devicecode";
// URL do endpoint para obter o token de acesso
$tokenEndpoint = $audience;

// Solicita o código do dispositivo
echo "Solicitando o código do dispositivo...\n";
$client = new Client();

try {
    $response = $client->post($deviceCodeEndpoint, [
        'form_params' => [
            'client_id' => $clientId,
            'scope' => $scopes,
        ],
    ]);

    $deviceCodeResponse = json_decode($response->getBody(), true);

    // Exibe as instruções para o usuário autenticar em outro dispositivo
    echo "Código do dispositivo obtido com sucesso.\n";
    echo "Por favor, visite " . $deviceCodeResponse['verification_uri'] . "\n";
    echo "E entre com o código: " . $deviceCodeResponse['user_code'] . "\n";

} catch (\Exception $e) {
    echo "Erro ao solicitar o código do dispositivo: " . $e->getMessage() . "\n";
    exit(1);
}

// Loop para verificar se a autorização foi concluída
echo "Aguardando a autorização do usuário...\n";
do {
    try {
        // Aguarda o intervalo definido mais alguns segundos antes de tentar novamente
        sleep($deviceCodeResponse['interval'] + 5); // Adiciona 5 segundos extras

        // Tenta obter o token de acesso
        echo "Tentando obter o token de acesso...\n";
        $tokenResponse = $client->post($tokenEndpoint, [
            'form_params' => [
                'client_id' => $clientId,
                'client_assertion' => $clientAssertion,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
                'device_code' => $deviceCodeResponse['device_code'],
            ],
        ]);

        $token = json_decode($tokenResponse->getBody(), true);

        // Verifica se o token foi obtido com sucesso
        if (isset($token['access_token'])) {
            echo "Token de acesso obtido com sucesso.\n";
            break;
        }
        
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $responseBody = $e->getResponse()->getBody()->getContents();
        echo "Erro ao tentar obter o token de acesso: " . $responseBody . "\n";

        // Verifica se a exceção é devido à autorização pendente
        if ($e->getResponse()->getStatusCode() !== 400 || strpos($responseBody, 'authorization_pending') === false) {
            throw $e; // Se não for "authorization_pending", relança a exceção
        }

        // Caso contrário, continua tentando até que a autorização seja concluída
        echo "Autorização ainda pendente, tentando novamente...\n";
    }
} while (true);

// Armazena o token de acesso em um arquivo para uso posterior
if (isset($token['access_token'])) {
    echo "Armazenando o token de acesso...\n";
    file_put_contents('token.json', json_encode($token));
    echo "Token de acesso recebido e armazenado em 'token.json'.\n";
} else {
    echo "Erro ao obter o token de acesso: " . $token['error_description'] . "\n";
}
