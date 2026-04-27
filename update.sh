#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# update.sh — Actualizar Contentio en produccion tras un git push
# Uso: bash update.sh
# ─────────────────────────────────────────────────────────────────────────────
set -e

COMPOSE="docker compose -f docker-compose.prod.yml"
DIR="$(cd "$(dirname "$0")" && pwd)"

echo ""
echo "▶ Actualizando Contentio..."
echo ""

# 1. Obtener cambios del repositorio
echo "▶ Descargando cambios (git pull)..."
git -C "$DIR" pull origin main

# 2. Reconstruir imagen PHP si el Dockerfile cambio
echo "▶ Reconstruyendo imagen PHP..."
$COMPOSE build php

# 3. Reconstruir imagen frontend y ejecutarla
echo "▶ Reconstruyendo frontend..."
$COMPOSE build frontend-build
$COMPOSE run --rm frontend-build

# 4. Instalar dependencias PHP (por si hay paquetes nuevos en composer.json)
echo "▶ Instalando dependencias PHP..."
$COMPOSE run --rm --no-deps php composer install --no-dev --optimize-autoloader --no-interaction

# 5. Reiniciar PHP-FPM con la nueva imagen
echo "▶ Reiniciando PHP-FPM..."
$COMPOSE up -d --no-deps php

# 6. Ejecutar migraciones pendientes
echo "▶ Ejecutando migraciones..."
$COMPOSE exec php php artisan migrate --force

# 7. Limpiar y regenerar caches de Laravel
echo "▶ Regenerando caches..."
$COMPOSE exec php php artisan config:cache
$COMPOSE exec php php artisan route:cache
$COMPOSE exec php php artisan event:cache
$COMPOSE exec php php artisan view:cache

# 8. Recargar Nginx para servir el nuevo frontend
echo "▶ Recargando Nginx..."
$COMPOSE exec nginx nginx -s reload

echo ""
echo "✓ Actualizacion completada."
echo ""
