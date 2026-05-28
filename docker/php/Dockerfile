# Utilisation de l'image PHP avec Apache
FROM php:8.2-apache

# Installation des outils nécessaires pour compiler les extensions (comme pecl)
RUN apt-get update && apt-get install -y \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Installation de l'extension PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# NOUVEAUTÉ SI40 : Installation et activation de l'extension MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# On active le module rewrite d'Apache
RUN a2enmod rewrite

# On garde la racine sur /var/www/html pour que tes scripts dans api/ soient accessibles directement
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Dossier de travail dans le conteneur
WORKDIR /var/www/html