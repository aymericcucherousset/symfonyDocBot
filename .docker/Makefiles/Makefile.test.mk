PHPUNIT_PATH = vendor/bin/phpunit

## —— 🧪 Tests 🧪 ——

test: ## Run tests
	$(EXEC) $(PHPUNIT_PATH)

test-coverage: ## Run tests with coverage
	$(EXEC_COVERAGE) $(PHPUNIT_PATH) --coverage-html var/coverage

test-coverage-ci: ## Run tests with coverage for CI
	$(EXEC_COVERAGE) $(PHPUNIT_PATH) --coverage-clover phpunit.coverage.xml --log-junit phpunit.report.xml
