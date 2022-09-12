include docker.mk

.PHONY: test

DRUPAL_VER ?= 9
PHP_VER ?= 8.1

test:
	cd ./tests/$(DRUPAL_VER) && PHP_VER=$(PHP_VER) ./run.sh

################################################################################
## Docker.
################################################################################

.PHONY: docker-upd
docker-upd: ## Start containers in detached mode.
	@echo "$(COLOR_LIGHT_GREEN)Starting up containers for $(COMPOSE_PROJECT_NAME)...$(COLOR_NC)"
	@docker-compose up -d

.PHONY: docker-stop
docker-stop: ## Stop containers.
	@echo "$(COLOR_LIGHT_GREEN)Stopping containers for $(COMPOSE_PROJECT_NAME)...$(COLOR_NC)"
	@docker-compose stop

.PHONY: docker-down
docker-down: ## Remove containers.
	@echo "$(COLOR_LIGHT_GREEN)Removing containers for $(COMPOSE_PROJECT_NAME)...$(COLOR_NC)"
	@docker-compose down

.PHONY: docker-shell
docker-shell: ## Open a command line in the web container.
	@docker-compose exec php bash

.PHONY: compile-sass
compile-sass: ## Open a command line in the web container.
	@sass web/themes/custom/wiw_theme/scss/style.scss web/themes/custom/wiw_theme/css/style.css
