FROM php:8.4-cli
 
WORKDIR /var/www
 
# Dependências do sistema + Node 22 (exigido pelo Vite 8 / concurrently 10)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
 
# Extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl
 
# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
 
# O código vem pelo volume (.:/var/www) no docker-compose,
# então não copiamos nada nem rodamos composer/npm install no build.
 
EXPOSE 8000 5173
 
