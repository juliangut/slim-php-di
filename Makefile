default: lint


.PHONY: lint-php
lint-php:
	vendor/bin/phplint --configuration=.phplint.yml --ansi

.PHONY: lint-phpcs
lint-phpcs:
	vendor/bin/phpcs --standard=PSR12 src tests

.PHONY: lint-phpcs-fixer
lint-phpcs-fixer:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --dry-run --verbose --ansi

.PHONY: lint
lint:
	make --no-print-directory lint-php && \
	make --no-print-directory lint-phpcs && \
	make --no-print-directory lint-phpcs-fixer


.PHONY: fix-phpcs-fixer
fix-phpcs-fixer:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --verbose --ansi

.PHONY: fix
fix:
	make --no-print-directory fix-phpcs-fixer


.PHONY: qa-phpcpd
qa-phpcpd:
	vendor/bin/phpcpd src tests

.PHONY: qa-phpmd
qa-phpmd:
	vendor/bin/phpmd src,tests ansi unusedcode,naming,design,controversial,codesize

.PHONY: qa-phpmnd
qa-phpmnd:
	vendor/bin/phpmnd --ansi ./

.PHONY: qa-compatibility
qa-compatibility:
	vendor/bin/phpcs --standard=PHPCompatibility --runtime-set testVersion 8.1- src tests

.PHONY: qa-phpstan
qa-phpstan:
	vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G --no-progress --error-format compact

.PHONY: qa
qa:
	make --no-print-directory qa-phpcpd && \
	make --no-print-directory qa-phpmd && \
	make --no-print-directory qa-phpmnd && \
	make --no-print-directory qa-compatibility && \
	make --no-print-directory qa-phpstan


.PHONY: test-phpunit
test-phpunit:
	vendor/bin/phpunit

.PHONY: test-infection
test-infection:
	vendor/bin/infection

.PHONY: test
test:
	make --no-print-directory test-phpunit && \
	make --no-print-directory test-infection


.PHONY: report-phpunit-coverage
report-phpunit-coverage:
	vendor/bin/phpunit --coverage-html build/coverage

.PHONY: report-phpunit-clover
report-phpunit-clover:
	vendor/bin/phpunit --coverage-clover build/logs/clover.xml

.PHONY: report
report:
	make --no-print-directory report-phpunit-coverage && \
	make --no-print-directory report-phpunit-clover
