USER_ID=$(shell id -u)

DC = @USER_ID=$(USER_ID) docker compose
DC_RUN = ${DC} run --rm scoring_app
DC_EXEC = ${DC} exec scoring_app

PHONY: help
.DEFAULT_GOAL := help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

init: down build up success-message ## Initialize environment

build: ## Build services.
	${DC} build $(c)

up: ## Create and start services.
	${DC} up -d $(c)

stop: ## Stop services.
	${DC} stop $(c)

start: ## Start services.
	${DC} start $(c)

down: ## Stop and remove containers and volumes.
	${DC} down -v $(c)

restart: stop start ## Restart services.

console: ## Login in console.
	${DC_EXEC} /bin/bash

install: ## Install dependencies without running the whole application.
	${DC_RUN} composer install

success-message:
	@echo "You can now access the application at http://localhost:8337"
	@echo "Good luck! 🚀"

cs:
	${DC_EXEC} bin/console cache:clear --env=dev
	${DC_EXEC}  ./vendor/bin/php-cs-fixer fix --diff --using-cache=yes

fixture:
	${DC_EXEC} bin/console cache:clear --env=dev
	${DC_EXEC} php bin/console doctrine:fixtures:load --no-interaction

test:
	${DC_EXEC} bin/console cache:clear --env=dev
	${DC_EXEC} vendor/bin/phpunit