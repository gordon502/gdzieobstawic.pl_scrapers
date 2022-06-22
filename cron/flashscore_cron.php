<?php

use App\Database\EventRepository;
use App\Provider\FlashscoreProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$flashscoreProvider = new FlashscoreProvider();

$result = $flashscoreProvider->findForToday();

if ($result === false) {
    echo 'Ups, something went wrong, try again later';

    return;
}

$eventRepository = new EventRepository();
$eventRepository->clear();

foreach ($result as $index => $event) {
    $eventRepository->insert($event);
}

echo 'Data from flashscore has been scraped successfully!';
