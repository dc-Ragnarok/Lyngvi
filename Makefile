build:
	docker build -t fenrir-stability .

build-run:
	docker rm --force Lyngvi; \
	docker run --name Lyngvi -d fenrir-stability

cs:
	composer cs

csf:
	composer csf

test:
	composer test

test-coverage:
	composer test-coverage
