FROM php:8-cli

# Обновляем репозитории и устанавливаем необходимые пакеты
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
        netcat-openbsd \
        zip \
        unzip \
        git && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Установка расширения PDO_Mysql
RUN docker-php-ext-install pdo pdo_mysql

# Установка расширения MySQLi
RUN docker-php-ext-install mysqli

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка часового пояса Москвы
RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime

# CMD: перезапуск main.php после падения (зависания ловим таймаутами cURL в патче Telegram.php)
CMD ["sh", "-c", "while true; do php /app/main.php >> /app/logs/main.log 2>&1; echo \"[$(date -Iseconds)] bot exited, restart in 5s\" >> /app/logs/main.log; sleep 5; done"]