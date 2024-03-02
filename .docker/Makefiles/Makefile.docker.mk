DOCKER = docker
DOCKER_COMPOSE = docker-compose
DOCKER_WWW = app
EXEC = $(DOCKER_COMPOSE) exec -t $(DOCKER_WWW)
EXEC_COVERAGE = $(DOCKER_COMPOSE) exec -t -e XDEBUG_MODE=coverage $(DOCKER_WWW)

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
