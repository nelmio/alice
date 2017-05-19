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
tu: vendor
	$(COVERS_VALIDATOR)
	$(PHPUNIT)

ts:             ## Run the tests for the Symfony Bridge
ts: vendor
	$(COVERS_VALIDATOR) -c phpunit_symfony.xml.dist
	$(PHPUNIT_SYMFONY) -c phpunit_symfony.xml.dist

tc:             ## Run the tests with coverage
tc: dist/coverage
	$(PHPDBG) --exclude-group=integration --coverage-text --coverage-html=dist/coverage

tm:             ## Run the tests for mutation testing
tm: vendor
	$(HUMBUG)

tp:             ## Run Blackfire performance tests
tp: vendor
	php profiling/scenario0/blackfire.php
	php profiling/scenario1/blackfire.php
	php profiling/scenario2/blackfire.php
	php profiling/scenario3/blackfire.php


##
## Code Analysis
##
##---------------------------------------------------------------------------

phpstan:        ## Run PHPStan analysis
phpstan: vendor
	$(PHPSTAN) analyze -c phpstan.neon -l4 src fixtures tests


##
## Code Style
##---------------------------------------------------------------------------

cs:             ## Run the CS Fixer
cs:	.php_cs.cache
	$(PHP_CS_FIXER) fix


##
## Rules from files
##---------------------------------------------------------------------------

vendor: composer.lock
	composer install

composer.lock: composer.json
	@echo compose.lock is not up to date.

vendor-bin/symfony/vendor: vendor-bin/symfony/composer.lock
	composer install

vendor-bin/symfony/composer.lock: vendor-bin/symfony/composer.json
	@echo compose.lock is not up to date.
