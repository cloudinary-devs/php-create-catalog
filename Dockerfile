# Use the base PHP image
FROM php:8.1.31-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo_mysql 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer