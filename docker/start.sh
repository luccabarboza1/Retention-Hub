#!/bin/sh
set -e

export PORT="${PORT:-80}"

echo "[start] PORT=${PORT} APP_ENV=${APP_ENV}"

# Gera nginx.conf com a porta correta
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template \
                   > /etc/nginx/http.d/default.conf

# Caches de produção (migrations já rodaram no preDeployCommand)
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[start] Iniciando php-fpm + nginx..."

php-fpm -D
exec nginx -g "daemon off;"
