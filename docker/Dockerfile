FROM php:8.2-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Inštalácia požadovaných balíčkov
RUN apt-get update && apt-get install -y \
#    libpng-dev \
#    libfreetype6-dev \
#    libjpeg62-turbo-dev \
#    zlib1g-dev \
#    libonig-dev \
#    libgd-dev \
#    && docker-php-ext-configure gd \
#       --with-freetype \
#       --with-jpeg \
    git unzip \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

ENV TZ=Europe/Bratislava
RUN ln -sf /usr/share/zoneinfo/Europe/Bratislava /etc/localtime && \
    echo "Europe/Bratislava" > /etc/timezone

COPY ./php.ini /usr/local/etc/php

# na prod netreba - tam sa pojde cez verejnu IP
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Skopíruj apache konfiguráciu (voliteľné)
COPY apache.conf /etc/apache2/sites-available/

RUN a2dissite 000-default.conf && \
    a2ensite apache.conf && \
    a2enmod rewrite

RUN service apache2 restart

#CMD ["php", "bin/console", "app:subscribe:humidity"]
