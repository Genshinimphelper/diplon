FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pgsql pdo_pgsql
RUN a2enmod rewrite
COPY . /var/www/html/
RUN mkdir -p /var/www/html/images /var/www/html/avatars && chmod -R 777 /var/www/html/images /var/www/html/avatars
EXPOSE 80
CMD ["apache2-foreground"]
