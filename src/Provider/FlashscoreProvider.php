<?php

namespace App\Provider;

use App\Model\Event;
use App\Model\Stake;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

class FlashscoreProvider
{
    private Client $client;

    public function __construct()
    {
        $this->client = Client::createChromeClient();
    }

    /**
     * @return Event[]|false
     */
    public function findForToday(): array|false
    {
        /** @var Event[] $events */
        $events = [];

        try {
            $this->client->request('GET', 'https://www.flashscore.pl');
            $this->client->wait(5);

            $crawler = $this->client->getCrawler();

            $homeTeams = $crawler
                ->filter('.event__participant.event__participant--home')
                ->each(function (Crawler $node) {
                    return $node->text();
                });

            $awayTeams = $crawler
                ->filter('.event__participant.event__participant--away')
                ->each(function (Crawler $node) {
                    return $node->text();
                });

            $eventTimes = $crawler
                ->filter('.event__time, .event__stage')
                ->each(function (Crawler $node) {
                    return $node->text();
                });

            $stakesIds = $crawler
                ->filter('.event__match')
                ->each(function (Crawler $node) {
                    $id = $node->attr('id');
                    $firstSnake = strpos($id, '_');

                    return substr($id, $firstSnake + 3);
                });

            $allStakes = [];
            foreach ($stakesIds as $id) {
                $stakes = [];

                $this->client->request(
                    'GET',
                    sprintf('https://www.flashscore.pl/mecz/%s/#/zestawienie-kursow/kursy-1x2/koniec-meczu', $id)
                );
                $this->client->wait(1);

                $crawler = $this->client->getCrawler();

                $bookmakers = $crawler->filter('.prematchLink')->each(function (Crawler $node) {
                    return $node->attr('title');
                });

                $allOdds = $crawler->filter('.oddsCell__odd')->each(function (Crawler $node) {
                    return $node->text();
                });

                foreach ($bookmakers as $index => $bookmaker) {
                    /*
                     * sometimes odds are not present (but bookmaker is), i don't know why,
                     * so just ignore these records
                    */
                    if (isset($allOdds[$index * 3]) === false) {
                        continue;
                    }

                    $stakes[] = new Stake(
                        bookmaker: $bookmaker,
                        home: floatval($allOdds[$index * 3] ?? 0),
                        draw: floatval($allOdds[$index * 3 + 1] ?? 0),
                        away: floatval($allOdds[$index * 3 + 2] ?? 0)
                    );
                }

                $allStakes[] = $stakes;
            }

            $pom = [];
            $today = (new \DateTime())->format('Y-m-d');

            for ($i = 0; $i < count($homeTeams); $i++) {
                if (!preg_match('/^[0-9]{2}:[0-9]{2}$/', $eventTimes[$i], $pom)) {
                    continue;
                }

                if (empty($allStakes[$i])) {
                    continue;
                }

                $events[] = new Event(
                    id: $i + 1,
                    homeTeam: $homeTeams[$i],
                    awayTeam: $awayTeams[$i],
                    date: (new \DateTime($today . ' ' . $eventTimes[$i] . ':00')),
                    stakes: $allStakes[$i]
                );
            }
        } catch (\Exception $exception) {
            echo $exception;

            $events = false;
        }

        // closing flashscore sometimes generates session errors
        try {
            $this->client->quit();
        } catch (\Exception $exception) {
            if (is_array($events) && empty($events)) {
                $events = false;
            }
        }

        return $events;
    }
}
