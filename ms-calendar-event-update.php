<?php
require 'vendor/autoload.php';
require 'ms-client-handler.php';

// Verifica se o arquivo ms-calendar-event-create.json existe
$jsonFile = 'ms-calendar-event-create.json';
if (!file_exists($jsonFile)) {
    echo "Arquivo $jsonFile não encontrado. Certifique-se de que o evento foi criado.\n";
    exit(1);
}

// Carrega as informações do evento a partir do JSON
$eventData = json_decode(file_get_contents($jsonFile), true);
if (!isset($eventData['id'])) {
    echo "ID do evento não encontrado no arquivo JSON. Não é possível atualizar o evento.\n";
    exit(1);
}

// Atualiza as informações do evento
$eventData['subject'] = 'Reunião Atualizada - Teste com Microsoft Graph API';
$eventData['body']['content'] = 'Este evento foi atualizado via Microsoft Graph API.';
$eventData['end']['dateTime'] = '2024-08-25T12:00:00'; // Mudando a hora de término

// Obtém o cliente HTTP com o token já verificado e renovado se necessário
$client = getClient();

// Atualiza o evento no calendário
try {
    $response = $client->patch('me/events/' . $eventData['id'], [
        'json' => $eventData,
    ]);

    // Recupera as informações atualizadas do evento
    $updatedEvent = json_decode($response->getBody(), true);

    // Salva as informações atualizadas de volta no arquivo JSON
    file_put_contents($jsonFile, json_encode($updatedEvent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    echo "Evento atualizado com sucesso. As informações atualizadas foram salvas em $jsonFile.\n";

} catch (\Exception $e) {
    echo "Erro ao atualizar o evento: " . $e->getMessage() . "\n";
}
