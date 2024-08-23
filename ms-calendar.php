<?php
require 'vendor/autoload.php';
require 'ms-client-handler.php';

// Obtém o cliente HTTP com o token já verificado e renovado se necessário
$client = getClient();

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
