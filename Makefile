COVERS_VALIDATOR=php -d zend.enable_gc=0 vendor-bin/covers-validator/bin/covers-validator
HUMBUG=phpdbg -qrr vendor-bin/humbug/bin/humbug --options="--exclude-group=integration"
PHPDBG=phpdbg -qrr -d zend.enable_gc=0 bin/phpunit
PHP_CS_FIXER=php -d zend.enable_gc=0 vendor-bin/php-cs-fixer/bin/php-cs-fixer
PHPSTAN=php -d zend.enable_gc=0 -dmemory_limit=1G vendor-bin/phpstan/bin/phpstan
PHPUNIT=php -d zend.enable_gc=0 bin/phpunit
PHPUNIT_SYMFONY=php -d zend.enable_gc=0 vendor-bin/symfony/bin/phpunit

.DEFAULT_GOAL := help
.PHONY: test tm ts tc tm cs phpstan cs


help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'


##
## Tests
##---------------------------------------------------------------------------

test:           ## Run all the tests
test: tu ts

tu:             ## Run the tests for the core library
tu: vendor/phpunit vendor-bin/covers-validator/vendor
	$(COVERS_VALIDATOR)
	$(PHPUNIT)

ts:             ## Run the tests for the Symfony Bridge
ts: vendor-bin/symfony/vendor vendor-bin/covers-validator/vendor
	$(COVERS_VALIDATOR) -c phpunit_symfony.xml.dist
	$(PHPUNIT_SYMFONY) -c phpunit_symfony.xml.dist

tc:             ## Run the tests with coverage
tc: vendor/phpunit
	$(PHPDBG) --exclude-group=integration --coverage-text --coverage-html=dist/coverage --coverage-clover=dist/clover.xml

tm:             ## Run the tests for mutation testing
tm: vendor/phpunit vendor-bin/humbug/vendor
	$(HUMBUG)

tp:             ## Run Blackfire performance tests
tp: vendor vendor-bin/profiling/vendor
	php profiling/scenario0/blackfire.php
	php profiling/scenario1/blackfire.php
	php profiling/scenario2/blackfire.php
	php profiling/scenario3/blackfire.php


##
## Code Analysis
##
##---------------------------------------------------------------------------

phpstan:        ## Run PHPStan analysis
phpstan: vendor-bin/phpstan/vendor
	$(PHPSTAN) analyze -c phpstan.neon -l4 src tests


##
## Code Style
##---------------------------------------------------------------------------

cs:             ## Run the CS Fixer
cs:	vendor-bin/php-cs-fixer/vendor
	rm -rf fixtures/Bridge/Symfony/Application/cache/*
	$(PHP_CS_FIXER) fix


##
## Rules from files
##---------------------------------------------------------------------------

composer.lock: composer.json
	@echo compose.lock is not up to date.

vendor: composer.lock
	composer install

vendor/phpunit: composer.lock
	composer install

vendor-bin/symfony/vendor: vendor-bin/symfony/composer.lock
	composer bin symfony install

vendor-bin/symfony/composer.lock: vendor-bin/symfony/composer.json
	@echo symfony compose.lock is not up to date.

vendor-bin/php-cs-fixer/vendor: vendor-bin/php-cs-fixer/composer.lock
	composer bin php-cs-fixer install

vendor-bin/php-cs-fixer/composer.lock: vendor-bin/php-cs-fixer/composer.json
	@echo php-cs-fixer composer.lock is not up to date.

vendor-bin/phpstan/vendor: vendor-bin/phpstan/composer.lock
	composer bin phpstan install

vendor-bin/phpstan/composer.lock: vendor-bin/phpstan/composer.json
	@echo phpstan composer.lock is not up to date

vendor-bin/profiling/vendor: vendor-bin/profiling/composer.lock
	composer bin profiling install

vendor-bin/profiling/composer.lock: vendor-bin/profiling/composer.json
	@echo profiling composer.lock is not up to date

vendor-bin/humbug/vendor: vendor-bin/humbug/composer.lock
	composer bin humbug install

vendor-bin/humbug/composer.lock: vendor-bin/humbug/composer.json
	@echo humbug composer.lock is not up to date

vendor-bin/covers-validator/vendor: vendor-bin/covers-validator/composer.lock
	composer bin covers-validator install

vendor-bin/covers-validator/composer.lock: vendor-bin/covers-validator/composer.json
	@echo covers-validator composer.lock is not up to date

dist/clover.xml: vendor/phpunit
	$(MAKE) tc
