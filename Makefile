.PHONY: install-phive
install-phive:
	mkdir tools; \
	wget -O tools/phive.phar https://phar.io/releases/phive.phar; \
	wget -O tools/phive.phar.asc https://phar.io/releases/phive.phar.asc; \
	gpg --keyserver pool.sks-keyservers.net --recv-keys 0x9D8A98B29B2D5D79; \
	gpg --verify tools/phive.phar.asc tools/phive.phar; \
	chmod +x tools/phive.phar

.PHONY: setup
setup: install-phive
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phar-ga:latest php tools/phive.phar install --force-accept-unsigned

.PHONY: phpcs
phpcs:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phpcs-ga:latest -s

.PHONY: phpcbf
phpcbf:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phpcs-ga:latest phpcbf

.PHONY: phpstan
phpstan:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project phpdoc/phpstan-ga:latest analyse src --no-progress --configuration phpstan.neon

.PHONY: psalm
psalm:
	docker run -it --rm -v${PWD}:/opt/project -w /opt/project php:7.3 vendor/bin/psalm.phar

.PHONY: test
test:
	docker run -it --rm -v${CURDIR}:/github/workspace phpdoc/phpunit-ga
	docker run -it --rm -v${CURDIR}:/data -w /data php:7.2 -f ./tests/coverage-checker.php 90

.PHONY: pre-commit-test
pre-commit-test: test phpcs phpstan psalm

