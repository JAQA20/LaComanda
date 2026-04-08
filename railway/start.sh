#!/bin/sh
set -e

# ========JARVIS UPDATE========
# Este script ahora soporta ambos entornos:
# 1) Railway: si la plataforma inyecta la variable PORT, Apache usará ese puerto.
# 2) Docker local: si PORT no existe, Apache usará 80 dentro del contenedor.
#    Esto evita el choque que teníamos con docker-compose, donde el mapeo es 8080:80.
PORT_TO_USE="${PORT:-80}"

sed -i "s/^Listen 80$/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf

# Evitar conflictos de MPM en Railway: dejar solo prefork habilitado.
a2dismod mpm_event >/dev/null 2>&1 || true
a2dismod mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

exec apache2-foreground
