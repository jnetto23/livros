#!/usr/bin/env bash
set -e

cd /var/www/html

# Se vendor não existe (ex.: bind-mount dev), instala
if [ ! -d "vendor" ] && [ -f "composer.json" ]; then
  echo "🧩 Rodando composer install..."
  composer install --prefer-dist --no-interaction --no-progress
fi

# Gera .env se não existir
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
  echo "📄 Criando .env a partir de .env.example..."
  cp .env.example .env
fi

# Gera APP_KEY se vazio
if [ -f ".env" ]; then
  APP_KEY_VAL=$(grep '^APP_KEY=' .env | cut -d= -f2-)
  if [ -z "$APP_KEY_VAL" ]; then
    echo "🔐 Gerando APP_KEY..."
    php artisan key:generate --force
  fi
fi

# Permissões runtime (compatível com bind-mount)
echo "🔧 Ajustando permissões de storage/ e bootstrap/cache/..."
mkdir -p storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache || true

# (Opcional) Migrações automáticas em dev:
# php artisan migrate --force || true

echo "🚀 Iniciando PHP-FPM..."
exec php-fpm -F
