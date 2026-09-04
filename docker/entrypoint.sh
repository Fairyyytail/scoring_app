#!/bin/sh
set -e

echo "Waiting for MariaDB..."

until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
    sleep 1
done

echo "MariaDB is ready."

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

exec "$@"