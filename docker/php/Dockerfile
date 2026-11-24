FROM php:8.3-fpm

# Системные библиотеки, нужные для PHP-расширений
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd intl bcmath exif \
    && rm -rf /var/lib/apt/lists/*

# Composer (менеджер PHP-пакетов) копируем из официального образа
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Кэш Composer храним во временной папке, чтобы пользователь www-data мог в неё писать
ENV COMPOSER_HOME=/tmp/composer

# Разрешаем git работать с папкой проекта (иначе он ругается на «чужого» владельца)
RUN git config --system --add safe.directory /var/www/html

# На Linux файлы проекта принадлежат пользователю хоста (обычно UID 1000).
# Даём www-data тот же номер, чтобы контейнер мог писать в vendor/ и storage/.
# На Windows (Docker Desktop) владелец файлов, созданных внутри контейнера, тоже запоминается.
# Если после смены UID появилось "Permission denied", один раз выполнить:
#   docker compose exec -u root app chown -R 1000:1000 /var/www/html
ARG UID=1000
ARG GID=1000
RUN groupmod -o -g ${GID} www-data && usermod -o -u ${UID} -g ${GID} www-data

# vendor/ хранится в томе Docker (см. docker-compose.yml), а не на диске хоста.
# Создаём папку заранее с владельцем www-data: новый пустой том унаследует эти права.
RUN mkdir -p /var/www/html/vendor && chown www-data:www-data /var/www/html/vendor

WORKDIR /var/www/html
