<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('ENVIRONMENT')->notEmpty();
$dotenv->required('PMA_HOST')->notEmpty();
$dotenv->required('MYSQL_USER')->notEmpty();
$dotenv->required('MYSQL_PASSWORD')->notEmpty();
$dotenv->required('MYSQL_DATABASE')->notEmpty();

// Параметры для подключения к базе данных
$hostname = $_ENV['PMA_HOST']; // Или IP-адрес сервера MySQL
$username = $_ENV['MYSQL_USER']; // Замените на имя пользователя MySQL
$password = $_ENV['MYSQL_PASSWORD']; // Замените на пароль пользователя MySQL
$database = $_ENV['MYSQL_DATABASE']; // Замените на имя базы данных MySQL

// Где будем хранить логи
$logFile = 'log/work.log';

// Проверяем, существует ли файл
if (!file_exists($logFile)) {
    // Создаем файл
    touch($logFile);
    chmod($logFile, 0777);
}

// Устанавливаем максимальное время выполнения скрипта в 60 секунд
set_time_limit(60);

// Бесконечный цикл, который будет повторяться после завершения
while (true) {

    try {
        // Подключение к базе данных
        $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Подготовка и выполнение запроса на вставку строки
        $stmt = $pdo->prepare("INSERT INTO test_table (test_column, test_column_date_time) VALUES (:column_value, NOW())");
        $columnValue = $_ENV['ENVIRONMENT'] . ' - ' . date("Y-m-d H:i:s"); // Значение для колонки test_column
        $stmt->bindParam(':column_value', $columnValue);
        $stmt->execute();

        $logEntry =  "Данные успешно добавлены в таблицу.";
    } catch (PDOException $e) {
        $logEntry =  "Ошибка: " . $e->getMessage();
    }

    // Добавляем запись в файл
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Завершаем текущую итерацию, чтобы избежать нагрузки на сервер
    sleep(1); // Задержка 1 секунда перед каждой итерацией цикла

    // Определяем текущее время
    $currentTime = time();

    // Проверяем, если прошла минута, завершаем скрипт и перезапускаем его
    if ($currentTime - $_SERVER['REQUEST_TIME'] >= 60) {
        // Запускаем новый экземпляр скрипта
        exec('php ' . __FILE__ . ' >> /app/log/error.log 2>&1 &');
        exit(); // Завершаем текущий экземпляр скрипта
    }
}
