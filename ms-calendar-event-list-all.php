<?php
require 'vendor/autoload.php';
require 'ms-auth-client-handler.php';

// Gets the HTTP client with the token already verified and renewed if necessary
$client = getClient();

// Retrieves the calendar ID from .env, using @ to avoid undefined variable errors
$calendarId = @$_ENV['CALENDAR_ID'];

// Builds the initial URL to request events based on calendarId
if ($calendarId) {
    $url = "me/calendars/$calendarId/events";
} else {
    $url = "me/events";
}

$allEvents = [];

do {
    // Requests events from the Graph API
    $response = $client->get($url);
    $events = json_decode($response->getBody(), true);

    // Adds the events to the total array
    $allEvents = array_merge($allEvents, $events['value']);

    // Checks if there is a next page of results
    $url = isset($events['@odata.nextLink']) ? $events['@odata.nextLink'] : null;

} while ($url); // Continues until there are no more next pages

// inserting calendarId into the events array
foreach ($allEvents as &$event) {
    $event['calendarId'] = $calendarId;
}

// Saves all events to the ms-calendar-event-list-all.json file formatted for viewing
file_put_contents('ms-calendar-event-list-all.json', json_encode($allEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

// Displays a success message
echo "Total events saved: " . count($allEvents) . "\n";
echo "The events have been saved to the file 'ms-calendar-event-list-all.json'.\n";

// echo "\n";
// echo "Displaying events:\n";
// foreach ($allEvents as $event) {
//     echo $event['subject'] . " - " . $event['start']['dateTime'] . "\n";
// }
