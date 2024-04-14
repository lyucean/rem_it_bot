<?php

require_once __DIR__ . '/vendor/autoload.php';

use RIB\model\Processing;
use RIB\model\Schedule;

$periodChecked = $_ENV['PERIOD_MESSAGE_CHECKED']; // Период проверки скрипта

sleep(60); // Нужно, для инициализации БД

$log = Logs::getInstance();
$log->info('Запуск бота', [
    "environment" => $_ENV['ENVIRONMENT'],
    "release" => $_ENV['RELEASE_DATE'],
    "pid" => getmypid()
]);

$cycleCount = 0; // счетчик цикла

// Бесконечный цикл, который будет вызывать основной файл скрипта
while (true) {
    // Ответ на все сообщения раз в секунду
    (new Processing())->check();

    // пускай выполняется каждый 60 цикл
    if ($cycleCount % 20 === 0) {
        heartbeat();
        if (1000 < $cycleCount) {
            $cycleCount = 0;
        }
    }
    $cycleCount++;

    // Проверяем расписание, нужно ли кому-то отправить сообщение
    if ($cycleCount % 60 === 0) { // пускай выполняется каждый 60 цикл
        (new  Schedule())->check();
    }

    // Давайте создадим список рассылки на день.
    if ($cycleCount % 600 === 0) { // пускай выполняется каждый 101 цикл
        (new  Schedule())->generate();
    }

    sleep($periodChecked);
}