FROM php:8.2-apache-bookworm

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libzip-dev \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite \
    # --- SOLUCIÓN AL ERROR MPM ---
    # Borramos cualquier carga previa de MPMs para evitar el "More than one loaded"
    && rm -f /etc/apache2/mods-enabled/mpm_* \
    # Activamos explícitamente el prefork (necesario para el módulo de PHP)
    && a2enmod mpm_prefork \
    # -----------------------------
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

# Ajuste dinámico de puerto para Railway (Muy importante para que no dé 502)
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]