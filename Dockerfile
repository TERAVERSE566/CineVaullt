FROM php:8.2-apache

# Enable Apache mod_rewrite for URL routing
RUN a2enmod rewrite

# Install PHP extensions required for TiDB / MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy the application code to the Apache document root
COPY . /var/www/html/

# Update permissions
RUN chown -R www-data:www-data /var/www/html

# Pass Environment Variables from Render to PHP through Apache
RUN echo "PassEnv DATABASE_URL DB_HOST DB_PORT DB_USER DB_PASS DB_NAME DB_USERNAME DB_PASSWORD DB_DATABASE RENDER" > /etc/apache2/conf-enabled/passenv.conf

EXPOSE 80
