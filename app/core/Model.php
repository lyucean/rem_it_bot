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
            telegram_bot_proxy_config(),
        );
        $this->db = new DB();
    }
}
