#!/bin/sh
set -e

# El código vive en un volumen montado desde el host. var/ (cache, logs y la
# base SQLite) lo tiene que escribir Apache (www-data), así que le damos la
# propiedad en cada arranque. Esto hace que "clone & run" funcione sin pasos
# manuales de permisos.
mkdir -p var
chown -R www-data:www-data var

exec apache2-foreground
