#!/bin/sh
set -e

# El código vive en un volumen montado desde el host. var/ (cache, logs y la
# base SQLite) lo tiene que escribir Apache (www-data), así que le damos la
# propiedad en cada arranque. Esto hace que "clone & run" funcione sin pasos
# manuales de permisos.
mkdir -p var
chown -R www-data:www-data var

# Primer arranque tras el clone: vendor/ no existe (está en .gitignore), así que
# instalamos las dependencias. Esto hace que "clone & run" funcione sin tener
# PHP ni Composer en el host.
if [ ! -f vendor/autoload.php ]; then
    echo "Instalando dependencias (composer install)..."
    composer install --no-interaction --prefer-dist --no-progress
    chown -R www-data:www-data var
fi

exec apache2-foreground
