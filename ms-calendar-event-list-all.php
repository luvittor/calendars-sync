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

// inserindo calendarId no array de eventos
foreach ($allEvents as &$event) {
    $event['calendarId'] = $calendarId;
}

// Salva todos os eventos no arquivo ms-calendar-event-list-all.json formatado para visualização
file_put_contents('ms-calendar-event-list-all.json', json_encode($allEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

// Exibe uma mensagem de sucesso
echo "Total de eventos salvos: " . count($allEvents) . "\n";
echo "Os eventos foram salvos no arquivo 'ms-calendar-event-list-all.json'.\n";

// Exibe todos os eventos
// echo "\n";
// echo "Exibindo eventos:\n";
// foreach ($allEvents as $event) {
//     echo $event['subject'] . " - " . $event['start']['dateTime'] . "\n";
// }
