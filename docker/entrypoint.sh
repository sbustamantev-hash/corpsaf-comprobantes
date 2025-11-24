#!/bin/bash

set -e

# Esperar a que la base de datos esté lista
echo "Esperando a que la base de datos esté lista..."
until nc -z db 3306; do
  echo "Esperando conexión a la base de datos..."
  sleep 2
done

echo "Base de datos lista!"

# Generar clave de aplicación si no existe
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    fi
    php artisan key:generate --force || true
fi

# Limpiar cache de Laravel
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force || true

# Optimizar Laravel (solo en producción)
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Iniciar PHP-FPM en segundo plano
php-fpm -D

# Iniciar Nginx en primer plano
echo "Iniciando servidor web..."
exec nginx -g "daemon off;"

