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

# Railway estaba arrancando Apache directo con apache2-foreground, por eso nunca pasaba
# por railway/start.sh y no se desactivaban los MPM sobrantes. Eso causaba:
# "AH00534: apache2: Configuration error: More than one MPM loaded."
#
# Dejamos start.sh como entrypoint único para que:
# - ajuste el puerto según Railway/Docker local
# - desactive mpm_event/mpm_worker
# - deje solo prefork habilitado antes de iniciar Apache
RUN chmod +x /var/www/html/railway/start.sh \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["/var/www/html/railway/start.sh"]
