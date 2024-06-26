<?php

namespace RIB\command;

use RIB\core\DB;
use Telegram;

class Start
{
    private Telegram $telegram;
    private int $chat_id;
    private DB $db;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function index()
    {
        $this->db->addSchedule(
            [
                'chat_id' => $this->chat_id,
                'hour_start' => 9,
                'hour_end' => 14,
                'time_zone_offset' => 3,
                'quantity' => 1,
            ]
        );

        $message[] = 'Привет 👩🏻‍🎓';
        $message[] = 'Я твой личный библиотекарь всего того, что даёт тебе поддержку, мотивацию, ' .
            'делает сильнее, поднимает настроение.';
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        $message = [];
        $message[] = 'Это могут быть  цитаты, мысли, фото, видео.';
        $message[] = 'Присылай всё мне.';
        $message[] = 'Я сохраняю их и отправлю обратно, по одной штуке в день, в удобный для тебя интервал.';
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        $message = [];
        $message[] = 'Так, каждый день, вы будете получать то, маленькую поддержку, от самого себя.';
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        $message = [];
        $message[] = 'Чтобы начать, просто отправьте мне сообщение, желательно, чтоб их было хотя бы пару штук.';
        $message[] = "Ты также можешь редактировать любое сообщение, обычным способом для телеграмм. Я сохраню все изменения.";
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );
    }
}
