build: ./php/Dockerfile composer.json composer.lock
	docker-compose build lyngvi

in:
	docker-compose run sh

up: build commands
	docker-compose up lyngvi

detached: build install commands
	docker-compose up lyngvi -d

commands: _register-commands.php
	docker-compose up commands

deploy:
	docker-compose down
	make detached

install:
	docker-compose up install
