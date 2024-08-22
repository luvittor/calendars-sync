<?php
// Inclui o autoloader e as configurações da API
require 'vendor/autoload.php';

// Verifica se o arquivo de token já existe
if (file_exists('token.json')) {
    // Carrega o token do arquivo
    $accessToken = json_decode(file_get_contents('token.json'), true);
    $token = $accessToken['access_token'];
} else {
    // Se não existir, executa o processo de autenticação para obter um novo token
    require 'ms-auth.php';
    // Carrega o token novamente após a autenticação
    $accessToken = json_decode(file_get_contents('token.json'), true);
    $token = $accessToken['access_token'];
}

// Usa a biblioteca Guzzle para fazer requisições HTTP
use GuzzleHttp\Client;

$client = new Client();

// Faz uma requisição GET para obter os eventos do calendário do usuário
$response = $client->request('GET', 'https://graph.microsoft.com/v1.0/me/events', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token, // Inclui o token de acesso no cabeçalho da requisição para autenticação
        'Accept'        => 'application/json', // Especifica que a resposta deve estar no formato JSON
    ],
]);

// Decodifica a resposta JSON em um array associativo PHP
$events = json_decode($response->getBody()->getContents(), true);

// Itera sobre os eventos retornados e exibe o assunto e a data/hora de início de cada evento
foreach ($events['value'] as $event) {
    echo 'Evento: ' . $event['subject'] . ' - ' . $event['start']['dateTime'] . "\n";
}
