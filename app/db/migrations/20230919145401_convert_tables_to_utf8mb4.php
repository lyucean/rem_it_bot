<?php

use Phinx\Migration\AbstractMigration;

class ConvertTablesToUtf8mb4 extends AbstractMigration
{
    public function up()
    {
        $tables = $this->fetchAll('SHOW TABLES');

        foreach ($tables as $table) {
            $tableName = reset($table);
            $this->execute("ALTER TABLE $tableName CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function down()
    {
        $this->output->writeln('Down migration is not supported.');
    }
}