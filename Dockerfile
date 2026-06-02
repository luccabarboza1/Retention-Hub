FROM php:8.1-fpm-alpine

# Dependências do sistema
RUN apk add --no-cache \
    nginx \
    curl \
    unzip \
    gettext \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurações de PHP e PHP-FPM
COPY docker/php/php.ini   $PHP_INI_DIR/conf.d/custom.ini
COPY docker/php/www.conf  /usr/local/etc/php-fpm.d/www.conf

# Configuração do Nginx (template — PORT substituída em runtime pelo start.sh)
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf.template

# Script de inicialização
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

WORKDIR /var/www/html

# Copia código e instala dependências sem dev
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 80

# Container 1 (app): este CMD roda nginx + php-fpm via script
# Container 2 (worker): sobrescrever no painel da Umbler com:
#   php artisan queue:work --queue=webhooks,default --tries=1 --timeout=30 --max-jobs=500
CMD ["/start.sh"]
