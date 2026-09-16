# ==============================================================================
# STAGE 1: Frontend Build (Node.js LTS - Debian Bookworm)
# ==============================================================================
FROM node:22-bookworm-slim AS frontend-builder
WORKDIR /app

# Salin definisi dependency package.json & lockfile untuk memanfaatkan layer caching
COPY package*.json ./
RUN npm ci

# Salin file konfigurasi Vite, Tailwind, dan asset views/resources
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Build asset bundle produksi (hasil di public/build)
RUN npm run build


# ==============================================================================
# STAGE 2: Vendor / Composer Builder (PHP 8.4 CLI)
# ==============================================================================
FROM php:8.4-cli-bookworm AS vendor-builder
WORKDIR /app

# Ambil binary resmi Composer dari composer:latest
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install paket sistem minimum yang dibutuhkan saat composer install
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Salin file deklarasi dependensi PHP
COPY composer.json composer.lock ./

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install dependency tanpa dev packages, script eksekusi, atau autoloader lengkap dulu (caching layer)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Salin source code aplikasi untuk generate optimized autoloader
COPY . .

# Generate production autoloader classmap
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative


# ==============================================================================
# STAGE 3: Final Production Image (PHP 8.4 FPM + Nginx + Supervisord)
# ==============================================================================
FROM php:8.4-fpm-bookworm AS runner

LABEL maintainer="DevOps Team" \
      description="Production image for Laravel 13 + Laravel Reverb with PHP 8.4"

# Set environment
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    NODE_ENV=production

# Install dependensi sistem runtime, ekstensi compiler, Nginx, & Supervisor
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    sqlite3 \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi dan install ekstensi PHP yang dibutuhkan Laravel 13 & Reverb:
# pdo_mysql, mbstring, exif, pcntl, bcmath, gd, sockets, opcache
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        sockets \
        opcache

# Salin binary Composer ke container final jika diperlukan untuk task runtime (seperti artisan / package check)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup direktori kerja
WORKDIR /var/www

# 1. Salin source code aplikasi
COPY . /var/www

# 2. Salin vendor PHP dari vendor-builder (Stage 2)
COPY --from=vendor-builder /app/vendor /var/www/vendor

# 3. Salin hasil build Vite assets dari frontend-builder (Stage 1)
COPY --from=frontend-builder /app/public/build /var/www/public/build

# 4. Salin konfigurasi Nginx & Supervisor & Start script
COPY nginx.conf /etc/nginx/sites-available/default
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# 5. Konfigurasi Opcache untuk PHP 8.4 di lingkungan produksi
RUN echo "opcache.enable=1\n\
opcache.enable_cli=0\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=20000\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=1" > /usr/local/etc/php/conf.d/opcache-recommended.ini

# 6. Setup database SQLite default & hak akses permission storage
RUN mkdir -p /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/storage/framework/cache \
             /var/www/storage/logs \
             /var/www/bootstrap/cache \
    && touch /var/www/database/database.sqlite \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database

# Expose port (80 untuk Nginx / Web & Reverb Proxy)
EXPOSE 80

# Jalankan entrypoint / startup script
CMD ["/usr/local/bin/start.sh"]
