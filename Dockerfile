# Wir starten mit dem offiziellen PHP 8.3 Apache Image
FROM php:8.3-apache

# 1. System-Abhängigkeiten und GraphicsMagick installieren
RUN apt-get update && apt-get install -y \
    graphicsmagick \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    git \
    unzip \
    mariadb-client \
    locales \
    && rm -rf /var/lib/apt/lists/*

# Locale für UTF-8 konfigurieren
RUN sed -i '/de_DE.UTF-8/s/^# //g' /etc/locale.gen && locale-gen
ENV LANG=de_DE.UTF-8
ENV LC_ALL=de_DE.UTF-8

# 2. PHP Extensions konfigurieren und installieren (GD, Zip, Intl, MySQL, OpCache, Exif)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip intl mysqli pdo_mysql opcache soap exif

# 3. Apache Konfiguration anpassen (DocumentRoot auf /public setzen)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite headers expires

# AllowOverride für .htaccess aktivieren
COPY docker-apache.conf /etc/apache2/conf-available/typo3.conf
RUN a2enconf typo3

# 4. PHP Konfiguration für Production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY <<EOF $PHP_INI_DIR/conf.d/typo3.ini
memory_limit = 256M
max_execution_time = 240
max_input_vars = 1500
upload_max_filesize = 50M
post_max_size = 50M
EOF

# 5. Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Projekt-Dateien kopieren
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .

# 7. Abhängigkeiten installieren (Production Mode)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. TYPO3 var Verzeichnisse erstellen und Rechte setzen
RUN mkdir -p var/cache var/lock var/log var/session var/labels \
    && mkdir -p public/typo3temp public/fileadmin \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/var \
    && chmod -R 775 /var/www/html/public/typo3temp \
    && chmod -R 775 /var/www/html/public/fileadmin

# 9. Entrypoint: Rechte zur Laufzeit setzen (wichtig bei Volume-Mounts)
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Marker für additional.php setzen
ENV IS_DOCKER_ENV=true

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
