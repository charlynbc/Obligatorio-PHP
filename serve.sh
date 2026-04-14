#!/usr/bin/env bash

# Levanta el servidor PHP nativo apuntando al Front Controller MVC.
# Accede en: http://localhost:8000

PORT=${1:-8000}

cd "$(dirname "$0")/restaurante" || exit 1

echo "Servidor iniciado en http://localhost:${PORT}"
echo "Presiona Ctrl+C para detenerlo."
echo ""

php -S 0.0.0.0:"${PORT}" -t public public/index.php
