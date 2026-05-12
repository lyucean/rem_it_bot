<?php

namespace RIB\command;

use RIB\core\DB;
use Telegram;

class Message
{
    private Telegram $telegram;
    private int $chat_id;
    private int $message_id = 0;
    private DB $db;
    const EMOJI_ICON = '👩‍🎓  ';

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function __debugInfo()
    {
        return [
            'message_id' => $this->message_id,
        ];
    }

    /**
     * Отправляет сообщение в чат
     * @param array $data
     */
    public function send(array $data)
    {
        if (isset($data['chat_id'])) {
            $answer['chat_id'] = $data['chat_id'];
        }

        if (empty($answer['chat_id'])) {
            $answer['chat_id'] = $this->chat_id;
        }

        if (isset($data['reply_markup'])) {
            $answer['reply_markup'] = $data['reply_markup'];
        }

        if (isset($data['text'])) {
            $answer['text'] = self::EMOJI_ICON . stripslashes($data['text']);
        }

        $this->telegram->sendMessage($answer);
    }

    public function edit()
    {
        $text = $this->telegram->Text();

        if (!empty($this->telegram->Caption())) {
            $text = $this->telegram->Caption();
        }

        if (!$this->db->existCheckMessage(
            [
                'message_id' => $this->telegram->MessageID(),
                'chat_id' => $this->chat_id,
            ]
        )) {
            (new Error($this->telegram))->send('Сообщение уже удалено или не существует');
            return;
        }


        $this->db->editMessageByMessageId(
            [
                'chat_id' => $this->chat_id,
                'text' => $text,
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $this->send(
            [
                'text' => 'Я сохранила изменения.'
            ]
        );
    }

    public function addImage()
    {
        // take the highest resolution
        $data = $this->telegram->getData();

        $file = $this->telegram->getFile(array_pop($data['message']['photo'])['file_id']);

        if (!array_key_exists('ok', $file) || !array_key_exists('result', $file)) {
            (new Error($this->telegram))->send('Я не смог скачать картинку, сервер недоступен.');
            return;
        }

        $file_path = $file['result']['file_path'];
        $file_name = $file['result']['file_unique_id'] . '.jpg';

        $token = trim((string) ($_ENV['TELEGRAM_TOKEN'] ?? ''));
        $url_on_server = 'https://api.telegram.org/file/bot' . $token . '/' . $file_path;

        $folder = rand(10, 999) . '/';

        if (!is_dir($_ENV['DIR_FILE'] . $folder)) {
            mkdir($_ENV['DIR_FILE'] . $folder);
        }

        $imageBody = telegram_download_url($url_on_server);
        if ($imageBody === null) {
            (new Error($this->telegram))->send('Не удалось скачать файл с Telegram (таймаут или сеть). Повторите позже.');
            return;
        }

        file_put_contents(
            $_ENV['DIR_FILE'] . $folder . $file_name,
            $imageBody
        );

        $this->message_id = $this->telegram->MessageID();

        $this->db->addMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Caption(),
                'image' => $folder . $file_name,
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Отменить',
                    $url = '',
                    '/message/cancel?message_id=' . $this->message_id
                ),
            ],
        ];

        $this->send(
            [
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Я сохранила /_' . $this->message_id
            ]
        );
    }

    public function add()
    {
        if (!in_array($this->telegram->getUpdateType(), ['message', 'reply_to_message'])) {
            (new Error($this->telegram))->send('Я не знаю, как работать с этим типом сообщений.');
            return;
        }

        // double check
        if ($this->db->existCheckMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
            ]
        )) {
            $message = $this->db->getMessage(['text' => $this->telegram->Text()]);

            (new Error($this->telegram))->send(
                'Это сообщение уже существует /_' . $message['message_id']
            );
            return;
        }

        $this->message_id = $this->telegram->MessageID();

        $this->db->addMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Отменить',
                    $url = '',
                    '/message/cancel?message_id=' . $this->message_id
                ),
            ],
        ];

        $this->send(
            [
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Я сохранила /_' . $this->message_id
            ]
        );
    }

    public function cancel()
    {
        if ('callback_query' != $this->telegram->getUpdateType()) {
            (new Error($this->telegram))->send('Ошибка запроса.', true);
            return;
        }

        $param = get_var_query($this->telegram->Text());

        if (empty($param['message_id'])) {
            (new Error($this->telegram))->send('Я не смогла найти это сообщение.');
            return;
        }

        $this->message_id = $param['message_id'];

        if (!$this->db->existCheckMessage(
            [
                'message_id' => $this->message_id,
                'chat_id' => $this->chat_id,
            ]
        )) {
            (new Error($this->telegram))->send(
                'Сообщение /_' . $this->message_id . ' уже удалено.'
            );
            return;
        }

        $this->db->deleteMessage(
            [
                'message_id' => $this->message_id,
                'chat_id' => $this->chat_id,
            ]
        );

        $this->send(
            [
                'text' => 'Я удалила сообщение /_' . $this->message_id
            ]
        );
    }
}
