#!/bin/bash
set -e

echo "🛑 Parando HyperF PIX Example..."

# Parar containers
docker-compose down

echo "✅ Aplicação parada com sucesso!"
