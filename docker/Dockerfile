FROM alpine:3.24.3

# apk upgrade first: the alpine base layer ships whatever package versions
# existed at tag time, so without it the weekly rebuilds never pick up fixes
# for base packages (musl, zlib, openssl, ca-certificates).
RUN apk upgrade --no-cache && \
    apk add --no-cache curl nginx php84 php84-fpm php84-opcache php84-pdo php84-pdo_sqlite php84-openssl && \
    mkdir /var/www/html

# PHP2Shell (CVE-2026-109618, CVE-2026-109619): php-fpm RCE fixed in PHP 8.4.27.
# php84 is deliberately not pinned so future Alpine fixes are picked up
# automatically; this guard only refuses to build a known-vulnerable image.
RUN php84 -r 'exit(version_compare(PHP_VERSION, "8.4.27", ">=") ? 0 : 1);'

COPY . /var/www/html

COPY docker/php-fpm.conf /etc/php84/php-fpm.conf
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
