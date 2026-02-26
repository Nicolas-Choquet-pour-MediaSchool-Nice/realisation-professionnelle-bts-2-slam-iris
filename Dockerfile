# Étape de construction (Build stage)
FROM php:8.4-fpm-alpine AS app_php

# Installation des dépendances système
RUN apk add --no-cache \
    acl \
    fcgi \
    file \
    gettext \
    git \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    zip \
    && docker-php-ext-install \
    intl \
    pdo_pgsql \
    zip \
    opcache

# Configuration du script de healthcheck
RUN set -xe; \
    echo '#!/bin/sh' > /usr/local/bin/fcgi-php-status; \
    echo 'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000' >> /usr/local/bin/fcgi-php-status; \
    chmod +x /usr/local/bin/fcgi-php-status

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie des fichiers de configuration composer
COPY composer.json composer.lock ./

# Copie du reste des fichiers du projet
COPY . .

# Finalisation de l'installation (autoloader et scripts)
RUN composer update --prefer-dist --no-progress --no-interaction
ARG APP_ENV=prod
ARG APP_SECRET=ChangeMe
ARG DATABASE_URL="postgresql://app:app@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
RUN composer run post-install-cmd; \
    sync

# Configuration des permissions
RUN chown -R www-data:www-data var

# Configuration de PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# Activer le ping dans PHP-FPM pour le healthcheck
RUN set -xe; \
    { \
        echo '[www]'; \
        echo 'pm.status_path = /status'; \
        echo 'ping.path = /ping'; \
    } | tee /usr/local/etc/php-fpm.d/zz-healthcheck.conf

# Configuration du point d'entrée
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
