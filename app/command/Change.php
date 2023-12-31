<?php


namespace RIB\command;

use Exception;
use RIB\core\DB;
use Telegram;

class Change
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
        // available only if messages exist
        if (empty($this->db->getMessages($this->chat_id))) {
            (new Error($this->telegram))->send('У вас пока нет сообщений и мне нечего менять.');
            return;
        }

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard(
                    [
                        [
                            $this->telegram->buildInlineKeyBoardButton(
                                '✏ Изменить сообщение',
                                $url = '',
                                '/change/choice'
                            )
                        ],
                        [
                            $this->telegram->buildInlineKeyBoardButton(
                                '❌ Удалить последнее присланное',
                                $url = '',
                                '/change/delete_last_sent'
                            )
                        ],
                        [
                            $this->telegram->buildInlineKeyBoardButton(
                                '❌ Удалить по номеру',
                                $url = '',
                                '/change/delete_choice'
                            )
                        ],
                    ]
                ),
                'text' => 'Что мне сделать?'
            ]
        );
    }

    public function delete_choice()
    {
        //Put the command on hold;
        $this->db->setWaitingCommand($this->chat_id, '/change/delete');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Введи номер сообщения, который нужно изменить [я жду просто цифру например 10].'
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        $message_id = $this->telegram->Text();

        if (!is_numeric($message_id)) {
            (new Error($this->telegram))->send('Я ожидаю число и оно должно быть больше 0.');
            // return the command on hold;
            $this->db->setWaitingCommand($this->chat_id, '/change/delete');
            return;
        }

        if (!$this->db->existCheckMessage(
            [
                'message_id' => $message_id,
                'chat_id' => $this->chat_id,
            ]
        )) {
            (new Error($this->telegram))->send(
                'Сообщение /_' . $message_id . ' уже удалено или не существует.'
            );
            return;
        }

        $this->db->deleteMessage(
            [
                'message_id' => $message_id,
                'chat_id' => $this->chat_id,
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Я удалила сообщение /_' . $message_id . ' 👌'
            ]
        );
    }

    public function delete_last_sent()
    {
        $m_last = $this->db->getLastMessage($this->chat_id);

        if (empty($m_last)) {
            (new Error($this->telegram))->send('Нет сообщений.');
        }

        $this->db->editMessageByMessageId(
            [
                'message_id' => $m_last['message_id'],
                'chat_id' => $this->chat_id,
                'display' => false,
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Сообщение /_' . $m_last['message_id']
                    . ' "' . shorten_line($m_last['text']) . '" удалено 👌',
            //                'reply_markup' => $this->telegram->buildInlineKeyBoard(
            //                    [
            //                        [
            //                            $this->telegram->buildInlineKeyBoardButton(
            //                                '◀ Отменить',
            //                                $url = '',
            //                                '/change/delete_undo'
            //                            )
            //                        ],
            //                    ]
            //                ),
            ]
        );
    }

    public function choice()
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Два способа:'
                    . PHP_EOL . '1. Найти, где ты отправляешь мне это сообщение и отредактировать обычным для telegram способом.'
                    . PHP_EOL . '2. Удалить сообщение и отправить мне уже изменённый текст.'
            ]
        );
    }
}
