<?php
require 'vendor/autoload.php';
require 'ms-auth-client-handler.php';

// Obtém o cliente HTTP com o token já verificado e renovado se necessário
$client = getClient();

// Data e hora a partir da qual queremos listar os eventos modificados
$modifiedAfter = '2024-08-25T00:00:00Z'; // Substitua pela data e hora desejadas

// URL inicial para solicitar eventos com o filtro de data de modificação
$url = "me/events?\$filter=lastModifiedDateTime ge $modifiedAfter";
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
echo "Total de eventos modificados após $modifiedAfter: " . count($allEvents) . "\n";
foreach ($allEvents as $event) {
    echo $event['subject'] . " - Modificado em: " . $event['lastModifiedDateTime'] . "\n";
}
