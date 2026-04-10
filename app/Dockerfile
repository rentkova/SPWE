FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite ssl

RUN apt-get update && apt-get install -y apache2-utils ssl-cert && rm -rf /var/lib/apt/lists/*

# Generování self-signed certifikátu pro HTTPS
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/hosting.key \
    -out /etc/ssl/certs/hosting.crt \
    -subj "/C=CZ/ST=Pardubice/L=Pardubice/O=HostingProjekt/CN=app.local" \
    -addext "subjectAltName = DNS:app.local, DNS:web1.local, DNS:web2.local, DNS:web3.local, DNS:web4.local"

RUN a2dissite 000-default.conf || true
RUN a2dissite default-ssl.conf || true

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
