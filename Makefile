# Выполним по умолчанию, при запуске пустого make
.DEFAULT_GOAL := help

# Подключим файл конфигурации
include app/.env

# И укажем его для docker-compose
ENV = --env-file app/.env

# Добавим красоты и чтоб наши команды было видно в теле скрипта
PURPLE = \033[1;35m $(shell date +"%H:%M:%S") --
RESET = --\033[0m

# Считываем файл, всё что содержит двойную решётку # Это описание к командам
help:
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-17s\033[0m %s\n", $$1, $$2}'
.PHONY: help

delay: # Задержка, она требуется, чтоб MYSQL успел инициализироваться
	@sleep 3
.PHONY: delay

wait-for-mysql: ## Задержка для MySQL, необходимая для инициализации, работает, только если mysql будет торчать наружу
	@echo "$(PURPLE)Ожидание инициализации MySQL$(RESET)"
	@seconds=0; \
	while ! docker-compose $(ENV) exec rem_it_bot_php-cli_1 nc -z mysql 3306; do \
		seconds=$$((seconds+1)); \
		sleep 1; \
		echo "Прошло: $$seconds сек."; \
	done
.PHONY: wait-for-mysql

log: ## Вывод логов
ifeq ($(ENVIRONMENT), developer)
	@tail -f app/logs/success_runner.log
else
	@tail -f app/logs/error_runner.log
endif
.PHONY: log

# Если это developer окружение, то подключим debug профиль
PROFILE =
ifeq ($(ENVIRONMENT),developer)
	PROFILE := --profile main,debug
else
	PROFILE := --profile main
endif

init: ## Инициализация проекта
init: docker-down clean docker-pull docker-build docker-up composer-install wait-for-mysql import-db migrate log

update: ## Пересобрать контейнер, обновить композер и миграции
update: docker-down clean docker-pull docker-build docker-up composer-install wait-for-mysql migrate log

restart: ## Restart docker containers
restart: docker-down clean docker-up log

php-bash: ## Подключается к контейнеру PHP
	docker-compose $(ENV) exec php-cli bash

composer: ## Подключается к контейнеру PHP и работаем с composer
	docker-compose $(ENV) exec php-cli bash -c "composer -V; bash"

migrate: ## Применить миграции
	@echo "$(PURPLE) Применить миграции $(RESET)"
	docker-compose $(ENV) run --rm php-cli php vendor/bin/phinx migrate --configuration phinx.php

rollback: ## Отменить последнюю миграцию
	@echo "$(PURPLE) Применить миграции $(RESET)"
	docker-compose $(ENV) run --rm php-cli php vendor/bin/phinx rollback --configuration phinx.php

import-db: ## Импорт образа бд
	@echo "$(PURPLE) Импорт БД (если нужен) $(RESET)"
	@#gunzip < ./app/db/dump.sql.gz | docker-compose $(ENV) exec -T mysql mysql -uroot -p${MYSQL_ROOT_PASSWORD} BD

composer-install: ## Поставим пакеты композера
	@echo "$(PURPLE) Поставим пакеты композера $(RESET)"
	@docker-compose $(ENV) run --rm composer

docker-up: ## Поднимем контейнеры
	@echo "$(PURPLE) Поднимем контейнеры $(RESET)"
	docker-compose $(ENV) $(PROFILE) up -d

docker-build: ## Соберём образы
	@echo "$(PURPLE) Соберём образы $(RESET)"
	docker-compose $(ENV) $(PROFILE) build

docker-pull: ## Поучим все контейнеры
	@echo "$(PURPLE) Поучим все контейнеры $(RESET)"
	docker-compose $(ENV) $(PROFILE) pull --include-deps

docker-down: ## Остановим контейнеры
	@echo "$(PURPLE) Остановим контейнеры $(RESET)"
	docker-compose $(ENV) $(PROFILE) down --remove-orphans

clean:  ## Очистим папку логов
	@echo "$(PURPLE) Очистим папку логов $(RESET)"
	rm -f app/logs/*