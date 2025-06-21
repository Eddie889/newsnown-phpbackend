# Use official PHP image with Apache
FROM php:8.2-apache

# Enable mod_rewrite (optional, useful for routing)
RUN a2enmod rewrite

# Copy project files into Apache's web root
COPY . /var/www/html/

# Set proper file permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose Apache default port (required by Render)
EXPOSE 80
