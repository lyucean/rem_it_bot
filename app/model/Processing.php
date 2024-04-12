<?php

namespace RIB\model;

use Exception;
use RIB\command\Message;
use RIB\command\Now;
use RIB\core\Action;
use RIB\core\Model;

/**
 * Responsible for the processing of all incoming messages from the user
 * Class Processing
 */
class Processing extends Model
{
    const MESSAGE_LIMIT_PER_REQUEST = 10;

    /**
     * @throws Exception
     */
    public function check(): void
    {
        // Get all the new updates and set the new correct update_id before each call
        $updates = $this->telegram->getUpdates(0, self::MESSAGE_LIMIT_PER_REQUEST);
        if (!array_key_exists('result', $updates) || empty($updates['result'])) {
            return;
        }

        for ($i = 0; $i < (int)$this->telegram->UpdateCount(); $i++) {
            // You NEED to call serveUpdate before accessing the values of message in Telegram Class
            $this->telegram->serveUpdate($i);

            $text = $this->telegram->Text();
            $chat_id = $this->telegram->ChatID();

            // для дев окружения всегда выкидываем ответ в консоль
            if (isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] == 'developer') {
                echo date('Y-m-d H:i:s') . " Сообщение: " . print_r($text, true) . PHP_EOL;
            }

            // Tracking activity
            $this->db->addChatHistory(
                [
                    'chat_id' => $this->telegram->ChatID(),
                    'first_name' => $this->telegram->FirstName(),
                    'last_name' => $this->telegram->LastName() ?? '',
                    'user_name' => $this->telegram->Username() ?? '',
                    'text' => $text
                ]
            );

            // If it's an independent command, it has the highest priority.
            // Necessarily, the very first
            if (is_string($text) && mb_substr($text, 0, 1, 'UTF-8') == '/') {
                // Clear command_waiting
                $this->db->cleanWaitingCommand($chat_id);

                // если это запрос на конкретное сообщение
                if (preg_match('/^\/_[0-9]+$/', $text)) {
                    (new Now($this->telegram))->get(substr(strrchr($text, "_"), 1));
                    continue;
                }

                // Let's look for our command
                $action = new Action($text);
                $action->execute($this->telegram);

//                \RIB\model\ya_metric($chat_id, $text);
                continue;
            }

            // If this is editing, just edit the message
            if ($this->telegram->getUpdateType() == 'edited_message') {
                (new Message($this->telegram))->edit();
                continue;
            }

            // If this message, then check if the command is waiting
            $waiting = $this->db->getWaitingCommand($chat_id);
            if (!empty($waiting['command'])) {
                // Clear command_waiting
                $this->db->cleanWaitingCommand($chat_id);

                // Let's look for our command_waiting
                $action = new Action($waiting['command']);
                $action->execute($this->telegram);

//                \RIB\model\ya_metric($chat_id, $waiting['command']);
                continue;
            }

            // If this image
            if ($this->telegram->getUpdateType() == 'photo') {
                (new Message($this->telegram))->addImage();
                continue;
            }
            // All that remains is sent to the controller by default
            (new Message($this->telegram))->add();
            continue;
        }
    }
}
