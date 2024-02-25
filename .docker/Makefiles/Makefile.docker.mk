DOCKER = docker
DOCKER_COMPOSE = docker-compose --env-file .env.local
DOCKER_WWW = app
EXEC = $(DOCKER_COMPOSE) exec $(DOCKER_WWW)

## —— 🐳 Docker 🐳 ——

docker-up: ## Start the project
	$(DOCKER_COMPOSE) up -d

docker-down: ## Stop the project
	$(DOCKER_COMPOSE) down

docker-restart: ## Restart the project
	$(MAKE) docker-down
	$(MAKE) docker-up

docker-shell: ## Open a shell in the app container
	$(EXEC) /bin/bash
