FROM php:8.3-apache
RUN mkdir -p /sessions \
    && chown www-data:www-data /sessions \
    && echo "session.save_path = /sessions" > /usr/local/etc/php/conf.d/session-path.ini
COPY . /var/www/html
