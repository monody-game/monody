.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | gawk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

vendor: composer.lock
	composer install --prefer-dist

.PHONY: install
install: vendor

.PHONY: server
server:
	docker-compose up -d

.PHONY: down
down:
	docker-compose down

.PHONY: format
format: install
	vendor/bin/pint --test

.PHONY: fix
fix: install
	vendor/bin/pint

.PHONY: migrate
migrate: install
	php artisan migrate:fresh

.PHONY: seed
seed: migrate
	php artisan db:seed
	php artisan passport:install

.PHONY: lint
lint: install
	vendor/bin/phpstan analyse --memory-limit=2G

.PHONY: tests
tests: install
	vendor/bin/phpunit

.PHONY: tt
tt: install
	phpunit-watcher watch
