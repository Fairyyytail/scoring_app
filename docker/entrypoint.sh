#!/bin/sh
set -e

if [ ! -f /app/vendor/autoload.php ]; then
    echo "vendor/ not found, running composer install..."
    composer install --no-interaction --prefer-dist
fi

echo "Waiting for MariaDB..."

until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
    sleep 1
done

echo "MariaDB is ready."

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction --append

exec "$@"