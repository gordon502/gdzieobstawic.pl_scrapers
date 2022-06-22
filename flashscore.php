<?php

header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/vendor/autoload.php';

use App\Database\EventRepository;

$eventRepository = new EventRepository();

$events = $eventRepository->findAll();

echo json_encode($events);
