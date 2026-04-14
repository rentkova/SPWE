FROM php:8.2-apache

# PHP rozšíření
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache moduly
RUN a2enmod rewrite ssl headers vhost_alias

# OpenSSL
RUN apt-get update && apt-get install -y openssl ssl-cert && rm -rf /var/lib/apt/lists/*

# Self-signed wildcard certifikát
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/hosting.key \
    -out /etc/ssl/certs/hosting.crt \
    -subj "/C=CZ/O=Hosting/CN=localhost" \
    -addext "subjectAltName=DNS:localhost,DNS:app.local,DNS:*.local"

# Zakázat výchozí stránky
RUN a2dissite 000-default.conf || true
RUN a2dissite default-ssl.conf || true

# Sjednotit UID www-data
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Adresář pro weby zákazníků
RUN mkdir -p /var/www/html/public/uploads && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
