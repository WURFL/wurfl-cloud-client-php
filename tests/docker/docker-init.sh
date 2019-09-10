#!/bin/sh -e

service memcached start

cd /code
/usr/local/bin/composer install
vendor/bin/phpunit -vvvv
