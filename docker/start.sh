#!/bin/sh
set -e

# Railway injeta PORT; fallback para 80 em outros ambientes
export PORT="${PORT:-80}"

echo "[start] PORT=${PORT} APP_ENV=${APP_ENV}"

# Gera nginx.conf com a porta correta
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template \
                   > /etc/nginx/http.d/default.conf

# Limpa caches antigos (importante entre deploys)
php artisan config:clear
php artisan cache:clear

# Migrations (idempotente — pula o que já rodou)
echo "[start] Rodando migrations..."
php artisan migrate --force

# Caches de produção (agora que APP_KEY está disponível)
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[start] Iniciando php-fpm + nginx..."

# PHP-FPM em background
php-fpm -D

# Nginx em foreground (mantém o container vivo)
exec nginx -g "daemon off;"
