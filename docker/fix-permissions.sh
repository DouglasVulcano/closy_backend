#!/bin/bash

# Script para corrigir permissões no container Docker
# Execute este script dentro do container para resolver problemas de cache

echo "Corrigindo permissões do Laravel..."

# Criar diretórios necessários se não existirem
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Definir propriedade para www-data
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Definir permissões corretas
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Permissões específicas para cache
chmod -R 775 /var/www/html/storage/framework/cache
chmod -R 775 /var/www/html/storage/framework/cache/data

echo "Permissões corrigidas com sucesso!"
echo "Agora você pode executar:"
echo "  php artisan cache:clear"
echo "  php artisan config:clear"
echo "  php artisan view:clear"
