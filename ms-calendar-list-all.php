<?php
require 'vendor/autoload.php';
require 'ms-client-handler.php';

// Obtém o cliente HTTP com o token já verificado e renovado se necessário
$client = getClient();

// Solicita a lista de calendários
$response = $client->get('me/calendars');
$calendars = json_decode($response->getBody(), true);

// Salva a lista de calendários em um arquivo JSON
$jsonFile = 'ms-calendar-list-all.json';
file_put_contents($jsonFile, json_encode($calendars, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo "A lista de calendários foi salva em $jsonFile.\n";

// Exibe os calendários disponíveis e salva em um arquivo JSON
// echo "\n";
// echo "Calendários disponíveis:\n";
// foreach ($calendars['value'] as $calendar) {
//     echo "ID: " . $calendar['id'] . " - Nome: " . $calendar['name'] . "\n";
// }