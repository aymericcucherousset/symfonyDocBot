# Variables
RM = rm -rf

# Colors
GREEN = /bin/echo -e "\x1b[32m\#\# $1\x1b[0m"
RED = /bin/echo -e "\x1b[31m\#\# $1\x1b[0m"

include .docker/Makefiles/Makefile.docker.mk
include .docker/Makefiles/Makefile.php.mk
include .docker/Makefiles/Makefile.composer.mk
include .docker/Makefiles/Makefile.symfony.mk
include .docker/Makefiles/Makefile.test.mk
include .docker/Makefiles/Makefile.database.mk
include .docker/Makefiles/Makefile.tools.mk
include .docker/Makefiles/Makefile.app.mk

## —— 🛠️  Others ——
help: ## List of commands
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
