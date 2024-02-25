COMPOSER = $(EXEC) composer

## —— 🧙‍♂️ Composer 🧙‍♂️ ——

composer-install: ## Install composer dependencies
	$(COMPOSER) install

composer-update: ## Update composer dependencies
	$(COMPOSER) update

composer-require: ## Require a composer package
	$(COMPOSER) require $(filter-out $@,$(MAKECMDGOALS))

composer-require-dev: ## Require a composer package for development
	$(COMPOSER) require --dev $(filter-out $@,$(MAKECMDGOALS))

composer-remove: ## Remove a composer package
	$(COMPOSER) remove $(filter-out $@,$(MAKECMDGOALS))
