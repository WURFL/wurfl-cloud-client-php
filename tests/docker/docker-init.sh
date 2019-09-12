#!/bin/sh -e

service memcached start
service redis-server start

cd /code
/usr/bin/composer install
vendor/bin/phpunit -vvvv
