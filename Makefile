export red=`tput setaf 1`
export green=`tput setaf 2`
export yellow=`tput setaf 3`
export cyan=`tput setaf 6`
export reset=`tput sgr0`

PROJECT_NAME = tg_bot
APP_CONTANER_COMMAND_PHP = @docker exec -it $(PROJECT_NAME)_php
APP_CONTANER_COMMAND_NGINX = @docker exec -it $(PROJECT_NAME)_nginx
APP_CONTANER_COMMAND_PGSQL = @docker exec -it $(PROJECT_NAME)_db
APP_CONTANER_COMMAND_PGSQL_NO_T = @docker exec -i $(PROJECT_NAME)_db
APP_MESSAGE = @echo  "Локальное приложение  ${cyan}запущено${reset}"
COMPOSE_DEV = docker compose -f ./docker/docker-compose.yml  --project-name $(PROJECT_NAME)

about:
	@echo "Выполняйте нужные действия с помощью ${yellow}make [имякоманды]${reset}, доступные команды: \
     \n ${green}migrate${reset} - применит миграции \
     \n ${green}docker.start.all${reset} - Запустит все контейнеры приложения (соберет образы, если их нет) \
     \n ${green}docker.stop.all${reset} - Остановит все контейнеры приложения \
     \n ${green}docker.restart.all${reset} - Остановит все контейнеры приложения и запустит их заново \
	 \n ${green}docker.rebuild.all${reset} - Остановит все контейнеры приложения, пересоберет их запустит их заново \
	 \n ${green}drop.tables${reset} - Очистит схему базы данных \
	"

# DOCKER---------------------------------------
sh.php:
	$(APP_CONTANER_COMMAND_PHP) sh -l
sh.nginx:
	$(APP_CONTANER_COMMAND_NGINX) sh
sh.db:
	$(APP_CONTANER_COMMAND_PGSQL) sh

docker.start.all:
	$(COMPOSE_DEV)  up -d
	$(APP_MESSAGE)

docker.stop.all:
	$(COMPOSE_DEV)  stop   

docker.rebuild.all: docker.stop.all
	$(COMPOSE_DEV)  up -d --build
	$(APP_MESSAGE)

docker.restart.all: docker.stop.all docker.start.all

docker.php.sh:
	$(APP_CONTANER_COMMAND_PHP) sh

#APPLICATION--------------------------------------
composer.install:
	@echo "Устанавливаем ${yellow}зависимости${reset}..."
	$(APP_CONTANER_COMMAND_PHP) composer install
drop.tables:
	$(APP_CONTANER_COMMAND_PGSQL) psql -U $(PROJECT_NAME)_user $(PROJECT_NAME)_pgsql_db -c \
	"DROP SCHEMA public CASCADE; CREATE SCHEMA public;"
	@echo "Таблицы в базе данных ${yellow}$(PROJECT_NAME)_pgsql_db${reset} ${red}удалены${reset}"
migrate:
	@echo "Выполняем ${yellow}миграции${reset}..."
	$(APP_CONTANER_COMMAND_PGSQL_NO_T) psql $(PROJECT_NAME)_pgsql_db -U \
	$(PROJECT_NAME)_user < migrations/schema.sql
set.webhook:
	$(APP_CONTANER_COMMAND_PHP) php src/set_hook.php

# SHOW DB-----------------------------------------
sh.db.test:
	$(APP_CONTANER_COMMAND_PGSQL) psql -U $(PROJECT_NAME)_user $(PROJECT_NAME)_pgsql_db

