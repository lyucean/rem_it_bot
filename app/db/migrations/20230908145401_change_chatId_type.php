<?php

use Phinx\Migration\AbstractMigration;

class ChangeChatIdType extends AbstractMigration
{
    public function change(): void
    {
        // Изменяем тип поля chat_id на BIGINT и делаем его NOT NULL в таблице chat_history
        $this->table('chat_history')
          ->changeColumn('chat_id', 'biginteger', ['null' => false])
          ->update();

        // Изменяем тип поля chat_id на BIGINT и делаем его NOT NULL в таблице command_waiting
        $this->table('command_waiting')
          ->changeColumn('chat_id', 'biginteger', ['null' => false])
          ->update();

        // Изменяем тип поля chat_id на BIGINT и делаем его NOT NULL в таблице message
        $this->table('message')
          ->changeColumn('chat_id', 'biginteger', ['null' => false])
          ->update();

        // Изменяем тип поля chat_id на BIGINT и делаем его NOT NULL в таблице schedule
        $this->table('schedule')
          ->changeColumn('chat_id', 'biginteger', ['null' => false])
          ->update();

        // Изменяем тип поля chat_id на BIGINT и делаем его NOT NULL в таблице schedule_daily
        $this->table('schedule_daily')
          ->changeColumn('chat_id', 'biginteger', ['null' => false])
          ->update();
    }
}
