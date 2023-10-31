<?php


namespace RIB\command;

use Telegram;

class Help
{
    private Telegram $telegram;
    private int $chat_id;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
    }

    private function getVersion(): string
    {
        $filename = __FILE__; // Получить имя текущего файла (текущего скрипта)
        $creation_time = filectime($filename); // Получить время создания файла

        if ($creation_time !== false) {
            return date('Y-m-d H:i:s', $creation_time);
        } else {
            return "Не удалось получить версию.";
        }
    }

    public function index()
    {
        $this->telegram->sendMessage(
          [
            'chat_id' => $this->chat_id,
            'text' => 'Напишите мне личное сообщение @lyucean'
              .PHP_EOL.'Я вам обязательно помогу 🖐'
              .PHP_EOL
              .PHP_EOL.'Ваша версия: '.$this->getVersion()
          ]
        );
    }
}
