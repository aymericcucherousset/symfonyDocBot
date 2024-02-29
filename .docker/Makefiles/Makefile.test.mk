PHPUNIT_PATH = vendor/bin/phpunit

## —— 🧪 Tests 🧪 ——

test: ## Run tests
	$(EXEC) $(PHPUNIT_PATH)

test-coverage: ## Run tests with coverage
	$(EXEC_COVERAGE) $(PHPUNIT_PATH) --coverage-html var/coverage
