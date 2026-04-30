#!/bin/bash
# =============================================================================
# ControClinic — Script de Deploy para Producción
# =============================================================================
# Uso: ./docker/deploy.sh
# Requisitos: .env configurado, Docker corriendo, git pull ya hecho
# =============================================================================
set -e

APP_CONTAINER="${APP_CONTAINER:-controclinic-app}"

echo "🚀 ControClinic — Deploy de Producción"
echo "======================================="

# ── 1. Verificar que el contenedor esté corriendo ────────────────────────────
if ! docker ps --format '{{.Names}}' | grep -q "^${APP_CONTAINER}$"; then
    echo "❌ Contenedor '${APP_CONTAINER}' no está corriendo."
    echo "   Ejecuta primero: docker compose up -d"
    exit 1
fi

# ── 2. Activar modo mantenimiento ────────────────────────────────────────────
echo "🔧 Activando modo mantenimiento..."
docker exec "$APP_CONTAINER" php artisan down --retry=60

# ── 3. Instalar dependencias PHP (sin dev) ───────────────────────────────────
echo "📦 Instalando dependencias PHP (producción)..."
docker exec "$APP_CONTAINER" composer install --no-dev --optimize-autoloader --no-interaction

# ── 4. Ejecutar migraciones ───────────────────────────────────────────────────
echo "🗄️  Ejecutando migraciones..."
docker exec "$APP_CONTAINER" php artisan migrate --force

# ── 5. Compilar assets ────────────────────────────────────────────────────────
echo "🎨 Compilando assets..."
docker exec "$APP_CONTAINER" npm ci --silent
docker exec "$APP_CONTAINER" npm run build

# ── 6. Optimizar Laravel ──────────────────────────────────────────────────────
echo "⚡ Optimizando Laravel..."
docker exec "$APP_CONTAINER" php artisan optimize:clear
docker exec "$APP_CONTAINER" php artisan optimize
docker exec "$APP_CONTAINER" php artisan storage:link --quiet 2>/dev/null || true

# ── 7. Reiniciar workers de cola ──────────────────────────────────────────────
echo "🔄 Reiniciando queue workers..."
docker exec "$APP_CONTAINER" php artisan queue:restart

# ── 8. Verificar health check ─────────────────────────────────────────────────
echo "🏥 Verificando health check..."
APP_URL=$(docker exec "$APP_CONTAINER" php artisan tinker --execute="echo config('app.url');" 2>/dev/null | tail -1)
HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/health" 2>/dev/null || echo "000")

if [ "$HEALTH_STATUS" = "200" ]; then
    echo "   ✅ Health check: OK"
else
    echo "   ⚠️  Health check devolvió HTTP ${HEALTH_STATUS} — revisar logs"
fi

# ── 9. Desactivar modo mantenimiento ─────────────────────────────────────────
echo "✅ Desactivando modo mantenimiento..."
docker exec "$APP_CONTAINER" php artisan up

echo ""
echo "🎉 Deploy completado exitosamente."
echo "   Verificar: ${APP_URL}"
