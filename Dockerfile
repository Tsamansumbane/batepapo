FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev nodejs npm \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN rm -rf public/build
RUN npm run build
RUN ls -la public/build
RUN ls -la public/build/assets

RUN chmod +x start.sh

CMD ["bash", "start.sh"]