<?php


namespace RIB\command;

use Exception;
use Telegram;

class Error
{
    private Telegram $telegram;
    private int $chat_id;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
    }

    public function send($message, $throw = false)
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => '👮🏻‍♀️ ' . $message
            ]
        );

        if ($throw) {
            $message = '[' . $this->telegram->getUpdateType() . '] ' . $message;
            throw new Exception($message);
        }
    }

    public function index(): void
    {
        if("138984892" == $_ENV['TELEGRAM_ADMIN_CHAT_ID']){
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => htmlspecialchars('Релиз ' . $_ENV['RELEASE_DATE']
                        . PHP_EOL . file_get_contents($_ENV['LOG_FILE']), ENT_QUOTES),
                    'parse_mode' => 'html'
                ]
            );
        }
    }
}
