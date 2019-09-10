#!/bin/sh -e

service memcached start

cd /code
/usr/bin/composer install
vendor/bin/phpunit -vvvv
