# See https://tech.davis-hansson.com/p/make/
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

COVERAGE_DIR = dist/coverage
COVERAGE_DIR_XML = $(COVERAGE_DIR)/xml
COVERAGE_DIR_HTML = $(COVERAGE_DIR)/html
CLOVER_COVERAGE = $(COVERAGE_DIR)/clover.xml

PHP_NO_GC = php -d zend.enable_gc=0
PHP_DBG=phpdbg -qrr -d zend.enable_gc=0 bin/phpunit

COVERS_VALIDATOR_BIN = vendor-bin/covers-validator/bin/covers-validator
COVERS_VALIDATOR = $(PHP_NO_GC) $(COVERS_VALIDATOR_BIN)
INFECTION_BIN = vendor-bin/infection/bin/infection
INFECTION = $(INFECTION_BIN) --test-framework-options="--exclude-group=integration"
PHP_CS_FIXER_BIN = vendor-bin/php-cs-fixer/bin/php-cs-fixer
PHP_CS_FIXER = $(PHP_NO_GC) $(PHP_CS_FIXER_BIN)
PHPSTAN_BIN = vendor-bin/phpstan/bin/phpstan
PHPSTAN = $(PHP_NO_GC) -dmemory_limit=1G $(PHPSTAN_BIN)
PHPUNIT_BIN = bin/phpunit
PHPUNIT = $(PHP_NO_GC) $(PHPUNIT_BIN)
PHPUNIT_SYMFONY_BIN = vendor-bin/symfony/bin/phpunit
PHPUNIT_SYMFONY = $(PHP_NO_GC) $(PHPUNIT_SYMFONY_BIN)


.DEFAULT_GOAL := help


.PHONY: help
.PHONY: help
help:
	@echo "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'


.PHONY: all
all:		  ## Runs everything
all: cs phpstan test


#
# Code Style
#---------------------------------------------------------------------------

.PHONY: cs
cs: 	 	  ## Fixes CS
cs: php_cs_fixer gitignore_sort

.PHONY: php_cs_fixer
php_cs_fixer: 	  ## Runs PHP-CS-Fixer
php_cs_fixer: $(PHP_CS_FIXER_BIN)
	$(PHP_CS_FIXER) fix

.PHONY: gitignore_sort
gitignore_sort:	  ## Sorts the .gitignore entries
gitignore_sort:
	LC_ALL=C sort -u .gitignore -o .gitignore


#
# Code Analysis
#---------------------------------------------------------------------------

.PHONY: phpstan
phpstan: 	  ## Runs PHPStan
phpstan: $(PHPSTAN_BIN)
	$(PHPSTAN) analyze


#
# Commands
#---------------------------------------------------------------------------

.PHONY: test
test:             ## Runs all the tests
test: test_core test_symfony

.PHONY: test_core
test_core:        ## Runs all the tests for the core library
test_core: validate-package covers_validate phpunit

.PHONY: validate-package
validate-package: ## Validates the Composer package
validate-package: vendor
	composer validate --strict

.PHONY: covers_validate
covers_validate:  ## Runs CoverValidator for the library core
covers_validate: $(COVERS_VALIDATOR_BIN)
#	$(COVERS_VALIDATOR)

.PHONY: phpunit
phpunit:          ## Runs PHPUnit fot the library core
phpunit: $(PHPUNIT_BIN)
	$(PHPUNIT)

.PHONY: test_symfony
test_symfony:     ## Runs all the tests for the Symfony bridge
test_symfony: covers_validate_symfony phpunit_symfony

.PHONY: covers_validate_symfony
covers_validate_symfony: ## Runs CoverValidator for the Symfony bridge
covers_validate_symfony: $(COVERS_VALIDATOR_BIN)
#	$(COVERS_VALIDATOR) -c phpunit_symfony.xml.dist

.PHONY: phpunit_symfony
phpunit_symfony:  ## Runs the tests for the Symfony Bridge
phpunit_symfony: $(PHPUNIT_SYMFONY_BIN)
	$(PHPUNIT_SYMFONY) --configuration=phpunit_symfony.xml.dist

.PHONY: phpunit_coverage
phpunit_coverage: ## Runs PHPUnit with coverage
phpunit_coverage: $(PHPUNIT_BIN)
	XDEBUG_MODE=coverage $(PHP_NO_GC) $(PHPUNIT) --exclude-group=integration --coverage-text --coverage-html=$(COVERAGE_DIR_HTML) --coverage-clover=$(CLOVER_COVERAGE)

.PHONY: infection
infection: 	  ## Runs Infection
infection: $(INFECTION_BIN)
	$(INFECTION)

.PHONY: blackfire
blackfire: 	  ## Runs Blackfire performance tests
blackfire: vendor vendor-bin/profiling/vendor
	php profiling/scenario0/blackfire.php
	php profiling/scenario1/blackfire.php
	php profiling/scenario2/blackfire.php
	php profiling/scenario3/blackfire.php


#
# Rules from files
#---------------------------------------------------------------------------

composer.lock: composer.json
	@echo compose.lock is not up to date.

vendor: composer.lock
	composer install
	touch -c $@

$(PHPUNIT_BIN): vendor
	composer install
	touch -c $@

$(PHPUNIT_SYMFONY_BIN): vendor-bin/symfony/composer.lock
	composer bin symfony install
	touch -c $@

vendor-bin/symfony/composer.lock: vendor-bin/symfony/composer.json
	@echo symfony compose.lock is not up to date.

$(PHP_CS_FIXER_BIN): vendor-bin/php-cs-fixer/composer.lock
	composer bin php-cs-fixer install
	touch -c $@

vendor-bin/php-cs-fixer/composer.lock: vendor-bin/php-cs-fixer/composer.json
	@echo php-cs-fixer composer.lock is not up to date.

$(PHPSTAN_BIN): vendor-bin/phpstan/composer.lock
	composer bin phpstan install
	touch -c $@

vendor-bin/phpstan/composer.lock: vendor-bin/phpstan/composer.json
	@echo phpstan composer.lock is not up to date

vendor-bin/profiling/vendor: vendor-bin/profiling/composer.lock
	composer bin profiling install

vendor-bin/profiling/composer.lock: vendor-bin/profiling/composer.json
	@echo profiling composer.lock is not up to date

$(INFECTION_BIN): vendor-bin/infection/composer.lock
	composer bin infection install
	touch -c $@

vendor-bin/infection/composer.lock: vendor-bin/infection/composer.json
	@echo infection composer.lock is not up to date

$(COVERS_VALIDATOR_BIN): vendor-bin/covers-validator/composer.lock
	composer bin covers-validator install
	touch -c $@

vendor-bin/covers-validator/composer.lock: vendor-bin/covers-validator/composer.json
	@echo covers-validator composer.lock is not up to date

$(CLOVER_COVERAGE):
	$(MAKE) phpunit_coverage
	touch -c $@
