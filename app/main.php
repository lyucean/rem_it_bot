<?php

require_once __DIR__ . '/vendor/autoload.php';



$db = new MysqliDb(
  array(
    'host' => $_ENV['MYSQL_HOST'],
    'username' => $_ENV['MYSQL_USER'],
    'password' => $_ENV['MYSQL_PASSWORD'],
    'db' => $_ENV['MYSQL_DATABASE'],
    'port' => $_ENV['MYSQL_PORT'],
    'prefix' => '',
    'charset' => 'utf8'
  )
);


