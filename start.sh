#!/bin/bash
set -e

echo "🚀 Iniciando HyperF PIX Example..."

# Executar configuração do ambiente
echo "📋 Configurando ambiente..."
./setup-env.sh

# Iniciar containers
echo "🐳 Iniciando containers Docker..."
docker-compose up -d

echo "✅ Aplicação iniciada com sucesso!"
echo ""
echo "📊 Status dos containers:"
docker-compose ps

echo ""
echo "🌐 URLs disponíveis:"
echo "   • Aplicação: http://localhost:8080"
echo "   • Swagger: http://localhost:8080/swagger"
echo "   • Mailhog: http://localhost:8025"
echo ""
echo "📝 Para ver os logs: docker-compose logs -f"
echo "🛑 Para parar: docker-compose down"
