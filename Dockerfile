FROM php:8.2-zts

RUN apt-get update && \
    apt-get install -y libzip-dev zip && \
    docker-php-ext-install zip

WORKDIR /app

RUN apt-get install -y git && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get install -y default-mysql-client && \
    docker-php-ext-install pdo pdo_mysql && \
    apt-get clean

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app", "example.php"]
