# FROM php:8.2-apache

# RUN apt-get update && apt-get install -y \
#     default-mysql-client \
#     libzip-dev \
#     unzip \
#     && docker-php-ext-install mysqli pdo pdo_mysql \
#     && a2enmod rewrite \
#     && rm -rf /var/lib/apt/lists/*

# COPY . /var/www/html/

# RUN chown -R www-data:www-data /var/www/html

# COPY railway/start.sh /start.sh
# RUN chmod +x /start.sh

# EXPOSE 8080

# CMD ["/start.sh"]

FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libzip-dev \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

CMD ["apache2-foreground"]