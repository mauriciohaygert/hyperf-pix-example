#!/bin/bash
set -e

echo "=== Configurando ambiente ==="

# Verificar se o arquivo .env existe, se não, criar baseado no .env.example
if [ ! -f ".env" ]; then
    if [ -f "env.example" ]; then
        echo "Criando arquivo .env baseado no .env.example..."
        cp env.example .env
        echo "✅ Arquivo .env criado com sucesso!"
    else
        echo "❌ ERRO: Arquivo .env.example não encontrado!"
        exit 1
    fi
else
    echo "✅ Arquivo .env já existe."
fi

# Verificar se o diretório storage existe
if [ ! -d "storage" ]; then
    echo "Criando diretório storage..."
    mkdir -p storage
    echo "✅ Diretório storage criado!"
else
    echo "✅ Diretório storage já existe."
fi

echo "=== Ambiente configurado com sucesso! ==="
