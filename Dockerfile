FROM registry.leafdev.top/leaf/docker-php-image:latest

WORKDIR /app

COPY . /app
COPY start.sh /usr/bin/start.sh

RUN useradd -ms /bin/bash -u 1337 www && chown -R 1337:1337 /app && chmod +x /usr/bin/start.sh

USER www

# unset composer repo
RUN composer config -g repo.packagist composer https://packagist.org &&  \
    rm -rf ~/.composer/cache && \
    rm -rf .env && \
    php init.php && rm init.php && \
    composer install --no-dev && \
    composer dump-autoload --optimize --no-dev --classmap-authoritative && \
    composer clear-cache && \
    art view:cache && \
    art octane:install --server=swoole


# COPY deploy/start-container /usr/local/bin/start-container
# COPY deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# COPY vendor /app/vendor
# RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

# ENTRYPOINT ["start-container"]
# Start Web
# CMD [ "/usr/bin/php", "/app/artisan", "app:init", "--start" ]
CMD [ "/usr/bin/php", "/app/artisan", "octane:start", "--host=0.0.0.0", "--workers=1" ]

# Start queue
# CMD [ "/usr/bin/php", "/app/artisan", "init", "queue", "--tries=3", "--timeout=60" ]
