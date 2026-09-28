#!/bin/sh
set -eu

PORT="${PORT:-10000}"

# Render fornece a porta HTTP pela variável PORT. Altere somente o listener HTTP padrão.
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
