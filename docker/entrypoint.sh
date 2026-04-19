#!/bin/bash
set -e

echo "🚀 Iniciando TwitchClips..."

cd /var/www/html

# Verificar .env
if [ ! -f .env ]; then
    echo "⚠️  No existe .env, copiando desde .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "❌ ERROR: No existe .env ni .env.example"
        exit 1
    fi
fi

# Generar APP_KEY si no existe
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=base64:$" .env 2>/dev/null; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force
fi

# Instalar dependencias si no existen
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependencias de npm..."
    npm install
fi

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Crear symlink de storage
if [ ! -L public/storage ]; then
    echo "🔗 Creando symlink de storage..."
    php artisan storage:link 2>/dev/null || true
fi

# Configurar permisos
echo "🔒 Configurando permisos..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
mkdir -p storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

echo "✅ Inicialización completada"
echo "🌐 Aplicación lista en http://localhost:8080"

# Iniciar PHP-FPM en background
php-fpm -D

# Iniciar nginx en foreground
exec nginx -g "daemon off;"
