# Build  docker build -t leafdev.top/ecosystem/oauth:v0.2.1-fix-1 . && docker push leafdev.top/ecosystem/oauth:v0.2.1-fix-1

FROM leafdev.top/leaf/docker-php-image:8.3

WORKDIR /app

# 初始化
COPY start.sh /usr/bin/start.sh
RUN useradd -ms /bin/bash -u 1337 www && chmod +x /usr/bin/start.sh && chown www:www /app
COPY composer.* /app
COPY artisan /app
COPY --chown=1337:1337 init.php /app
USER www
RUN php init.php

# 安装依赖
RUN composer config -g repo.packagist composer https://packagist.org &&  \
    rm -rf ~/.composer/cache && \
    composer install --no-dev --no-scripts --no-autoloader --no-plugins

# 复制项目代码
COPY --chown=1337:1337 . /app

RUN mkdir -p /app/bootstrap/cache && chown www:www -R /app/bootstrap/cache
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative && \
    composer clear-cache

# 安装 RoadRunner
RUN ./vendor/bin/rr get-binary && art octane:install --server=roadrunner



# 缓存视图
RUN art view:cache

EXPOSE 8000

CMD [ "/usr/bin/php", "/app/artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--workers=1" ]

# Start queue
# CMD [ "/usr/bin/php", "/app/artisan", "init", "queue", "--tries=3", "--timeout=60" ]
