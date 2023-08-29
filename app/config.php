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
$dotenv->required('TELEGRAM_ADMIN_CHAT_ID')->notEmpty();