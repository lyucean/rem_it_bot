<?php

namespace RIB\core;

use Exception;
use MysqliDb;

class DB
{
    private MysqliDb $db;

    public function __construct()
    {
        $this->db = new MysqliDb(
          array(
            'host' => $_ENV['DB_HOST'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'db' => $_ENV['DB_NAME'],
            'port' => $_ENV['DB_PORT'],
            'prefix' => '',
            'charset' => $_ENV['DB_CHARSET']
          )
        );

        return $this;
    }
}
