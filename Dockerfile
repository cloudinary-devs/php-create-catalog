# Use the base PHP image
FROM php:8.1.31-apache

# Set the working directory inside the container
WORKDIR /var/www/html

# Install necessary PHP extensions (including SQLite for DATABASE_URL)
RUN docker-php-ext-install pdo pdo_sqlite

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Grant appropriate permissions for the SQLite database
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Enable Apache mod_rewrite (if required)
RUN a2enmod rewrite

# Copy all local files to the working directory
COPY . /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
