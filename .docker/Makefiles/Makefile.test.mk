PHPUNIT_PATH = vendor/bin/phpunit
DOCKER_COMPOSE_TEST = docker compose -f compose.test.yaml
EXEC_TEST = $(DOCKER_COMPOSE_TEST) exec -t $(DOCKER_WWW)
SYMFONY_CONSOLE_TEST = $(EXEC_TEST) bin/console
EXEC_COVERAGE = $(DOCKER_COMPOSE_TEST) exec -t -e XDEBUG_MODE=coverage $(DOCKER_WWW)

## —— 🧪 Tests 🧪 ——

test: ## Run tests
	$(EXEC) $(PHPUNIT_PATH)

test-coverage: ## Run tests with coverage
	$(EXEC_COVERAGE) $(PHPUNIT_PATH) --coverage-html var/coverage

test-coverage-ci: ## Run tests with coverage for CI
	$(EXEC_TEST) mkdir -p public/symfony-symfony-docs/5.4
	$(EXEC_COVERAGE) $(PHPUNIT_PATH) --coverage-clover phpunit.coverage.xml --log-junit phpunit.report.xml

install-test: ## Install the application
	@$(call GREEN,"Installing the application")
	$(DOCKER_COMPOSE_TEST) up -d
	$(EXEC_TEST) composer install
	$(SYMFONY_CONSOLE_TEST) doctrine:database:create --if-not-exists
	$(SYMFONY_CONSOLE_TEST) doctrine:migrations:migrate --no-interaction
	$(EXEC_TEST) npm install && npm run build
	$(SYMFONY_CONSOLE_TEST) c:c
	@$(call GREEN,"Application installed")

docker-down-ci:
	$(DOCKER_COMPOSE_TEST) down

sf-lint-ci: ## Lint twig and yaml files
	$(SYMFONY_CONSOLE_TEST) lint:twig templates
	$(SYMFONY_CONSOLE_TEST) lint:yaml config compose*

phpstan-ci: ## Run PHPStan
	$(EXEC_TEST) vendor/bin/phpstan

php-cs-fixer-dry-run-ci: ## Run PHP CS Fixer
	$(EXEC_TEST) vendor/bin/php-cs-fixer fix --dry-run --diff

test-ci: ## Run tests
	$(EXEC_TEST) mkdir -p public/symfony-symfony-docs/5.4
	$(EXEC_TEST) $(PHPUNIT_PATH)