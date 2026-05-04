# Obligatorio-PHP
Obligatorio curso PHP

## Ejecutar Laravel localmente

El proyecto Laravel está dentro de la carpeta `restaurante`.

## Build + Run en un solo comando

Desde la raíz del repositorio:

```bash
bash build_and_serve.sh
```

Con puerto personalizado:

```bash
bash build_and_serve.sh 8080
```

Este script ejecuta, en orden:

- `composer install` en `restaurante`
- `npm install` en `restaurante`
- `npm run build` en `restaurante`
- servidor PHP nativo vía `./serve.sh`

Usa este script desde la raíz del repositorio:

```bash
./serve.sh
```

O manualmente desde la carpeta `restaurante`:

```bash
cd restaurante
php artisan serve --host=0.0.0.0 --port=8000
```
