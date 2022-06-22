<?php

namespace App\Model;

use JsonSerializable;

class Stake implements JsonSerializable
{
    /**
     * @param string $bookmaker
     * @param float $home
     * @param float $draw
     * @param float $away
     */
    public function __construct(
        public string $bookmaker,
        public float $home,
        public float $draw,
        public float $away
    ) { }

    public function jsonSerialize()
    {
        return [
            'bookmaker' => $this->bookmaker,
            'home' => $this->home,
            'draw' => $this->draw,
            'away' => $this->away
        ];
    }
}
