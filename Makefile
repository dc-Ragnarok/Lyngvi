build:
	docker build -t fenrir-stability .

build-run:
	docker run -d fenrir-stability

cs:
	composer cs

csf:
	composer csf

test:
	composer test

test-coverage:
	composer test-coverage
