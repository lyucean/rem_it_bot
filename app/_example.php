<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('ENVIRONMENT')->notEmpty();
$dotenv->required('MYSQL_HOST')->notEmpty();
$dotenv->required('MYSQL_USER')->notEmpty();
$dotenv->required('MYSQL_PASSWORD')->notEmpty();
$dotenv->required('MYSQL_DATABASE')->notEmpty();

// Параметры для подключения к базе данных
$hostname = $_ENV['MYSQL_HOST']; // Или IP-адрес сервера MySQL
$username = $_ENV['MYSQL_USER']; // Замените на имя пользователя MySQL
$password = $_ENV['MYSQL_PASSWORD']; // Замените на пароль пользователя MySQL
$database = $_ENV['MYSQL_DATABASE']; // Замените на имя базы данных MySQL

try {
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Подготовка и выполнение запроса на вставку строки
    $stmt = $pdo->prepare("INSERT INTO test_table (test_column, test_column_date_time) VALUES (:column_value, NOW())");
    $columnValue = $_ENV['ENVIRONMENT'] . ' - ' . date("Y-m-d H:i:s"); // Значение для колонки test_column
    $stmt->bindParam(':column_value', $columnValue);
    $stmt->execute();

    $logEntry =  "Данные успешно добавлены в таблицу." . PHP_EOL;
} catch (PDOException $e) {
    $logEntry =  "Ошибка: " . $e->getMessage() . PHP_EOL;
}