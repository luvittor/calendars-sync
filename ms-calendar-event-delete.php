<?php
require 'vendor/autoload.php';
require 'ms-auth-client-handler.php';

// Verifica se o arquivo ms-calendar-event-create.json existe
$jsonFile = 'ms-calendar-event-create.json';
if (!file_exists($jsonFile)) {
    echo "Arquivo $jsonFile não encontrado. Certifique-se de que o evento foi criado.\n";
    exit(1);
}

// Carrega as informações do evento a partir do JSON
$eventData = json_decode(file_get_contents($jsonFile), true);
if (!isset($eventData['id'])) {
    echo "ID do evento não encontrado no arquivo JSON. Não é possível apagar o evento.\n";
    exit(1);
}

// Obtém o cliente HTTP com o token já verificado e renovado se necessário
$client = getClient();

// Verifica se o calendarId está presente no JSON
$calendarId = @$eventData['calendarId'];

// Monta a URL para excluir o evento com base no calendarId
if ($calendarId) {
    $eventEndpoint = "me/calendars/$calendarId/events/" . $eventData['id'];
} else {
    $eventEndpoint = "me/events/" . $eventData['id'];
}

// Apaga o evento do calendário
try {
    $client->delete($eventEndpoint);
    echo "Evento com ID " . $eventData['id'] . " apagado com sucesso.\n";

    // Apaga o arquivo JSON após a exclusão bem-sucedida do evento
    if (unlink($jsonFile)) {
        echo "Arquivo $jsonFile apagado com sucesso.\n";
    } else {
        echo "Erro ao tentar apagar o arquivo $jsonFile.\n";
    }

} catch (\Exception $e) {
    echo "Erro ao apagar o evento: " . $e->getMessage() . "\n";
}
