build: ./php/Dockerfile
	docker compose build lyngvi

in:
	docker-compose exec lyngvi sh

up: build
	docker-compose up

commands: _register-commands.php
	docker-compose exec lyngvi php ./_register-commands.php

deploy:
	docker-compose down
	make build
	docker-compose up -d
	make commands

cs:
	composer cs

csf:
	composer csf

test:
	composer test

test-coverage:
	composer test-coverage
