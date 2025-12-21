# CoffeeShop API - Development Commands
# =====================================
# Run `make help` to see available commands

.PHONY: help setup up down restart logs shell test test-unit test-integration clean

# Default target
help:
	@echo "CoffeeShop API - Available Commands"
	@echo "===================================="
	@echo ""
	@echo "  make setup          - First-time setup: build containers and install dependencies"
	@echo "  make up             - Start all containers in the background"
	@echo "  make down           - Stop and remove all containers"
	@echo "  make restart        - Restart all containers"
	@echo "  make logs           - Follow logs from all containers"
	@echo "  make logs-php       - Follow logs from PHP container only"
	@echo "  make shell          - Open a shell in the PHP container"
	@echo "  make mysql          - Open MySQL CLI"
	@echo ""
	@echo "  make test           - Run all tests"
	@echo "  make test-unit      - Run unit tests only"
	@echo "  make test-integration - Run integration tests only"
	@echo ""
	@echo "  make composer-install - Install PHP dependencies"
	@echo "  make composer-update  - Update PHP dependencies"
	@echo ""
	@echo "  make clean          - Remove containers, volumes, and generated files"
	@echo ""

# First-time setup
setup: 
	@echo "🚀 Setting up CoffeeShop API..."
	docker-compose build --no-cache
	docker-compose up -d
	@echo "⏳ Waiting for containers to be ready..."
	@sleep 5
	docker-compose exec php composer install
	@echo ""
	@echo "✅ Setup complete!"
	@echo "   API available at: http://localhost:8080"
	@echo "   API docs at:      http://localhost:8080/docs"
	@echo ""

# Start containers
up:
	docker-compose up -d
	@echo "✅ Containers started. API available at http://localhost:8080"

# Stop containers
down:
	docker-compose down

# Restart containers
restart: down up

# View logs
logs:
	docker-compose logs -f

logs-php:
	docker-compose logs -f php

logs-nginx:
	docker-compose logs -f nginx

logs-mysql:
	docker-compose logs -f mysql

# Shell access
shell:
	docker-compose exec php sh

mysql:
	docker-compose exec mysql mysql -u coffeeshop -psecret coffeeshop

# Testing
test:
	docker-compose exec php composer test

test-unit:
	docker-compose exec php composer test:unit

test-integration:
	docker-compose exec php composer test:integration

# Composer commands
composer-install:
	docker-compose exec php composer install

composer-update:
	docker-compose exec php composer update

# Cleanup
clean:
	docker-compose down -v --rmi local
	rm -rf vendor/
	@echo "✅ Cleaned up containers, volumes, and vendor directory"

