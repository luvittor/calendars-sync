<?php
require 'vendor/autoload.php';
require 'ms-auth-client-handler.php';

// Obtém o cliente HTTP com o token já verificado e renovado se necessário
$client = getClient();

// Recupera o ID do calendário do .env, utilizando @ para evitar erros de variáveis não definidas
$calendarId = @$_ENV['CALENDAR_ID'];

// Monta a URL inicial para solicitar eventos com base no calendarId
if ($calendarId) {
    $url = "me/calendars/$calendarId/events";
} else {
    $url = "me/events";
}

// Define a data e hora a partir da qual você quer listar os eventos
$startDate = '2024-08-24T00:00:00Z'; // Substitua pela data e hora desejada

// Adiciona o filtro para listar eventos a partir da data e hora especificadas
$url .= "?\$filter=start/dateTime ge '$startDate'";
$allEvents = [];

do {
    // Solicita eventos da API Graph com o filtro aplicado
    $response = $client->get($url);
    $events = json_decode($response->getBody(), true);

    // Adiciona os eventos ao array total
    $allEvents = array_merge($allEvents, $events['value']);

    // Verifica se há uma próxima página de resultados
    $url = isset($events['@odata.nextLink']) ? $events['@odata.nextLink'] : null;

} while ($url); // Continua até não haver mais uma próxima página

// Exibe todos os eventos
echo "Total de eventos a partir de $startDate: " . count($allEvents) . "\n";
foreach ($allEvents as $event) {
    echo $event['subject'] . " - Início: " . $event['start']['dateTime'] . "\n";
}
