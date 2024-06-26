<?php


namespace RIB\command;

use Exception;
use Logs;
use Telegram;

class Test
{
    private Telegram $telegram;
    private int $chat_id;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
    }

    public function error()
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => '`Error`',
                'parse_mode' => 'MarkdownV2'
            ]
        );

        throw new Exception("My first Sentry error!");
    }

    public function index(): void
    {
        self::textLog();
    }

    public function textLog(): void
    {
        date_default_timezone_set('Europe/Moscow');
        $timestamp = $_SERVER['REQUEST_TIME'];
        $start_datetime = date("Y-m-d H:i:s", $timestamp);
        echo "Скрипт был запущен: " . $start_datetime;

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => htmlspecialchars('Релиз ' . $_ENV['RELEASE_DATE']
                    . PHP_EOL . 'Врем запуска ' . $start_datetime, ENT_QUOTES),
                'parse_mode' => 'html'
            ]
        );

        $log = Logs::getInstance();
        $log->debug('TestLog', [
            "environment" => $_ENV['ENVIRONMENT'],
            "release" => $_ENV['RELEASE_DATE'],
            "pid" => getmypid()
        ]);
    }
    public function testMarkdownV2(): void
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => '
*bold \*text*
_italic \*text_
__underline__
~strikethrough~
*bold _italic bold ~italic bold strikethrough~ __underline italic bold___ bold*
[inline URL](http://www.example.com/)
[inline mention of a user](tg://user?id=123456789)
`inline fixed-width code`
```
pre-formatted fixed-width code block
```
```python
pre-formatted fixed-width code block written in the Python programming language
```
                ',
                'parse_mode' => 'MarkdownV2'
            ]
        );
    }
}
