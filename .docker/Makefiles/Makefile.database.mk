
## —— 📊 Database 📊 ——

database-init: ## Initialize the database
	@$(call GREEN,"Initializing the database")
	$(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction

database-drop: ## Drop the database
	@$(call RED,"Dropping the database")
	$(SYMFONY_CONSOLE) doctrine:database:drop --force --if-exists

database-reset: ## Reset the database
	@$(call RED,"Resetting the database")
	$(MAKE) database-drop
	$(MAKE) database-init
	@$(call GREEN,"Database reset")
