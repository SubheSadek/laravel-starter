# ===============================
# Makefile for Laravel + Docker
# ===============================

# Containers
PHP_CONTAINER=php
NGINX_CONTAINER=nginx
REDIS_CONTAINER=redis
POSTGRES_CONTAINER=postgres
COMPOSER_CONTAINER=composer
ARTISAN_CONTAINER=artisan

# Docker Compose commands
DC=docker-compose
UP=$(DC) up -d
DOWN=$(DC) down
BUILD=$(DC) build --no-cache

# -------------------------------
# Main commands
# -------------------------------

# Start all containers
up:
	$(UP)

# Stop all containers
down:
	$(DOWN)

# Restart all containers
rs:
	$(DOWN) && $(UP)

# Rebuild all containers
build:
	$(BUILD)

# Rebuild PHP container only
build-php:
	$(DC) build --no-cache $(PHP_CONTAINER)

# Nginx Restart
nginx-restart:
	docker-compose restart $(NGINX_CONTAINER)

# Enter PHP container shell
bash:
	docker exec -it $(PHP_CONTAINER) sh

# Enter Composer container shell
composer-bash:
	docker-compose run --rm $(COMPOSER_CONTAINER) sh

# Run arbitrary Artisan command
artisan:
	docker exec -it $(ARTISAN_CONTAINER) php /var/www/html/artisan $(cmd)

# Run Composer command
composer:
	docker exec -it $(COMPOSER_CONTAINER) composer $(cmd)

# Run migrations
migrate:
	docker exec -it $(ARTISAN_CONTAINER) php /var/www/html/artisan migrate

# Run migrations fresh
migrate-fresh:
	docker exec -it $(ARTISAN_CONTAINER) php /var/www/html/artisan migrate:fresh

# Seed database
db-seed:
	docker exec -it $(ARTISAN_CONTAINER) php /var/www/html/artisan db:seed

# Rollback last migration
migrate-rollback:
	docker exec -it $(ARTISAN_CONTAINER) php /var/www/html/artisan migrate:rollback

# Clear Laravel caches
cache-clear:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

# Test PHP version
test: 
	docker-compose exec php php artisan $(cmd)

# Enter PostgreSQL shell
psql:
	docker exec -it $(POSTGRES_CONTAINER) psql -U laravel -d laravel

# Stop all and remove volumes
clean:
	$(DOWN) -v

# Tail logs for all containers
logs:
	$(DC) logs -f

# Tail logs for a specific container
logs-container:
	docker logs -f $(container)
