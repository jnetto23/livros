# ===== Variáveis =====
TARGET ?= dev
UID := $(shell id -u)
GID := $(shell id -g)

DC_DEV  := UID=$(UID) GID=$(GID) TARGET=$(TARGET) docker compose
DC_PROD := UID=$(UID) GID=$(GID) TARGET=prod docker compose -f docker-compose.yml -f docker-compose.prod.yml

APP_SVC := php
NGINX_SVC := nginx
DB_SVC := mysql

.PHONY: up down restart logs-app logs-nginx logs-db bash test artisan composer migrate seed seed-biblioteca fresh tinker prune \
        build-prod up-prod down-prod restart-prod logs-app-prod

# ===== Dev (default) =====
up:
	$(DC_DEV) up -d --build
	@echo "Aguardando containers ficarem prontos..."
	@sleep 5
	@echo "Rodando migrations..."
	@$(DC_DEV) exec -T $(APP_SVC) php artisan migrate --force || echo "Migrations já executadas ou erro ignorado"
	@echo "Rodando seeds..."
	@$(DC_DEV) exec -T $(APP_SVC) php artisan db:seed --force || echo "Seeds já executados ou erro ignorado"
	@echo "✅ Projeto pronto!"

down:
	$(DC_DEV) down

restart:
	$(DC_DEV) down
	$(DC_DEV) up -d --build

logs-app:
	$(DC_DEV) logs -f $(APP_SVC)
logs-nginx:
	$(DC_DEV) logs -f $(NGINX_SVC)
logs-db:
	$(DC_DEV) logs -f $(DB_SVC)

bash:
	$(DC_DEV) exec $(APP_SVC) bash

test:
	$(DC_DEV) exec $(APP_SVC) ./vendor/bin/pest --testsuite=Unit

artisan:
	$(DC_DEV) exec $(APP_SVC) php artisan $(cmd)

composer:
	$(DC_DEV) exec $(APP_SVC) composer $(cmd)

migrate:
	$(DC_DEV) exec $(APP_SVC) php artisan migrate
seed:
	$(DC_DEV) exec $(APP_SVC) php artisan db:seed
seed-biblioteca:
	$(DC_DEV) exec $(APP_SVC) php artisan db:seed --class=Database\\Seeders\\BibliotecaSeeder
fresh:
	$(DC_DEV) exec $(APP_SVC) php artisan migrate:fresh --seed
tinker:
	$(DC_DEV) exec $(APP_SVC) php artisan tinker

prune:
	$(DC_DEV) down -v --remove-orphans
	docker system prune -f

# ===== Prod (override sem mexer no compose base) =====
build-prod:
	$(DC_PROD) build --no-cache

up-prod:
	$(DC_PROD) up -d --build
	@echo "Aguardando containers ficarem prontos..."
	@sleep 5
	@echo "Rodando migrations..."
	@$(DC_PROD) exec -T $(APP_SVC) php artisan migrate --force || echo "Migrations já executadas ou erro ignorado"
	@echo "Rodando seeds..."
	@$(DC_PROD) exec -T $(APP_SVC) php artisan db:seed --force || echo "Seeds já executados ou erro ignorado"
	@echo "✅ Projeto pronto!"

down-prod:
	$(DC_PROD) down

restart-prod:
	$(DC_PROD) down
	$(DC_PROD) up -d --build

logs-app-prod:
	$(DC_PROD) logs -f $(APP_SVC)
