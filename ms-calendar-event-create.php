<?php
require 'vendor/autoload.php';
require 'ms-auth-client-handler.php';

// Gets the HTTP client with the token already verified and renewed if necessary
$client = getClient();

// Retrieves the calendar ID from .env, using @ to avoid undefined variable errors
$calendarId = @$_ENV['CALENDAR_ID'];

// Checks if calendarId is set
if ($calendarId) {
    // If calendarId is set, creates the event in that calendar
    $calendarEndpoint = "me/calendars/$calendarId/events";
} else {
    // If not set, creates the event in the default calendar
    $calendarEndpoint = "me/events";
}

// Event data to be created
$eventData = [
    'subject' => 'Test Meeting with Microsoft Graph API',
    'body' => [
        'contentType' => 'HTML',
        'content' => 'This is a test meeting created via Microsoft Graph API.',
    ],
    'start' => [
        'dateTime' => '2024-08-26T10:00:00',
        'timeZone' => 'America/Sao_Paulo',
    ],
    'end' => [
        'dateTime' => '2024-08-26T11:00:00',
        'timeZone' => 'America/Sao_Paulo',
    ],
    'location' => [
        'displayName' => 'Office',
    ],
    'attendees' => [
        [
            'emailAddress' => [
                'address' => 'example@domain.com',
                'name' => 'Attendee Name',
            ],
            'type' => 'required',
        ],
    ],
];

// Creates the event in the specified calendar
try {
    $response = $client->post($calendarEndpoint, [
        'json' => $eventData,
    ]);

    $createdEvent = json_decode($response->getBody(), true);
    echo "Event created successfully:\n";
    echo "Event ID: " . $createdEvent['id'] . "\n";
    echo "Subject: " . $createdEvent['subject'] . "\n";
    echo "Start: " . $createdEvent['start']['dateTime'] . "\n";
    echo "End: " . $createdEvent['end']['dateTime'] . "\n";

    // Adds the calendarId used to save the event in the JSON, leaves it empty if it's the default calendar
    $createdEvent['calendarId'] = $calendarId ?? '';

    // Saves all event data to the ms-calendar-event-create.json file
    file_put_contents('ms-calendar-event-create.json', json_encode($createdEvent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    echo "Created event data saved in 'ms-calendar-event-create.json'.\n";

} catch (\Exception $e) {
    echo "Error creating the event: " . $e->getMessage() . "\n";
}
