# Install all the depedencies of your symfony project
FROM composer:latest as build
ENV APP_ENV=prod
WORKDIR /app/
COPY composer.json composer.lock /app/
RUN composer install --no-scripts --no-autoloader \
    && composer dump-autoload --optimize

FROM php:8.3-apache
ENV APP_ENV=prod
ENV DATABASE_URL="postgresql://postgres:Aurelien&23@34.105.176.221:5432/tutorial"

# Configure PHP for Cloud Run.
# Precompile PHP code with opcache.
RUN docker-php-ext-install -j "$(nproc)" opcache
RUN set -ex; \
  { \
    echo "; Cloud Run enforces memory & timeouts"; \
    echo "memory_limit = -1"; \
    echo "max_execution_time = 0"; \
    echo "; File upload at Cloud Run network limit"; \
    echo "upload_max_filesize = 1M"; \
    echo "post_max_size = 1M"; \
    echo "; Configure Opcache for Containers"; \
    echo "opcache.enable = On"; \
    echo "opcache.validate_timestamps = Off"; \
    echo "; Configure Opcache Memory (Application-specific)"; \
    echo "opcache.memory_consumption = 32"; \
  } > "$PHP_INI_DIR/conf.d/cloud-run.ini"

WORKDIR /var/www

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --from=build /app/vendor /var/www/vendor
COPY . /var/www/

RUN chown -R www-data:www-data /var/www/

RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN a2enmod rewrite

RUN apt-get install -y libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql

USER www-data
# If your project does not have assets, use this instead:
RUN php bin/console cache:clear --no-warmup && \
    php bin/console cache:warmup

CMD ["apache2-foreground"]