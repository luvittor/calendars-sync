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

// Date and time from which we want to list modified events
$modifiedAfter = '2024-08-25T00:00:00Z'; // Replace with the desired date and time

// Adds the filter to list events modified after the specified date and time
$url .= "?\$filter=lastModifiedDateTime ge $modifiedAfter";
$allEvents = [];

do {
    // Requests events from the Graph API with the filter applied
    $response = $client->get($url);
    $events = json_decode($response->getBody(), true);

    // Adds the events to the total array
    $allEvents = array_merge($allEvents, $events['value']);

    // Checks if there is a next page of results
    $url = isset($events['@odata.nextLink']) ? $events['@odata.nextLink'] : null;

} while ($url); // Continues until there are no more next pages

// Displays all events
echo "Total events modified after $modifiedAfter: " . count($allEvents) . "\n";
foreach ($allEvents as $event) {
    echo $event['subject'] . " - Modified at: " . $event['lastModifiedDateTime'] . "\n";
}
