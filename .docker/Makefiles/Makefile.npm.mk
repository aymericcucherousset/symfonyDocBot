NPM = $(EXEC) npm

## —— 📦 NPM 📦 ——

npm-install: ## Install npm dependencies
	$(NPM) install $(filter-out $@,$(MAKECMDGOALS))

npm-update: ## Update npm dependencies
	$(NPM) update

npm-run: ## Run npm script
	$(NPM) run $(filter-out $@,$(MAKECMDGOALS))

npm-build: ## Build npm assets
	$(NPM) run build

npm-watch: ## Watch npm assets
	$(NPM) run watch

npm-dev: ## Run npm dev
	$(NPM) run dev
