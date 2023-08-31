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
            'host' => $_ENV['MYSQL_HOST'],
            'username' => $_ENV['MYSQL_USER'],
            'password' => $_ENV['MYSQL_PASSWORD'],
            'db' => $_ENV['MYSQL_DATABASE'],
            'port' => $_ENV['MYSQL_PORT'],
            'prefix' => ''
          )
        );

        return $this;
    }

    // Таблица заданий для ежедневной отправки сообщений -----------------------

    /**
     * Получим массив сообщений для отправки
     * @throws Exception
     */
    public function getSendingDailyNow(): MysqliDb|array
    {
        $this->db->where("date_time", gmdate('Y-m-d H:i:s'), "<=");
        $this->db->where("status_sent", 0);
        return $this->db->get("schedule_daily");
    }

    /** Добавим задание на отправку
     * @throws Exception
     */
    public function addSendingDailyNow($data): bool
    {
        return $this->db->insert('schedule_daily', $data);
    }

    /** Изменим статус на отправлено
     * @throws Exception
     */
    public function setScheduleDailyStatusSent($schedule_daily_id): void
    {
        $this->db->where('schedule_daily_id', $schedule_daily_id);
        $this->db->update('schedule_daily', ['status_sent' => 1]);
    }

    // Таблица расписание отправки сообщений ---------------------------------------------------

    /**
     * @throws Exception
     */
    public function getSchedules(): MysqliDb|array
    {
        return $this->db->get("schedule");
    }

    /**
     * @throws Exception
     */
    public function getSchedule($chat_id): array|null
    {
        $this->db->where("chat_id", $chat_id);
        return $this->db->getOne("schedule");
    }

    /**
     * @throws Exception
     */
    public function setSchedule($chat_id, $data): void
    {
        if (!empty($data['quantity'])) {
            $change['quantity'] = (int)$data['quantity'];
        }

        if (!empty($data['time_zone_offset'])) {
            $change['time_zone_offset'] = (int)$data['time_zone_offset'];
        }

        if (!empty($data['hour_start'])) {
            $change['hour_start'] = (int)$data['hour_start'];
        }

        if (!empty($data['hour_end'])) {
            $change['hour_end'] = (int)$data['hour_end'];
        }

        if (empty($change)) {
            return;
        }

        $change['chat_id'] = $chat_id;
        $change['date_modified'] = $this->db->now();

        $this->db->replace('schedule', $change);
    }

    /**
     * @throws Exception
     */
    public function addSchedule($data): bool
    {
        $data['date_modified'] = $this->db->now();
        return $this->db->replace('schedule', $data);
    }

    // Таблица сообщений для отправки ----------------------------------------------------

    /**
     * Selects which message to send.
     * @param $chat_id
     * @return array|MysqliDb
     * @throws Exception
     */
    public function getMessagePrepared($chat_id): MysqliDb|array
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "asc");
        $message = $this->db->getOne("message");

        if (empty($message)) {
            return [];
        }

        // Add the information that we have already shown this message
        $this->addDateReminderMessage($message['message_id']);

        return $message;
    }

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function existCheckMessage($data): bool
    {
        if (isset($data['text'])) {
            $this->db->where("text", $this->db->escape(trim($data['text'])));
        }
        if (isset($data['message_id'])) {
            $this->db->where("message_id", (int)$data['message_id']);
        }
        $this->db->where("chat_id", $data['chat_id']);
        $this->db->where("display", 1);
        return !empty($this->db->get("message"));
    }

    /**
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getMessages($chat_id): MysqliDb|array|string
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "desc");
        return $this->db->get("message");
    }

    /**
     * @param array $filter
     * @return array
     * @throws Exception
     */
    public function getMessage(array $filter = []): array
    {
        if (isset($filter['message_id'])) {
            $this->db->where("message_id", (int)$filter['message_id']);
        }
        if (isset($filter['text'])) {
            $this->db->where("text", $this->db->escape(trim($filter['text'])));
        }
        return $this->db->getOne("message");
    }

    /**
     * @param int $chat_id
     * @return array
     * @throws Exception
     */
    public function getLastMessage(int $chat_id): array
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->orderBy("date_reminder", "DESC");
        $this->db->where("display", 1);

        return $this->db->getOne("message");
    }

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function deleteMessage($data): bool
    {
        $this->db->where('message_id', $data['message_id']);
        $this->db->where('chat_id', $data['chat_id']);
        return $this->db->delete('message');
    }

    /**
     * @param $chat_id
     * @return bool
     * @throws Exception
     */
    public function clearAllMessage($chat_id): bool
    {
        $this->db->where('chat_id', $chat_id);
        return $this->db->update(
          'message',
          [
            'display' => 0
          ]
        );
    }

    /**
     * Adds message, returns message_id
     * @param $data
     * @throws Exception
     */
    public function addMessage($data): void
    {
        $this->db->insert(
          'message',
          [
            'message_id' => $data['message_id'],
            'chat_id' => $data['chat_id'],
            'text' => $this->db->escape(trim($data['text'] ?? '')),
            'image' => $data['image'] ?? '',
            'view' => 0,
            'date_added' => $this->db->now(),
            'date_reminder' => $this->db->now(),
            'display' => 1,
          ]
        );
    }

    /**
     * update date reminder and view for Message
     * @param $message_id
     * @throws Exception
     */
    public function addDateReminderMessage($message_id): void
    {
        $this->db->where('message_id', $message_id);
        $this->db->update(
          'message',
          [
            'date_reminder' => $this->db->now(),
            'view' => $this->db->inc()
          ]
        );
    }

    /**
     * update Message
     * @param $data
     * @throws Exception
     */
    public function editMessageByMessageId($data): void
    {
        $this->db->where('message_id', $data['message_id']);
        $this->db->where('chat_id', $data['chat_id']);

        if (isset($data['text'])) {
            $changes['text'] = $this->db->escape(trim($data['text']));
        }
        if (isset($data['display'])) {
            $changes['display'] = (bool)$data['display'];
        }

        if (isset($changes)) {
            $this->db->update(
              'message',
              $changes
            );
        }
    }

    // ChatHistory ------------------------------------------------

    /**
     * @throws Exception
     */
    public function addChatHistory($data): void
    {
        $data['date_added'] = $this->db->now();
        $this->db->insert('chat_history', $data);
    }

    // WaitingCommand ---------------------------------------------

    /**
     * @throws Exception
     */
    public function getWaitingCommand($chat_id): MysqliDb|array|string|null
    {
        $this->db->where("chat_id", $chat_id);
        return $this->db->getOne("command_waiting", 'command');
    }

    /**
     * @throws Exception
     */
    public function cleanWaitingCommand($chat_id): bool
    {
        $this->db->where("chat_id", $chat_id);
        return $this->db->delete('command_waiting');
    }

    /**
     * @throws Exception
     */
    public function setWaitingCommand($chat_id, $command): void
    {
        $this->db->replace(
          'command_waiting',
          [
            'chat_id' => $chat_id,
            'date_added' => $this->db->now(),
            'command' => $this->db->escape($command)
          ]
        );
    }
}
