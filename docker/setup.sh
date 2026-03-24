#!/bin/bash
set -e

echo "🏥 ControClinic - Docker Setup"
echo "=============================="

# Copy .env if not exists
if [ ! -f .env ]; then
    echo "📋 Copiando .env.docker → .env"
    cp .env.docker .env
fi

# Build and start containers
echo "🐳 Construyendo contenedores..."
docker compose up -d --build

# Wait for MySQL
echo "⏳ Esperando MySQL..."
docker compose exec app bash -c 'while ! mysqladmin ping -h mysql --silent 2>/dev/null; do sleep 1; done'

# Install dependencies
echo "📦 Instalando dependencias PHP..."
docker compose exec app composer install

echo "📦 Instalando dependencias Node..."
docker compose exec app npm install

# Generate key
echo "🔑 Generando APP_KEY..."
docker compose exec app php artisan key:generate

# Run migrations and seeders
echo "🗄️ Ejecutando migraciones..."
docker compose exec app php artisan migrate --seed

# Storage link
echo "🔗 Creando storage link..."
docker compose exec app php artisan storage:link

# Build assets
echo "🎨 Compilando assets..."
docker compose exec app npm run build

# Cache
echo "⚡ Optimizando..."
docker compose exec app php artisan optimize:clear

echo ""
echo "✅ ControClinic listo!"
echo "🌐 http://localhost:8080"
echo "📧 doctor@controclinic.com / password"
echo ""
echo "Para Vite dev server: docker compose --profile dev up node"
