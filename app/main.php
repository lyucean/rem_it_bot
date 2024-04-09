<?php

require_once __DIR__ . '/vendor/autoload.php';

use RIB\model\Processing;
use RIB\model\Schedule;
use Monolog\Logger;
use Logtail\Monolog\LogtailHandler;

$periodChecked = $_ENV['PERIOD_MESSAGE_CHECKED']; // Период проверки скрипта

$logger = new Logger("RIB-".$_ENV['ENVIRONMENT']); // Инициализация логов
$logger->pushHandler(new LogtailHandler($_ENV['BETTERSTACK_TOKEN']));
$logger->info("Запуск бота", [
    "environment" => $_ENV['ENVIRONMENT'],
    "release" => $_ENV['RELEASE'],
    "pid" => getmypid()
]);

sleep(5); // Нужно, для инициализации БД

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