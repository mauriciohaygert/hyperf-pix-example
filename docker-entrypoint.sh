#!/bin/bash
set -e

echo "=== Docker Entrypoint iniciado ==="
echo "Diretório atual: $(pwd)"
echo "Conteúdo do diretório:"
ls -la

if [ ! -f "vendor/autoload.php" ]; then
    echo "Instalando dependências do Composer..."
    composer install
    echo "Dependências instaladas com sucesso!"
else
    echo "Dependências já estão instaladas."
fi

echo "Verificando se vendor/autoload.php existe:"
ls -la vendor/autoload.php || echo "vendor/autoload.php não encontrado!"

echo "=== Executando comando: $@ ==="
exec "$@"
