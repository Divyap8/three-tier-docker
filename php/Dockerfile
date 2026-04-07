ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-fpm-alpine

LABEL maintainer="your-email@example.com"
LABEL description="PHP-FPM Application Tier — Three-Tier Docker Architecture"

# ── System dependencies ───────────────────────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && rm -rf /var/cache/apk/*

# ── PHP extensions ────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        mbstring \
        intl \
        opcache

# ── Composer ─────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── PHP runtime configuration ─────────────────────────────────
COPY php.ini /usr/local/etc/php/conf.d/app.ini
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# ── App user (non-root) ───────────────────────────────────────
RUN addgroup -g 1000 -S appgroup \
    && adduser -u 1000 -S appuser -G appgroup

# ── Working directory & permissions ───────────────────────────
WORKDIR /var/www/html
RUN chown -R appuser:appgroup /var/www/html \
    && mkdir -p /var/log/php \
    && chown -R appuser:appgroup /var/log/php

USER appuser

# ── Copy application source ───────────────────────────────────
COPY --chown=appuser:appgroup src/ .

EXPOSE 3000

CMD ["php-fpm"]
