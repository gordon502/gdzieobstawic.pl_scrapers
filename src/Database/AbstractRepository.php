<?php

namespace App\Database;

use PDO;

abstract class AbstractRepository
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO("sqlite:".__DIR__."/../../database.db");
    }

    abstract function findAll();

    abstract function insert(object $object): bool;

    abstract function clear(): void;
}