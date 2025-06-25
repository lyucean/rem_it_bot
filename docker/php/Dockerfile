FROM php:8-cli

# Обновляем ключи GPG и репозитории
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends gnupg && \
    apt-key update && \
    apt-get update -y

WORKDIR /app

# нужно для проверки доступа к БД
RUN apt-get install -y netcat-openbsd

# Установка расширения zip для работы с composer
RUN apt-get install -y zip unzip

# Установка расширения git для работы с composer
RUN apt-get install -y git

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