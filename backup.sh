#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# backup.sh — Backup de base de datos y storage de Contentio
# Uso:  bash backup.sh
# Cron: 0 2 * * * cd /ruta/proyecto && bash backup.sh >> /var/log/contentio-backup.log 2>&1
#
# Rotacion automatica:
#   - Backups diarios: se conservan 7 dias
#   - Backups del domingo: se conservan 4 semanas
# ─────────────────────────────────────────────────────────────────────────────
set -e

DIR="$(cd "$(dirname "$0")" && pwd)"
COMPOSE="docker compose -f docker-compose.prod.yml"
BACKUP_DIR="$DIR/backups"
DATE=$(date +%Y-%m-%d_%H-%M)
DAY_OF_WEEK=$(date +%u)   # 1=lunes, 7=domingo

mkdir -p "$BACKUP_DIR/db"
mkdir -p "$BACKUP_DIR/storage"

echo "[$(date)] Iniciando backup..."

# ── Backup de base de datos (pg_dump) ────────────────────────────────────────
DB_FILE="$BACKUP_DIR/db/contentio_$DATE.sql.gz"
echo "[$(date)] Volcando base de datos → $DB_FILE"

$COMPOSE exec -T postgres pg_dump \
    -U contentio \
    -d contentio \
    --no-owner \
    --no-acl \
    | gzip > "$DB_FILE"

# ── Backup de storage (archivos subidos) ─────────────────────────────────────
STORAGE_FILE="$BACKUP_DIR/storage/storage_$DATE.tar.gz"
echo "[$(date)] Comprimiendo storage → $STORAGE_FILE"

tar -czf "$STORAGE_FILE" \
    -C "$DIR/backend/storage/app" \
    public 2>/dev/null || echo "[$(date)] AVISO: storage/app/public vacio o no existe"

# ── Rotacion: eliminar backups diarios de mas de 7 dias ───────────────────────
echo "[$(date)] Rotando backups diarios (>7 dias)..."
find "$BACKUP_DIR/db" -name "*.sql.gz" -mtime +7 \
    ! -name "*sunday*" \
    -delete

find "$BACKUP_DIR/storage" -name "*.tar.gz" -mtime +7 \
    ! -name "*sunday*" \
    -delete

# ── Marcar backups del domingo para conservarlos 4 semanas ───────────────────
if [ "$DAY_OF_WEEK" -eq 7 ]; then
    WEEKLY_DB="$BACKUP_DIR/db/contentio_weekly_$DATE.sql.gz"
    cp "$DB_FILE" "$WEEKLY_DB"
    echo "[$(date)] Backup semanal guardado → $WEEKLY_DB"

    # Eliminar semanales de mas de 28 dias
    find "$BACKUP_DIR/db" -name "contentio_weekly_*.sql.gz" -mtime +28 -delete
    find "$BACKUP_DIR/storage" -name "storage_weekly_*.tar.gz" -mtime +28 -delete
fi

echo "[$(date)] Backup completado."
echo "  DB:      $DB_FILE ($(du -sh "$DB_FILE" | cut -f1))"
echo "  Storage: $STORAGE_FILE"
echo ""
