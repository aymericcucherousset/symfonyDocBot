SYMFONY_CONSOLE = $(PHP) bin/console
SYMFONY = $(EXEC) symfony

## —— 🚀 Symfony 🚀 ——

sf: ## List all Symfony commands
	$(SYMFONY) list

sf-cache-clear: ## Clear the cache
	$(SYMFONY_CONSOLE) cache:clear

sf-lint: ## Lint twig and yaml files
	$(SYMFONY_CONSOLE) lint:twig templates
	$(SYMFONY_CONSOLE) lint:yaml config compose*
