<?php
require 'vendor/autoload.php';
require 'ms-auth-client-handler.php';

// Checks if the ms-calendar-event-create.json file exists
$jsonFile = 'ms-calendar-event-create.json';
if (!file_exists($jsonFile)) {
    echo "File $jsonFile not found. Make sure the event was created.\n";
    exit(1);
}

// Loads the event information from the JSON file
$eventData = json_decode(file_get_contents($jsonFile), true);
if (!isset($eventData['id'])) {
    echo "Event ID not found in the JSON file. Cannot update the event.\n";
    exit(1);
}

// Updates the event information
$eventData['subject'] = 'Updated Meeting - Test with Microsoft Graph API';
$eventData['body']['content'] = 'This event was updated via Microsoft Graph API.';
$eventData['end']['dateTime'] = '2024-08-26T12:00:00'; // Changing the end time

// Gets the HTTP client with the token already verified and renewed if necessary
$client = getClient();

// Checks if calendarId is present in the JSON
$calendarId = @$eventData['calendarId'];

// Builds the URL to update the event based on calendarId
if ($calendarId) {
    $eventEndpoint = "me/calendars/$calendarId/events/" . $eventData['id'];
} else {
    $eventEndpoint = "me/events/" . $eventData['id'];
}

// Updates the event in the calendar
try {
    // Remove calendarId from the event data array
    unset($eventData['calendarId']);

    $response = $client->patch($eventEndpoint, [
        'json' => $eventData,
    ]);

    // Retrieves the updated event information
    $updatedEvent = json_decode($response->getBody(), true);

    // Inserts calendarId back into the event data array
    $updatedEvent['calendarId'] = $calendarId;

    // Saves the updated information back to the JSON file
    file_put_contents($jsonFile, json_encode($updatedEvent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    echo "Event updated successfully. The updated information was saved in $jsonFile.\n";

} catch (\Exception $e) {
    echo "Error updating the event: " . $e->getMessage() . "\n";
}
