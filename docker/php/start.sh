#!/bin/sh
set -e

php artisan optimize:clear

# inicia o worker em background
php artisan queue:work redis --queue=default --sleep=1 --tries=3 --timeout=90 --verbose &

# inicia o servidor HTTP
php artisan serve --host=0.0.0.0 --port=8000