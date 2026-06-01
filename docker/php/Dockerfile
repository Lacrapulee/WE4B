FROM php:8.2-apache

# 1. PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# 2. Enable rewrite module
RUN a2enmod rewrite

# 3. Set document root to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Patch the DocumentRoot AND the Directory path in the vhost
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

# Patch the global apache2.conf Directory block too
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/../!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# 4. Allow access and enable .htaccess overrides for the public folder
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/public-dir.conf \
    && a2enconf public-dir