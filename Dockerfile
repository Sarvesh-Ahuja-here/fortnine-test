FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf

COPY src/ /var/www/html/

# Some platforms (e.g. Railway) lose layer whiteouts, resurrecting the
# mpm_event module the base image disabled; ensure a single MPM at runtime.
CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*; exec apache2-foreground"]
