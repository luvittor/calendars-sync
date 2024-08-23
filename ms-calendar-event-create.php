<?php
require 'vendor/autoload.php';
require 'ms-client-handler.php';

// Obtém o cliente HTTP com o token já verificado e renovado se necessário
$client = getClient();

// Dados do evento a ser criado
$eventData = [
    'subject' => 'Reunião de Teste com Microsoft Graph API',
    'body' => [
        'contentType' => 'HTML',
        'content' => 'Esta é uma reunião de teste criada via Microsoft Graph API.',
    ],
    'start' => [
        'dateTime' => '2024-08-25T10:00:00',
        'timeZone' => 'America/Sao_Paulo',
    ],
    'end' => [
        'dateTime' => '2024-08-25T11:00:00',
        'timeZone' => 'America/Sao_Paulo',
    ],
    'location' => [
        'displayName' => 'Escritório',
    ],
    'attendees' => [
        [
            'emailAddress' => [
                'address' => 'exemplo@dominio.com',
                'name' => 'Nome do Participante',
            ],
            'type' => 'required',
        ],
    ],
];

// Cria o evento no calendário
try {
    $response = $client->post('me/events', [
        'json' => $eventData,
    ]);

    $createdEvent = json_decode($response->getBody(), true);
    echo "Evento criado com sucesso:\n";
    echo "ID do Evento: " . $createdEvent['id'] . "\n";
    echo "Assunto: " . $createdEvent['subject'] . "\n";
    echo "Início: " . $createdEvent['start']['dateTime'] . "\n";
    echo "Término: " . $createdEvent['end']['dateTime'] . "\n";

    // Salva todos os dados do evento no arquivo ms-calendar-event-create.json
    file_put_contents('ms-calendar-event-create.json', json_encode($createdEvent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    echo "Dados do evento criado salvos em 'ms-calendar-event-create.json'.\n";

} catch (\Exception $e) {
    echo "Erro ao criar o evento: " . $e->getMessage() . "\n";
}
