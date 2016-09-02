#!/bin/sh -e

service memcached start

cd /code
vendor/bin/phpunit -vvvv
