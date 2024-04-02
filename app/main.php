<?php

require_once __DIR__ . '/vendor/autoload.php';

use RIB\model\Processing;
use RIB\model\Schedule;

sleep(2); // Нужно, для инициализации БД

$periodChecked = $_ENV['PERIOD_MESSAGE_CHECKED']; // Период проверки скрипта

// Бесконечный цикл, который будет вызывать основной файл скрипта
while (true) {
    // Проверяем расписание, нужно ли кому-то отправить сообщение
    (new  Schedule())->check();

    // Ответ на все сообщения раз в секунду
    (new Processing())->check();

    // Давайте создадим список рассылки на день.
    (new  Schedule())->generate();

    sleep($periodChecked);
}