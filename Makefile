disable-xdebug=XDEBUG_MODE=off
enable-xdebug=XDEBUG_MODE=coverage
tools-bin-dir ?= bin
env=dev

.PHONY: start
start: ## Start the project
	docker-compose up --build
	symfony server:start

.PHONY: stop
stop: ## Stop the project
	docker-compose down
	symfony server:stop

.PHONY: cache-clear
cache-clear: ## Clear the cache
	$(disable-xdebug) symfony console cache:clear
	$(disable-xdebug) symfony console cache:warmup

.PHONY: cc
cc: cache-clear ## Clear the cache

.PHONY: install-composer
install-composer: ## Install composer
	composer install

.PHONY: install-phparkitect
install-phparkitect: ## Install PHPArkitect
	[ -d $(tools-bin-dir) ] || mkdir -p $(tools-bin-dir)
	wget -O $(tools-bin-dir)/phparkitect.phar https://github.com/phparkitect/arkitect/releases/latest/download/phparkitect.phar

.PHONY: install
install: ## Install the project
	make install-phparkitect
	make install-composer
	make db env=dev
	make db env=test

.PHONY: db-fixtures
db-fixtures: ## Load the fixtures
	symfony console doctrine:fixtures:load -n --env=$(env)

.PHONY: db-schema
db-schema: ## Create the database schema
	$(disable-xdebug) symfony console doctrine:database:drop --if-exists --force --env=$(env)
	$(disable-xdebug) symfony console doctrine:database:create --env=$(env)
	$(disable-xdebug) symfony console doctrine:migration:migrate --no-interaction --allow-no-migration --env=$(env)

.PHONY: db-migration
db-migration: ## Create a new migration
	$(disable-xdebug) symfony console make:migration

.PHONY: db
db: db-schema db-fixtures ## Create the database and load the fixtures

.PHONY: test
test: test-unit test-component test-integration  ## Run the tests

.PHONY: test-unit
test-unit: ## Run the tests
	$(disable-xdebug) php $(tools-bin-dir)/phpunit --testsuite=unit -c tools/phpunit.xml

.PHONY: test-component
test-component: ## Run the tests
	$(disable-xdebug) php $(tools-bin-dir)/phpunit --testsuite=component -c tools/phpunit.xml

.PHONY: test-integration
test-integration: ## Run the tests
	$(disable-xdebug) php $(tools-bin-dir)/phpunit --testsuite=integration -c tools/phpunit.xml

.PHONY: test
test-coverage: ## Run the tests
	$(enable-xdebug) php $(tools-bin-dir)/phpunit --testdox -c tools/phpunit.xml

.PHONY: qa-phpstan
qa-phpstan: ## Run PHPStan
	$(disable-xdebug) php $(tools-bin-dir)/phpstan analyse -c tools/phpstan.neon

.PHONY: qa-php-cs-fixer
qa-php-cs-fixer: ## Run PHP-CS-Fixer (dry run)
	$(disable-xdebug) php $(tools-bin-dir)/php-cs-fixer fix --dry-run --config=tools/php-cs-fixer.php

.PHONY: qa-composer
qa-composer: ## Run Composer Lint
	composer valid

.PHONY: qa-doctrine
qa-doctrine: ## Run Symfony Doctrine Lint
	$(disable-xdebug) symfony console doctrine:schema:valid --skip-sync

.PHONY: qa-twig
qa-twig: ## Run Symfony Twig Lint
	$(disable-xdebug) symfony console lint:twig templates

.PHONY: qa-yaml
qa-yaml: ## Run Symfony Yaml Lint
	$(disable-xdebug) symfony console lint:yaml config --parse-tags

.PHONY: qa-container
qa-container: ## Run Symfony Container Lint
	$(disable-xdebug) symfony console lint:container

.PHONY: qa-security-check
qa-security-check: ## Run Symfony Security Check
	symfony check:security

.PHONY: qa-phpmd
qa-phpmd: ## Run PHPMD
	$(disable-xdebug) php $(tools-bin-dir)/phpmd src text tools/phpmd.xml

.PHONY: qa-phparkitect
qa-phparkitect: ## Run PHPArkitect
	$(disable-xdebug) php $(tools-bin-dir)/phparkitect.phar check --config=tools/phparkitect.php

.PHONY: qa
qa: qa-composer qa-doctrine qa-twig qa-yaml qa-container qa-security-check qa-phpmd qa-php-cs-fixer qa-phpstan qa-phparkitect ## Run analysis tools

.PHONY: fix-cs-fixer
fix-cs-fixer: ## Correction automatique des erreurs de code avec PHP-CS-Fixer
	$(disable-xdebug) php vendor/bin/php-cs-fixer fix  --config=tools/php-cs-fixer.php

.PHONY: fix
fix: fix-cs-fixer ## Fix the code

.PHONY: help
help: ## Show this help.
	@echo "Symfony-Makefile"
	@echo "---------------------------"
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
