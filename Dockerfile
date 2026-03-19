FROM php:8.2-apache-bookworm

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libzip-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
COPY railway/start.sh /usr/local/bin/railway-start.sh

RUN chown -R www-data:www-data /var/www/html \
    && chmod +x /usr/local/bin/railway-start.sh

EXPOSE 80

CMD ["/usr/local/bin/railway-start.sh"]
