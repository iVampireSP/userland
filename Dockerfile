FROM registry.leafdev.top/leaf/docker-php-image:latest

WORKDIR /app

COPY . /app
COPY start.sh /usr/bin/start.sh

RUN useradd -ms /bin/bash -u 1337 www && chown -R 1337:1337 /app && chmod +x /usr/bin/start.sh
RUN wget https://github.com/roadrunner-server/roadrunner/releases/download/v2024.1.5/roadrunner-2024.1.5-linux-amd64.deb -O /tmp/rr.deb && \
    apt autoremove --purge roadrunner* -y && \
    dpkg -i /tmp/rr.deb  && \
    rm /tmp/rr.deb

# Switch to non-root user 
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
    art octane:install --server=roadrunner


# COPY deploy/start-container /usr/local/bin/start-container
# COPY deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# COPY vendor /app/vendor
# RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

# ENTRYPOINT ["start-container"]
# Start Web
# CMD [ "/usr/bin/php", "/app/artisan", "app:init", "--start" ]
CMD [ "/usr/bin/php", "/app/artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--workers=1" ]

# Start queue
# CMD [ "/usr/bin/php", "/app/artisan", "init", "queue", "--tries=3", "--timeout=60" ]
