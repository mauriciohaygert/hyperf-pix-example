#!/bin/bash
set -e

echo "🚀 Iniciando Migracão e Seeders..."

docker-compose exec app php bin/hyperf.php migrate
docker-compose exec app php bin/hyperf.php db:seed

echo "✅ Migracão e Seeders iniciadas com sucesso!"