<?php
use Monolog\Logger;
use Logtail\Monolog\LogtailHandler;

class Logs
{
    private static $instance = null; // Static field to store the class instance

    private function __construct()
    {
        // Private constructor with logging setup
        if (!empty($_ENV['BETTERSTACK_TOKEN'])) {
            $logger = new Logger($_ENV['TELEGRAM_BOT_NAME'] . $_ENV['ENVIRONMENT']); // Initialize Logger
            $logger->pushHandler(new LogtailHandler($_ENV['BETTERSTACK_TOKEN']));
            self::$instance = $logger; // Store the Logger instance
        }else{
            self::$instance = new Logger('no_token_logger'); // Create a default logger
            self::$instance->critical('BETTERSTACK_TOKEN отсутствует');
        }
    }

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            new self(); // Create instance of this class if not already created
        }
        return self::$instance; // Return the Logger instance
    }
}
