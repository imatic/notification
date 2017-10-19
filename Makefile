.PHONY: test
test: phpunit phpmd phpcs phpda

.PHONY: phpunit
phpunit:
	./vendor/bin/phpunit

.PHONY: phpmd
phpmd:
	./vendor/bin/phpmd src/,tests/ text phpmd.xml

.PHONY: phpcs
phpcs:
	./vendor/bin/php-cs-fixer fix --dry-run

.PHONY: phpda
phpda:
	./vendor/bin/phpda analyze phpda.yml

composer:
ifeq ($(shell which composer 2> /dev/null),)
	curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=$$(pwd) --filename=composer
else
	ln --symbolic $$(which composer) composer.phar
endif

.PHONY: update-test
update-test: | composer
	./composer install

.PHONY: configure-pipelines
configure-pipelines:
	apt-get update
	apt-get install --yes git
	docker-php-ext-install bcmath

