FROM registry.daisukide.com:2083/leaf/docker-php-image:latest

WORKDIR /app

COPY . /app

RUN rm -rf vendor/ && composer install --no-dev
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative
RUN ./vendor/bin/rr get-binary
RUN art octane:install --server=roadrunner


ENTRYPOINT [ "art", "octane:start" ]