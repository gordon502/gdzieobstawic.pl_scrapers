# gdzieobstawic.pl scrapers
This project contains scrapers to get information about football 
matches and bookmaker stakes for them. It was created for the needs of my other project 
[gdzieobstawic.pl](https://github.com/gordon502/gdzieobstawic.pl) which is written in Angular.

At the moment project contains only one source for polish 
bookmakers [flashscore.pl](https://www.flashscore.pl) and is working properly at least today (22.06.2022),
but it is possible that in the future some reworks will be needed.

## Requirements
- Apache Server with at least PHP 8.0
- Composer

## Installation
```bash
composer install
php vendor/bin/bdi detect drivers
```

## Usage
To create SQLite DB file and fulfill it with scraped data:
```bash
php cron/flashscore_cron.php
```

To obtain data from database (in JSON format), make any REQUEST call to root flashscore.php file, e.g.:
```
http://localhost:8080/flashscore.php
```

## TODO
Pack whole project into docker containers.
