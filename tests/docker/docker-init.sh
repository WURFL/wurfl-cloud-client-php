#!/bin/sh -e

service memcached start
service redis-server start

cd /code
vendor/bin/phpunit -vvvv
