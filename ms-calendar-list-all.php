<?php
require 'vendor/autoload.php';
require 'ms-auth-client-handler.php';

// Gets the HTTP client with the token already verified and renewed if necessary
$client = getClient();

// Requests the list of calendars
$response = $client->get('me/calendars');
$calendars = json_decode($response->getBody(), true);

// Saves the list of calendars in a JSON file
$jsonFile = 'ms-calendar-list-all.json';
file_put_contents($jsonFile, json_encode($calendars, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo "The list of calendars has been saved to $jsonFile.\n";

// Displays the available calendars and saves in a JSON file
// echo "\n";
// echo "Available calendars:\n";
// foreach ($calendars['value'] as $calendar) {
//     echo "ID: " . $calendar['id'] . " - Name: " . $calendar['name'] . "\n";
// }