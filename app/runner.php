<?php
/**
 * Этот скрипт запускает основной скрипт бота каждую секунду и пишет логи от его выполнения.
 */
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('PERIOD_MESSAGE_CHECKED')->notEmpty();
$dotenv->required('BACKUPS_FOLDER')->notEmpty();

$max_execution_time = 60; // Зададим максимальное время выполнения нашего скрипта
$logFile_success = 'logs/success_runner.log'; // Где будем хранить логи работы бота
$logFile_error = 'logs/error_runner.log'; // Где будем хранить логи работы бота
$targetScript = dirname(__FILE__).'/main.php'; // Путь к целевому скрипту
$periodChecked = $_ENV['PERIOD_MESSAGE_CHECKED']; // Период проверки скрипта
//$backups_folder = $_ENV['BACKUPS_FOLDER']; // Период проверки скрипта
//$backupScript = dirname(__FILE__).'/backup.php'; // Путь к целевому скрипту
//$backupMask = 'RIB_'.date('Y-m-d').'.tar.gz'; // Маска имени файла бэкапа
//$time_create_backup = '1'; // Время после скольки часов снимать бекап в 24 формате

// Проверяем, существует ли файл логов, если нет - создадим
if (!file_exists($logFile_success)) {
    touch($logFile_success);
    chmod($logFile_success, 0777); // поправим права
}
if (!file_exists($logFile_error)) {
    touch($logFile_error);
    chmod($logFile_error, 0777); // поправим права
}

// Проверяем количество строк в файле и удаляем первые 1000 строк, если нужно
$logContents = file($logFile_success);
if (count($logContents) >= 2000) {
    $logContents = array_slice($logContents, 1000);
    file_put_contents($logFile_success, implode('', $logContents));
}

// Устанавливаем максимальное время выполнения скрипта в 60 секунд
set_time_limit(60);

// Запись лога работы скрипта
$log = function ($logFile_success,$output, $executionTimeMs){
    // Записываем вывод и время выполнения в лог файл
    $logMessage = date('Y-m-d H:i:s')." : Execution time: ".number_format($executionTimeMs * 1000, 2)." ms".PHP_EOL;
    $logMessage .= '    '.implode("\n", $output).PHP_EOL;
    file_put_contents($logFile_success, $logMessage, FILE_APPEND);
};

// Проверяем, если текущее время позже 1:00 и файла бекапа не существует, то создадим его
//if (intval(date('G')) >= $time_create_backup && !file_exists($backups_folder.'/'.$backupMask)) {
//
//    // Засекаем время до выполнения скрипта
//    $startTime = microtime(true);
//
//    // Выполняем скрипт бекапа и сохраняем вывод в переменную
//    $output = ['Скрипт бекапа'];
//    exec("php $backupScript", $output);
//
//    // Засекаем время после выполнения скрипта и вычисляем разницу в миллисекундах
//    $executionTimeMs = (microtime(true) - $startTime) * 1000;
//
//    $log($logFile_success,$output, $executionTimeMs);
//}

// Бесконечный цикл, который будет вызывать основной файл скрипта
while (true) {
    // Засекаем время до выполнения скрипта
    $startTime = microtime(true);

    // Выполняем целевой скрипт и сохраняем вывод в переменную
    $output = [];
    exec("php $targetScript", $output);

    // Засекаем время после выполнения скрипта и вычисляем разницу в миллисекундах
    $executionTimeMs = (microtime(true) - $startTime) * 1000;

    $log($logFile_success,$output, $executionTimeMs);

    // Завершаем текущую итерацию, чтобы избежать нагрузки на сервер
    sleep($periodChecked); // Задержка в секундах перед каждой итерацией цикла

    // Определяем текущее время
    $currentTime = time();

    // Проверяем, если скрипт работает больше нужного, перезапустим его
    if ($currentTime - $_SERVER['REQUEST_TIME'] >= $max_execution_time) {
        // Запускаем новый экземпляр скрипта
        exec('php '.__FILE__.' >> '.$logFile_error.' 2>&1 &');
        exit(); // Завершаем текущий экземпляр скрипта
    }
}
