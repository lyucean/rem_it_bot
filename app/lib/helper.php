<?php

// get query param
if (!function_exists('get_var_query')) {
    /**
     * @param string $string
     * @return array
     */
    function get_var_query(string $string): array
    {
        $string = parse_url($string);

        if (empty($string['query'])) {
            return [];
        }

        parse_str($string['query'], $query);

        return $query;
    }
}

// shorten_line
if (!function_exists('shorten_line')) {
    /**
     * @param string $text
     * @return string|string[]|null
     */
    function shorten_line(string $text)
    {
        if (is_url($text)) {
            return shorten_link($text);
        }

        return shorten_text($text, $_ENV['MAX_LINE_LENGTH']);
    }
}

// is_url
if (!function_exists('is_url')) {
    /**
     * @param string $text
     * @return bool
     */
    function is_url(string $text): bool
    {
        return (bool)preg_match('~^(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![.,:])~i', $text);
    }
}

// shorten_text
if (!function_exists('shorten_text')) {
    /**
     * @param string $text
     * @param int $max_line_length
     * @return string|string[]
     */
    function shorten_text(string $text, int $max_line_length = 10)
    {
        // cut www.
        $text = str_replace("www.", "", $text);

        // cut ...
        $text = str_replace("...", "", $text);

        // cut to length
        if ($max_line_length < iconv_strlen($text, 'UTF-8')) {
            $text = mb_strimwidth($text, 0, $max_line_length, "...");
        }

        return $text;
    }
}

// shorten_link
if (!function_exists('shorten_link')) {
    /**
     * @param $value
     * @param  string[]  $protocols
     * @return string|string[]|null
     */
    function shorten_link($value, array $protocols = array('https', 'http', 'mail'))
    {
        $links = array();

        // Extract existing links and tags
        $value = preg_replace_callback(
            '~(<a .*?>.*?</a>|<.*?>)~i',
            function ($match) use (&$links) {
                return '<' . array_push($links, $match[1]) . '>';
            },
            $value
        );

        // Extract text links for each protocol
        foreach ((array)$protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':
                    $value = preg_replace_callback(
                        '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![.,:])~i',
                        function ($match) use ($protocol, &$links) {
                            if ($match[1]) {
                                $protocol = $match[1];
                            }
                            $link = $match[2] ?: $match[3];
                            return '<' . array_push(
                                $links,
                                "<a href=\"$protocol://$link\">" . shorten_text($link, $_ENV['MAX_LINE_LENGTH']) . "</a>"
                            ) . '>';
                        },
                        $value
                    );
                    break;
                case 'mail':
                    $value = preg_replace_callback(
                        '~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![.,:])~',
                        function ($match) use (&$links) {
                            return '<' . array_push(
                                $links,
                                "<a href=\"mailto:{$match[1]}\">" . shorten_text(
                                    $match[1]
                                , $_ENV['MAX_LINE_LENGTH']) . "</a>"
                            ) . '>';
                        },
                        $value
                    );
                    break;
                default:
                    $value = preg_replace_callback(
                        '~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![.,:])~i',
                        function ($match) use ($protocol, &$links) {
                            return '<' . array_push(
                                $links,
                                "<a href=\"$protocol://{$match[1]}\">" . shorten_text(
                                    $match[1]
                                , $_ENV['MAX_LINE_LENGTH']) . "</a>"
                            ) . '>';
                        },
                        $value
                    );
                    break;
            }
        }

        // Insert all link
        return preg_replace_callback(
            '/<(\d+)>/',
            function ($match) use (&$links) {
                return $links[$match[1] - 1];
            },
            $value
        );
    }
}

// debug display function
if (!function_exists('ddf')) {
    /**
     * @param $var
     * @param  bool  $die
     */
    function ddf($var, bool $die = true): void
    {
        echo PHP_EOL . gmdate("i:s") . ' ' . PHP_EOL;
        print_r($var);
        flush();
        if ($die) {
            die;
        }
    }
}



// heartbeat - стучим, что бот работает и все с ним ок
if (!function_exists('heartbeat')) {
    /**
     * @return void
     */
    function heartbeat(): void
    {
        if (!empty($_ENV['HEARTBEAT_TOKEN'])) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://uptime.betterstack.com/api/v1/heartbeat/' . $_ENV['HEARTBEAT_TOKEN']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);

            curl_exec($ch);
        }
    }
}

/**
 * Параметры прокси для eleirbag89/telegrambotphp (getUpdates, sendMessage и т.д.).
 * TELEGRAM_PROXY - как в abxtest.com (строка host:port или http://...).
 * TELEGRAM_HTTP_PROXY - как в logtail.ru, если TELEGRAM_PROXY не задан.
 *
 * @return array<string, int|string>
 */
if (!function_exists('telegram_bot_proxy_config')) {
    function telegram_bot_proxy_config(): array
    {
        $raw = trim($_ENV['TELEGRAM_PROXY'] ?? $_ENV['TELEGRAM_HTTP_PROXY'] ?? '');
        if ($raw === '') {
            return [];
        }
        if (preg_match('#^socks5h://#i', $raw)) {
            return [
                'url' => $raw,
                'type' => defined('CURLPROXY_SOCKS5_HOSTNAME') ? CURLPROXY_SOCKS5_HOSTNAME : CURLPROXY_SOCKS5,
            ];
        }
        if (preg_match('#^socks5://#i', $raw)) {
            return ['url' => $raw, 'type' => CURLPROXY_SOCKS5];
        }
        if (preg_match('#^socks4a?://#i', $raw)) {
            return ['url' => $raw, 'type' => CURLPROXY_SOCKS4];
        }

        return ['url' => $raw, 'type' => CURLPROXY_HTTP];
    }
}

/**
 * GET по URL с тем же прокси, что и eleirbag89/telegrambotphp (см. TELEGRAM_PROXY).
 * file_get_contents до api.telegram.org не использует прокси и часто падает по таймауту.
 *
 * @return string|null тело ответа при HTTP 200, иначе null
 */
if (!function_exists('telegram_download_url')) {
    function telegram_download_url(string $url, int $connectTimeout = 30, int $timeout = 120): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        $proxy = telegram_bot_proxy_config();
        if ($proxy !== []) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy['url']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy['type'] ?? CURLPROXY_HTTP);
            if (($proxy['type'] ?? CURLPROXY_HTTP) === CURLPROXY_HTTP) {
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            }
        }

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $httpCode !== 200) {
            return null;
        }

        return $body;
    }
}