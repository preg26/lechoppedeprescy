FROM php:8.3-fpm

# =========================
# Dépendances système
# =========================
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        intl \
        zip \
        opcache \
        gd \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# =========================
# Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# Application
# =========================
WORKDIR /var/www/app

ENV APP_ENV=prod
ENV APP_DEBUG=0

# =========================
# Code Symfony
# =========================
COPY . /var/www/app/

# =========================
# Dépendances PHP
# =========================
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction

# =========================
# Permissions Symfony
# =========================
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

# =========================
# Nginx
# =========================
RUN rm -f /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/conf.d/default.conf

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# =========================
# Build marker
# =========================
ARG BUILD_ID
RUN echo "BUILD_ID=${BUILD_ID}" > /build-id.txt

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]