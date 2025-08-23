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
    echo "Event ID not found in the JSON file. Cannot delete the event.\n";
    exit(1);
}

// Gets the HTTP client with the token already verified and renewed if necessary
$client = getClient();

// Checks if calendarId is present in the JSON
$calendarId = @$eventData['calendarId'];

// Builds the URL to delete the event based on calendarId
if ($calendarId) {
    $eventEndpoint = "me/calendars/$calendarId/events/" . $eventData['id'];
} else {
    $eventEndpoint = "me/events/" . $eventData['id'];
}

// Deletes the event from the calendar
try {
    $client->delete($eventEndpoint);
    echo "Event with ID " . $eventData['id'] . " deleted successfully.\n";

    // Deletes the JSON file after the event is successfully deleted
    if (unlink($jsonFile)) {
        echo "File $jsonFile deleted successfully.\n";
    } else {
        echo "Error trying to delete the file $jsonFile.\n";
    }

} catch (\Exception $e) {
    echo "Error deleting the event: " . $e->getMessage() . "\n";
}
