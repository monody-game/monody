dc := USER_ID=$(user) GROUP_ID=$(group) docker-compose

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | gawk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

composer.lock: composer.json
	composer update

vendor: composer.lock
	composer install --prefer-dist

.PHONY: install
install: vendor

.PHONY: server
server: install
	$(dc) up

.PHONY: format
format: install
	vendor\bin\php-cs-fixer fix --dry-run

.PHONY: fix
fix: install
	vendor\bin\php-cs-fixer fix

.PHONY: migrate
migrate: install
	php artisan migrate:fresh

.PHONY: seed
seed: migrate
	php artisan db:seed
	php artisan passport:install

.PHONY: compile
compile:
	sass assets\scss\style.scss public\style.css --no-source-map --watch

.PHONY: lint
lint: vendor\autoload.php install
	vendor\bin\phpstan analyse --memory-limit=2G

.PHONY: tests
tests: install
	vendor\bin\phpunit --stop-on-failure

.PHONY: tt
tt: install
	vendor\bin\phpunit-watcher watch
