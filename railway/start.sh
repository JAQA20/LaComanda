#!/bin/sh
set -e

PORT_TO_USE="${PORT:-8080}"

sed -i "s/^Listen 80$/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf

# Evitar conflictos de MPM en Railway: dejar solo prefork habilitado.
a2dismod mpm_event >/dev/null 2>&1 || true
a2dismod mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

exec apache2-foreground
