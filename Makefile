default: lint


lint-php:
	vendor/bin/phplint --configuration=.phplint.yml --ansi

lint-phpcs:
	vendor/bin/phpcs --standard=PSR2 src tests

lint-phpcs-fixer:
	vendor/bin/php-cs-fixer fix --config=.php_cs --dry-run --verbose --ansi

lint:
	make --no-print-directory lint-php && \
	make --no-print-directory lint-phpcs && \
	make --no-print-directory lint-phpcs-fixer


fix-phpcs-fixer:
	vendor/bin/php-cs-fixer fix --config=.php_cs --verbose --ansi

fix:
	make --no-print-directory fix-phpcs-fixer


qa-phpcpd:
	vendor/bin/phpcpd src tests

qa-phpmd:
	vendor/bin/phpmd src,tests ansi unusedcode,naming,design,controversial,codesize

qa-phpmnd:
	vendor/bin/phpmnd --ansi ./

qa-compatibility:
	vendor/bin/phpcs --standard=PHPCompatibility --runtime-set testVersion 8.0- src tests

qa-phpstan:
	vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G --no-progress

qa:
	make --no-print-directory qa-phpcpd && \
	make --no-print-directory qa-phpmd && \
	make --no-print-directory qa-phpmnd && \
	make --no-print-directory qa-compatibility && \
	make --no-print-directory qa-phpstan


test-phpunit:
	vendor/bin/phpunit

test-infection:
	vendor/bin/infection

test:
	make --no-print-directory test-phpunit && \
	make --no-print-directory test-infection


report-phpunit-coverage:
	vendor/bin/phpunit --coverage-html build/coverage

report-phpunit-clover:
	vendor/bin/phpunit --coverage-clover build/logs/clover.xml

report:
	make --no-print-directory report-phpunit-coverage && \
	make --no-print-directory report-phpunit-clover
