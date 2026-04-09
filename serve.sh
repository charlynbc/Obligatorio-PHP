#!/usr/bin/env bash

# Servir la aplicación Laravel desde la carpeta correcta.
cd "$(dirname "$0")/restaurante" || exit 1
php artisan serve --host=0.0.0.0 --port=8000
