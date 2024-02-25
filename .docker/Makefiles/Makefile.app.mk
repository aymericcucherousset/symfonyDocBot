
## —— 🚀 App 🚀 ——

install: ## Install the application
	@$(call GREEN,"Installing the application")
	$(MAKE) docker-up
	$(MAKE) composer-install
	$(MAKE) database-init
	$(MAKE) sf-cache-clear
	@$(call GREEN,"Application installed")

download-documentation: ## Download the documentation
	@$(call GREEN,"Downloading the documentation")
	$(SYMFONY_CONSOLE) doc:download $(filter-out $@,$(MAKECMDGOALS))
	@$(call GREEN,"Documentation downloaded")

generate-embedding: ## Generate embedding
	@$(call GREEN,"Generating embedding")
	$(SYMFONY_CONSOLE) app:generate-embedding $(filter-out $@,$(MAKECMDGOALS))
	@$(call GREEN,"Embedding generated")

install-documentation: ## Install the documentation
	$(MAKE) download-documentation $(filter-out $@,$(MAKECMDGOALS))
	$(MAKE) generate-embedding $(filter-out $@,$(MAKECMDGOALS))
