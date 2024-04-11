<?php

require_once __DIR__ . '/vendor/autoload.php';

use RIB\model\Processing;
use RIB\model\Schedule;

$periodChecked = $_ENV['PERIOD_MESSAGE_CHECKED']; // Период проверки скрипта

sleep(5); // Нужно, для инициализации БД

$log = Logs::getInstance();
$log->info('Запуск бота', [
    "environment" => $_ENV['ENVIRONMENT'],
    "release" => $_ENV['RELEASE'],
    "pid" => getmypid()
]);

$cycleCount = 0; // счетчик цикла

// Бесконечный цикл, который будет вызывать основной файл скрипта
while (true) {

    // пускай выполняется каждый 60 цикл
    if ($cycleCount % 20 === 0) {
        heartbeat();
    }
    $cycleCount++;

    // Проверяем расписание, нужно ли кому-то отправить сообщение
    (new  Schedule())->check();

    // Ответ на все сообщения раз в секунду
    (new Processing())->check();

    // Давайте создадим список рассылки на день.
    (new  Schedule())->generate();

    sleep($periodChecked);
}