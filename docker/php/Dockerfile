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

# CMD
CMD ["sh", "-c", "php /app/main.php >> /app/logs/main.log 2>&1 && tail -f /dev/null"]