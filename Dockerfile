FROM php:5.6-cli

WORKDIR /usr/src/vote

COPY . .

RUN apt-get update && apt-get install -y libpq-dev zlib1g-dev libxml2-dev libpng-dev libfreetype6-dev && docker-php-ext-install mysqli pdo pdo_mysql soap zip gd
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install
EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "./", "./index.php"]