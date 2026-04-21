#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# deploy.sh — Despliegue INICIAL en VPS OVH
# Uso:  bash deploy.sh <dominio> <email>
# Ej.:  bash deploy.sh contentio.es admin@contentio.es
#
# Prerrequisitos en el servidor:
#   - Docker y Docker Compose instalados
#   - DNS del dominio apuntando a la IP del VPS (A record)
#   - Puerto 80 y 443 abiertos en el firewall
#   - backend/.env creado a partir de backend/.env.production.example
# ─────────────────────────────────────────────────────────────────────────────
set -e

DOMAIN="${1:?Uso: bash deploy.sh <dominio> <email>}"
EMAIL="${2:?Uso: bash deploy.sh <dominio> <email>}"
COMPOSE="docker compose -f docker-compose.prod.yml"
DIR="$(cd "$(dirname "$0")" && pwd)"

echo ""
echo "▶ Desplegando Contentio en https://$DOMAIN"
echo ""

# ── Verificar .env ────────────────────────────────────────────────────────────
if [ ! -f "$DIR/backend/.env" ]; then
    echo "✗ ERROR: backend/.env no existe."
    echo "  Crea el archivo a partir de backend/.env.production.example:"
    echo "  cp backend/.env.production.example backend/.env"
    echo "  nano backend/.env   # ajusta APP_KEY, DB_PASSWORD, MAIL_*, etc."
    exit 1
fi

# ── Sustituir YOUR_DOMAIN en prod.conf ────────────────────────────────────────
echo "▶ Configurando Nginx con dominio $DOMAIN..."
sed "s/YOUR_DOMAIN/$DOMAIN/g" "$DIR/docker/nginx/prod.conf" > "$DIR/docker/nginx/prod.conf.tmp"

# ── Fase 1: arrancar con config HTTP-only para obtener el certificado ─────────
echo "▶ Fase 1 — Arrancando Nginx en modo HTTP para verificacion ACME..."
cp "$DIR/docker/nginx/init.conf" "$DIR/docker/nginx/current.conf"

# Montar current.conf en Nginx durante el arranque inicial
$COMPOSE up -d nginx postgres php

echo "▶ Esperando a que Nginx este listo..."
sleep 5

# ── Fase 2: obtener certificado SSL ───────────────────────────────────────────
echo "▶ Fase 2 — Obteniendo certificado SSL (Let's Encrypt)..."
$COMPOSE run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    -d "$DOMAIN" \
    -d "www.$DOMAIN"

# ── Fase 3: activar config HTTPS y recargar Nginx ─────────────────────────────
echo "▶ Fase 3 — Activando HTTPS..."
cp "$DIR/docker/nginx/prod.conf.tmp" "$DIR/docker/nginx/current.conf"
rm "$DIR/docker/nginx/prod.conf.tmp"
$COMPOSE exec nginx nginx -s reload

# ── Fase 4: build del frontend ────────────────────────────────────────────────
echo "▶ Fase 4 — Construyendo frontend Vue..."
$COMPOSE run --rm frontend-build
$COMPOSE exec nginx nginx -s reload   # recargar para servir los archivos nuevos

# ── Fase 5: base de datos ─────────────────────────────────────────────────────
echo "▶ Fase 5 — Ejecutando migraciones..."
$COMPOSE exec php php artisan migrate --force

echo "▶ Creando enlace de storage..."
$COMPOSE exec php php artisan storage:link

# ── Fase 6: optimizar Laravel ────────────────────────────────────────────────
echo "▶ Fase 6 — Optimizando Laravel para produccion..."
$COMPOSE exec php php artisan config:cache
$COMPOSE exec php php artisan route:cache
$COMPOSE exec php php artisan event:cache
$COMPOSE exec php php artisan view:cache

# ── Fase 7: renovacion automatica SSL ────────────────────────────────────────
echo "▶ Fase 7 — Configurando renovacion automatica de SSL..."
CRON_CMD="0 3 * * * cd $DIR && docker compose -f docker-compose.prod.yml run --rm certbot renew --quiet && docker compose -f docker-compose.prod.yml exec nginx nginx -s reload"
( crontab -l 2>/dev/null | grep -v "certbot renew"; echo "$CRON_CMD" ) | crontab -

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✓ Contentio desplegado en https://$DOMAIN"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Pasos siguientes:"
echo "  1. Crear usuario admin inicial:"
echo "     $COMPOSE exec php php artisan tinker"
echo "     >>> App\Models\User::create(['name'=>'Admin','email'=>'admin@$DOMAIN','password'=>bcrypt('CAMBIA_ESTO'),'role'=>'admin']);"
echo ""
echo "  2. Configurar backups automaticos:"
echo "     bash backup.sh   # prueba manual"
echo "     (el cron de backup se configura en backup.sh)"
echo ""
echo "  3. Para actualizaciones futuras:"
echo "     bash update.sh"
echo ""
