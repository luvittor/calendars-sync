<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;

// Verifica se o token está disponível e ainda é válido
$tokenFile = 'token.json';
$tokenIsValid = false;

if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    
    // Verifica se o token tem um campo 'expires_in' e se ele ainda é válido
    if (isset($tokenData['expires_in'])) {
        $tokenAcquiredAt = filemtime($tokenFile); // Tempo em que o token.json foi modificado/criado
        $currentTime = time();
        $tokenIsValid = ($tokenAcquiredAt + $tokenData['expires_in']) > $currentTime;
    }
}

if (!$tokenIsValid) {
    // Token expirado ou inexistente, chamar ms-auth.php para obter um novo token
    require 'ms-auth.php';
    $tokenData = json_decode(file_get_contents($tokenFile), true);
}

$accessToken = $tokenData['access_token'];

// Configuração do cliente HTTP
$client = new Client([
    'base_uri' => 'https://graph.microsoft.com/v1.0/',
    'headers' => [
        'Authorization' => "Bearer $accessToken",
        'Accept' => 'application/json',
    ],
]);

// URL inicial para solicitar eventos
$url = 'me/events';
$allEvents = [];

do {
    // Solicita eventos da API Graph
    $response = $client->get($url);
    $events = json_decode($response->getBody(), true);

    // Adiciona os eventos ao array total
    $allEvents = array_merge($allEvents, $events['value']);

    // Verifica se há uma próxima página de resultados
    $url = isset($events['@odata.nextLink']) ? $events['@odata.nextLink'] : null;

} while ($url); // Continua até não haver mais uma próxima página

// Exibe todos os eventos
echo "Total de eventos: " . count($allEvents) . "\n";
foreach ($allEvents as $event) {
    echo $event['subject'] . " - " . $event['start']['dateTime'] . "\n";
}
