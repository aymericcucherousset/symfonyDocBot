PHPSTAN = $(PHP) vendor/bin/phpstan
PHPCSFIXER = $(PHP) vendor/bin/php-cs-fixer

## —— 🛠️ Tools 🛠️ ——

phpstan: ## PHPStan
	$(PHPSTAN)

php-cs-fixer: ## PHP-CS-Fixer
	$(PHPCSFIXER) fix

php-cs-fixer-dry-run: ## PHP-CS-Fixer dry-run
	$(PHPCSFIXER) fix --dry-run --diff

precommit: ## Run precommit checks
	$(MAKE) phpstan
	$(MAKE) php-cs-fixer
	$(MAKE) sf-lint
	@$(call GREEN,"Precommit checks passed")
