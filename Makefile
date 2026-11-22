vendor/autoload.php:
	composer install

sniff: vendor/autoload.php
	composer phpcs

phpstan: vendor/autoload.php
	composer phpstan

test: vendor/autoload.php
	composer test
