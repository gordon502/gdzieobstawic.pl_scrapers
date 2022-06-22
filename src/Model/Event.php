<?php

namespace App\Model;

use DateTime;
use JsonSerializable;

class Event implements JsonSerializable
{
    /**
     * @param int $id
     * @param string $homeTeam
     * @param string $awayTeam
     * @param DateTime $date
     * @param Stake[] $stakes
     */
    public function __construct(
        public int $id,
        public string $homeTeam,
        public string $awayTeam,
        public DateTime $date,
        public array $stakes
    ) { }

    public function jsonSerialize()
    {
        return [
            'homeTeam' => $this->homeTeam,
            'awayTeam' => $this->awayTeam,
            'date' => $this->date->format('d-m-Y H:i:s'),
            'stakes' => $this->stakes
        ];
    }
}
