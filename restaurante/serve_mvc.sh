#!/usr/bin/env bash

# Ejecuta el servidor PHP nativo apuntando al Front Controller MVC.
# El Front Controller real es restaurante/public/index.php
# Accede en: http://localhost:8080

PORT=${1:-8080}

cd "$(dirname "$0")" || exit 1

echo "Iniciando servidor PHP en http://localhost:${PORT}"
echo "Presiona Ctrl+C para detenerlo."
echo ""

php -S 0.0.0.0:"${PORT}" -t public public/index.php
