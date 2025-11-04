# =========================
# STAGE: assets (Vite)
# =========================
FROM node:20-alpine AS assets
WORKDIR /app

# Instala deps de front
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* .npmrc* ./
RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

# Copia fontes e configurações do Vite/Tailwind
COPY resources ./resources
COPY vite.config.* postcss.config.* tailwind.config.* ./
COPY public ./public

# Build (se não houver script, ignora)
RUN npm run build || echo "⚠️  Sem script build; ignorando"

# =========================
# STAGE: base php-fpm
# =========================
FROM php:8.3-fpm-alpine AS php_base

# Pacotes de runtime + build (em camada virtual para limpeza)
RUN apk add --no-cache bash git unzip icu icu-dev oniguruma-dev libzip-dev zlib-dev \
    libpng-dev freetype-dev libjpeg-turbo-dev libxml2-dev \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers \
 # Extensões nativas
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
    bcmath intl pcntl pdo_mysql zip gd dom xml simplexml sockets opcache \
 # Limpa build deps
 && apk del .build-deps

# Opcache e ini base
RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.enable_cli=0"; \
  echo "opcache.validate_timestamps=1"; \
  echo "opcache.max_accelerated_files=20000"; \
  echo "opcache.memory_consumption=192"; \
  echo "opcache.interned_strings_buffer=16"; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
  echo "date.timezone=America/Sao_Paulo"; \
  echo "memory_limit=512M"; \
  echo "upload_max_filesize=50M"; \
  echo "post_max_size=50M"; \
  echo "max_execution_time=120"; \
} > /usr/local/etc/php/conf.d/99-custom.ini

WORKDIR /var/www/html

# =========================
# STAGE: prod (imagem imutável)
# =========================
FROM php_base AS prod

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} app && adduser -u ${UID} -G app -D -s /bin/sh app

# Composer oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 1) Copia apenas composer.* para cache melhor de deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts \
 && composer clear-cache

# 2) Agora copia o restante do app
COPY . .

# Reexecuta autoload dump + discover (agora com código)
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# Copia assets buildados
COPY --from=assets /app/public/build ./public/build

# Caches de configuração/rota/view para prod
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

# Permissões runtime
RUN mkdir -p storage bootstrap/cache \
 && chown -R app:app storage bootstrap/cache \
 && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER app
EXPOSE 9000
CMD ["php-fpm", "-F"]

# =========================
# STAGE: dev (bind-mount)
# =========================
FROM php_base AS dev

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} app && adduser -u ${UID} -G app -D -s /bin/sh app

# Composer para dev
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Prepara diretórios de runtime
RUN mkdir -p storage bootstrap/cache \
 && chown -R app:app storage bootstrap/cache \
 && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER app
EXPOSE 9000
CMD ["php-fpm", "-F"]
