#!/usr/bin/env bash

set -euo pipefail

PORT=${1:-8000}
ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
APP_DIR="${ROOT_DIR}/restaurante"

if ! command -v composer >/dev/null 2>&1; then
    echo "Error: composer no esta instalado o no esta en PATH."
    exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
    echo "Error: npm no esta instalado o no esta en PATH."
    exit 1
fi

echo "==> Instalando dependencias PHP (composer install)..."
cd "${APP_DIR}"
composer install

echo "==> Instalando dependencias frontend (npm install)..."
npm install

echo "==> Compilando assets (npm run build)..."
npm run build

echo "==> Iniciando servidor en http://localhost:${PORT}"
cd "${ROOT_DIR}"
./serve.sh "${PORT}"
