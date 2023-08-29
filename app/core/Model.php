<?php

namespace RIB\core;

use RIB\core\DB;
use Telegram;

class Model
{
    public Telegram $telegram;
    public DB $db;

    public function __construct()
    {
        $this->telegram = new Telegram(
            $_ENV['TELEGRAM_TOKEN'],
            true,
        );
        $this->db = new DB();
    }
}
