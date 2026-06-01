FROM php:8.1-apache

# Extensión de MySQL 
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar el código fuente
COPY ./src /var/www/html

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Error 404
RUN echo 'ErrorDocument 404 /404.php' >> /etc/apache2/sites-enabled/000-default.conf