<?php

$dotenv = Dotenv\Dotenv::createMutable(__DIR__);
$dotenv->load();

$dotenv->required('ENVIRONMENT')->notEmpty();

$dotenv->required('MYSQL_HOST')->notEmpty();
$dotenv->required('MYSQL_USER')->notEmpty();
$dotenv->required('MYSQL_PASSWORD')->notEmpty();
$dotenv->required('MYSQL_DATABASE')->notEmpty();
$dotenv->required('MYSQL_PORT')->notEmpty();

$dotenv->required('TELEGRAM_TOKEN')->notEmpty();
$dotenv->required('TELEGRAM_BOT_NAME')->notEmpty();
$dotenv->required('TELEGRAM_ADMIN_CHAT_ID')->notEmpty();

$dotenv->required('MAX_OF_MESSAGES_PER_DAY')->notEmpty();
$dotenv->required('MAX_LINE_LENGTH')->notEmpty();
$dotenv->required('PERIOD_MESSAGE_CHECKED')->notEmpty();
$dotenv->required('BETTERSTACK_TOKEN')->notEmpty();
$dotenv->required('RELEASE_DATE')->notEmpty();

$_ENV['DIR_BASE'] = __DIR__;
$_ENV['DIR_FILE'] = __DIR__ . '/file/';

date_default_timezone_set('Europe/Moscow'); // Установка временной зоны на Московское время

// Копим логи ошибок в Sentry
if (!empty($_ENV['SENTRY_DNS'])) {
    \Sentry\init([
        'dsn' => $_ENV['SENTRY_DNS'],
        'release' => $_ENV['RELEASE_DATE'],
        'environment' => $_ENV['ENVIRONMENT'],
        'traces_sample_rate' => 1.0,
        'profiles_sample_rate' => 1.0,
    ]);
}