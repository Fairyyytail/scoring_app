FROM php:8.3-cli-alpine AS scoring_app

RUN apk add --no-cache git zip bash mariadb-dev \
    && docker-php-ext-install pdo_mysql
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG USER_ID=1000

RUN adduser -u ${USER_ID} -D -H app

WORKDIR /app

COPY --chown=app . /app

COPY --chown=app docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER app

EXPOSE 8337

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php", "-S", "0.0.0.0:8337", "-t", "public"]