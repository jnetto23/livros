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

# Build (se não houver script, cria a pasta para não quebrar o COPY depois)
RUN npm run build || (echo "⚠️  Sem script build; criando public/build vazio" && mkdir -p /app/public/build)

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
 && docker-php-ext-install -j"$(nproc)" \
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
ARG APP_USER=app
ARG APP_HOME=/var/www/html

# Criação segura de grupo/usuário mesmo com GID/UID já existentes
# Usa shadow temporariamente e remove na mesma camada
RUN set -eux; \
    apk add --no-cache --virtual .userbuild-deps shadow; \
    # Grupo (se GID já existir, renomeia para APP_USER; senão cria)
    if getent group "${GID}" >/dev/null; then \
        EXIST_GRP="$(getent group "${GID}" | cut -d: -f1)"; \
        if [ "${EXIST_GRP}" != "${APP_USER}" ]; then groupmod -n "${APP_USER}" "${EXIST_GRP}"; fi; \
    else \
        groupadd -g "${GID}" "${APP_USER}"; \
    fi; \
    # Usuário (se já existir, ajusta UID/GID; senão cria)
    if id -u "${APP_USER}" >/dev/null 2>&1; then \
        usermod -u "${UID}" -g "${GID}" "${APP_USER}"; \
    else \
        useradd -u "${UID}" -g "${GID}" -M -s /bin/sh "${APP_USER}"; \
    fi; \
    mkdir -p "${APP_HOME}"; chown -R "${APP_USER}:${APP_USER}" "${APP_HOME}"; \
    apk del .userbuild-deps

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
 && chown -R "${APP_USER}:${APP_USER}" storage bootstrap/cache \
 && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER ${APP_USER}
EXPOSE 9000
CMD ["php-fpm", "-F"]

# =========================
# STAGE: dev (bind-mount)
# =========================
FROM php_base AS dev

ARG UID=1000
ARG GID=1000
ARG APP_USER=app
ARG APP_HOME=/var/www/html

# Criação segura de grupo/usuário (mesma lógica do prod)
RUN set -eux; \
    apk add --no-cache --virtual .userbuild-deps shadow; \
    if getent group "${GID}" >/dev/null; then \
        EXIST_GRP="$(getent group "${GID}" | cut -d: -f1)"; \
        if [ "${EXIST_GRP}" != "${APP_USER}" ]; then groupmod -n "${APP_USER}" "${EXIST_GRP}"; fi; \
    else \
        groupadd -g "${GID}" "${APP_USER}"; \
    fi; \
    if id -u "${APP_USER}" >/dev/null 2>&1; then \
        usermod -u "${UID}" -g "${GID}" "${APP_USER}"; \
    else \
        useradd -u "${UID}" -g "${GID}" -M -s /bin/sh "${APP_USER}"; \
    fi; \
    mkdir -p "${APP_HOME}"; chown -R "${APP_USER}:${APP_USER}" "${APP_HOME}"; \
    apk del .userbuild-deps

# Composer para dev
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Prepara diretórios de runtime
RUN mkdir -p storage bootstrap/cache \
 && chown -R "${APP_USER}:${APP_USER}" storage bootstrap/cache \
 && chmod -R ug+rwX storage bootstrap/cache

# Entrypoint
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER ${APP_USER}
EXPOSE 9000
CMD ["php-fpm", "-F"]
