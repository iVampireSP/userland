FROM registry.daisukide.com:2083/leaf/docker-php-image:latest

WORKDIR /app

COPY . /app

RUN useradd -ms /bin/bash -u 1337 www && rm -rf vendor/

RUN apt update && apt install supervisor -y 
# unset composer repo
RUN composer config -g repo.packagist composer https://packagist.org
RUN composer install --no-dev
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative
RUN ./vendor/bin/rr get-binary
RUN art octane:install --server=roadrunner

COPY deploy/start-container /usr/local/bin/start-container
COPY deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]