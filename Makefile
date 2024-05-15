# Выполним по умолчанию, при запуске пустого make
.DEFAULT_GOAL := help

# Подключим файл конфигурации
include app/.env

# И укажем его для docker compose
ENV = --env-file app/.env

# Дата время
BACKUP_DATETIME := $(shell date '+%Y-%m-%d')

# Добавим красоты и чтоб наши команды было видно в теле скрипта
PURPLE = \033[1;35m $(shell date +"%H:%M:%S") --
RESET = --\033[0m

# Считываем файл, всё что содержит двойную решётку # Это описание к командам
help:
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-17s\033[0m %s\n", $$1, $$2}'
.PHONY: help

wait-for-mysql: ## Задержка для MySQL, необходимая для инициализации, работает, только если mysql будет торчать наружу
	@echo "$(PURPLE)Ожидание инициализации MySQL$(RESET)"
	@seconds=0; \
	while ! docker compose $(ENV) exec -T php-cli nc -z mysql 3306; do \
		seconds=$$((seconds+1)); \
		sleep 1; \
		echo "Прошло: $$seconds сек."; \
	done
.PHONY: wait-for-mysql

log: ## Вывод логов
ifeq ($(ENVIRONMENT), developer)
	@tail -f app/logs/main.log
else
	@tail app/logs/main.log
endif
.PHONY: log

# Если это developer окружение, то подключим debug профиль
PROFILE =
ifeq ($(ENVIRONMENT),developer)
	PROFILE := --profile dev
else
	PROFILE := --profile main
endif

init: ## Инициализация проекта
init: clean docker-down docker-pull docker-build docker-up composer-install wait-for-mysql migrate update-release-date

update: ## Пересобрать контейнер, обновить композер и миграции
update: clean docker-down docker-pull docker-build docker-up composer-install wait-for-mysql migrate

restart: ## Restart docker containers
restart: clean docker-down docker-up log

php-bash: ## Подключается к контейнеру PHP
	docker compose $(ENV) exec php-cli bash

composer: ## Подключается к контейнеру PHP и работаем с composer
	docker compose $(ENV) exec php-cli bash -c "composer -V; bash"

migrate: ## Применить миграции
	@echo "$(PURPLE) Применить миграции $(RESET)"
	docker compose $(ENV) run --rm php-cli php vendor/bin/phinx migrate --configuration phinx.php

rollback: ## Отменить последнюю миграцию
	@echo "$(PURPLE) Применить миграции $(RESET)"
	docker compose $(ENV) run --rm php-cli php vendor/bin/phinx rollback --configuration phinx.php

composer-install: ## Поставим пакеты композера
	@echo "$(PURPLE) Поставим пакеты композера $(RESET)"
	@docker compose $(ENV) run --rm composer

docker-up: ## Поднимем контейнеры
	@echo "$(PURPLE) Поднимем контейнеры $(RESET)"
	docker compose $(ENV) $(PROFILE) up -d

docker-build: ## Соберём образы
	@echo "$(PURPLE) Соберём образы $(RESET)"
	docker compose $(ENV) $(PROFILE) build

docker-pull: ## Поучим все контейнеры
	@echo "$(PURPLE) Поучим все контейнеры $(RESET)"
	docker compose $(ENV) $(PROFILE) pull --include-deps

docker-down: ## Остановим контейнеры
	@echo "$(PURPLE) Остановим контейнеры $(RESET)"
	docker compose $(ENV) $(PROFILE) down --remove-orphans

clean:  ## Очистим папку логов
	@echo "$(PURPLE) Очистим папку логов $(RESET)"
	rm -f app/logs/*

import-dump:  ## Импорт тестовой БД из дампа для разработки
	@echo "$(PURPLE) Импорт тестовой БД из дампа для разработки $(RESET)"
	@if [ -f "app/db/dump/RIB_test.sql" ]; then \
		docker compose $(ENV) exec -T mysql sh -c 'exec mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}"' < "app/db/dump/RIB_test.sql"; \
	else \
		echo "Тестовый дампа нет"; \
	fi

save-dump:  ## Снимем тестовой дамп БД дампа для разработки
	@echo "$(PURPLE) Снимем дамп с БД $(RESET)"
	docker compose $(ENV) exec mysql sh -c 'exec mysqldump -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}"' > "app/db/dump/RIB_test.sql"

backup-db:  ## Снимем дамп с БД
	@echo "$(PURPLE) Снимем дамп с БД $(RESET)"
	docker compose $(ENV) exec mysql sh -c 'exec mysqldump -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}"' | gzip > "${BACKUPS_FOLDER}/RIB_$(BACKUP_DATETIME).sql.gz"

backup-file:  ## Делаем архив данных
	@echo "$(PURPLE) Создадим архив файлов $(RESET)"
	tar -cvzf ${BACKUPS_FOLDER}/RIB_${BACKUP_DATETIME}.file.gz ./app/file/*

update-release-date: ## Перезаписать дату релиза
	@echo "$(PURPLE) Перезапишем дату релиза $(RESET)"
	@awk -v date="RELEASE_DATE=\"$(shell date '+%Y-%m-%d_%H.%M')\"" '/^RELEASE_DATE/{print date; found=1; next} 1; END {if (!found) print date}' app/.env > app/.env.tmp
	@mv app/.env.tmp app/.env

