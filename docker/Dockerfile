FROM alpine:3.20.3

RUN apk add --no-cache curl nginx php83 php83-fpm php83-opcache php83-pdo php83-pdo_sqlite php83-openssl && \
    mkdir /var/www/html

COPY . /var/www/html

COPY docker/php-fpm.conf /etc/php83/php-fpm.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/settings.php /var/www/html/settings.php

# application code stays root-owned and read-only for the php/nginx user;
# only the data directory is writable
RUN rm -rf /var/www/html/docker && \
    chown -R root:root /var/www/html && \
    chmod -R a+rX,go-w /var/www/html && \
    mkdir -p /var/www/html/data && \
    chown nginx:nginx /var/www/html/data && \
    mkdir -p /var/run/nginx && \
    mkdir -p /var/run/php-fpm && \
    chown -R nginx:nginx /var/run/ && \
    chmod +x /entrypoint.sh

VOLUME /var/www/html/data

ENTRYPOINT ["/bin/ash", "/entrypoint.sh"]
