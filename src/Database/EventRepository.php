<?php

namespace App\Database;

use App\Model\Event;
use App\Model\Stake;
use PDO;

class EventRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct();

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS "event" (
                "id" INTEGER NOT NULL,
                "home_team"	TEXT NOT NULL,
                "away_team"	TEXT NOT NULL,
                "date" TEXT NOT NULL,
                "mean_home" REAL NOT NULL,
                "mean_draw" REAL NOT NULL,
                "mean_away" REAL NOT NULL,
                PRIMARY KEY("id" AUTOINCREMENT)
            )
        ');

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS "stake" (
                "id"	INTEGER NOT NULL,
                "bookmaker"	TEXT NOT NULL,
                "home"	REAL NOT NULL,
                "draw"	REAL NOT NULL,
                "away"	REAL NOT NULL,
                "event_id"	INTEGER,
                FOREIGN KEY("event_id") REFERENCES "event"("id"),
                PRIMARY KEY("id" AUTOINCREMENT)
            )
        ');
    }

    /**
     * @return Event[]
     */
    public function findAll(): array
    {
        $entries = $this->pdo
            ->query('SELECT e.id as event_id, * FROM `event` e JOIN `stake` s ON e.id = s.event_id')
            ->fetchAll(PDO::FETCH_ASSOC);

        if ($entries === false) {
            return [];
        }

        /** @var Event[] $events */
        $events = [];

        foreach ($entries as $entry) {
            if (!isset($events[$entry['event_id']])) {
                $events[$entry['event_id']] = new Event(
                    id: $entry['event_id'],
                    homeTeam: $entry['home_team'],
                    awayTeam: $entry['away_team'],
                    date: new \DateTime($entry['date']),
                    meanHome: $entry['mean_home'],
                    meanDraw: $entry['mean_draw'],
                    meanAway: $entry['mean_away'],
                    stakes: []
                );
            }

            $events[$entry['event_id']]->stakes[] = new Stake(
                bookmaker: $entry['bookmaker'],
                home: $entry['home'],
                draw: $entry['draw'],
                away: $entry['away']
            );
        }

        return array_values($events);
    }

    public function insert(object $object): bool
    {
        if (!$object instanceof Event) {
            throw new \InvalidArgumentException();
        }

        $eventStmt = $this->pdo->prepare('INSERT INTO event (id, home_team, away_team, date, mean_home, mean_draw, mean_away) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $result = $eventStmt->execute([
            $object->id,
            $object->homeTeam,
            $object->awayTeam,
            $object->date->format('Y-m-d H:i:s'),
            $object->meanHome,
            $object->meanDraw,
            $object->meanAway
        ]);

        if ($result === false) {
            return false;
        }

        $stakesData = [];

        foreach ($object->stakes as $index => $stake) {
            $stakesData[] = [
                $stake->bookmaker,
                $stake->home,
                $stake->draw,
                $stake->away,
                $object->id
            ];
        }

        $stakesStmt = $this->pdo->prepare('INSERT INTO `stake` (bookmaker, home, draw, away, event_id) VALUES (?, ?, ?, ?, ?)');
        try {
            $this->pdo->beginTransaction();

            foreach ($stakesData as $row) {
                $stakesStmt->execute($row);
            }

            return $this->pdo->commit();
        } catch (\Exception $exception) {
            $this->pdo->rollBack();

            return false;
        }
    }

    public function clear(): void
    {
        $this->pdo->exec('DELETE FROM `stake`');
        $this->pdo->exec('DELETE FROM `event`');
    }
}
