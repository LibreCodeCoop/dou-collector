#!/bin/bash
if [ ! -d "vendor" ]; then
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install
fi
php-fpm