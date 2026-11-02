
FROM php:8.3-apache

# Instalar el controlador para conectar PHP con MySQL/MariaDB
RUN docker-php-ext-install pdo_mysql

# Habilitar el módulo de Apache
RUN a2enmod rewrite

# Carpeta donde estará nuestra aplicación
WORKDIR /var/www/html
