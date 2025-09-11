# 🏦 Sistema de Saque PIX - HyperF 3

Sistema completo de saques PIX desenvolvido com HyperF 3, incluindo agendamento de saques, processamento automático e notificações por email.

## 📚 Sobre Este Projeto

Este é um **projeto de estudo** desenvolvido para explorar e demonstrar as funcionalidades do **HyperF 3**, um framework PHP moderno baseado em Swoole. O objetivo é criar um sistema real de saques PIX que exemplifique as melhores práticas e recursos avançados do framework.

### 🎯 **Objetivos de Aprendizado:**
- ✅ **Eventos e Listeners**: Sistema de eventos para desacoplamento
- ✅ **Docker**: Containerização completa da aplicação
- ✅ **Migrations e Seeders**: Gestão de banco de dados
- ✅ **API REST**: Endpoints bem estruturados
- ✅ **Validação**: Validação de dados de entrada
- ✅ **Logs**: Sistema de log robusto
- ✅ **Cron Jobs**: Processamento automático
- ✅ **Redis**: Cache e deduplicação

## 🚀 Início Rápido

### Pré-requisitos
- Docker
- Docker Compose
- Git

### Instalação e Execução

1. **Clone o repositório:**
```bash
git clone https://github.com/mauriciohaygert/hyperf-pix-example
cd hyperf-pix-example
```

2. **Inicie a aplicação:**
```bash
# Executar script (Linux) que copia .env e inicia os containers
./start.sh

## EXECUTAR MANUALMENTE
cp env.example .env
docker compose up -d
```

3. **Aguarde oExecute as migrações e seeders:**

> **Aguarde alguns segundos até o container Mysql estar pronto**

```bash
# Executar script (Linux) que executa Migrate e Seed
./migrate.sh

## EXECUTAR MANUALMENTE
# Executar migrações do banco de dados
docker-compose exec app php bin/hyperf.php migrate
# Executar seeders para criar dados de teste
docker-compose exec app php bin/hyperf.php db:seed
```

> **💡 Dica**: Após executar os seeders, os IDs das contas criadas serão salvos em `storage/client_ids.txt` para facilitar os testes da API.

4. **Acesse a aplicação:**
- **API**: http://localhost:8080
- **Mailhog**: http://localhost:8025

## 📋 Funcionalidades

### ✅ Saques PIX
- **Saque Instantâneo**: Processamento imediato
- **Saque Agendado**: Agendamento para data/hora específica
- **Validação de Saldo**: Verificação automática de fundos
- **Notificações**: Emails automáticos de confirmação

### ✅ Sistema de Contas
- **Múltiplas Contas**: Suporte a vários clientes
- **Gestão de Saldo**: Controle automático de débitos
- **Histórico**: Rastreamento completo de transações

### ✅ Processamento Automático
- **Cron Jobs**: Processamento automático de saques agendados
- **Eventos**: Sistema de eventos para notificações
- **Deduplicação**: Prevenção de emails duplicados

## 🛠️ Arquitetura e Tecnologias

### 🏗️ Stack Tecnológico
- **Framework**: HyperF 3 (PHP 8.1+)
- **Banco de Dados**: MySQL 8.0
- **Cache/Filas**: Redis
- **Email**: Mailhog (desenvolvimento)
- **Containerização**: Docker + Docker Compose

### 🎯 Por que Eventos?

O sistema utiliza **Eventos** em vez de chamadas diretas por várias razões:

#### 🔄 **Desacoplamento**
- **Separação de Responsabilidades**: O serviço de saque não precisa conhecer detalhes de notificação
- **Flexibilidade**: Fácil adição de novos listeners (SMS, Push, etc.)
- **Manutenibilidade**: Mudanças em notificações não afetam o core do negócio

#### 📈 **Escalabilidade**
- **Processamento Assíncrono**: Eventos podem ser processados em background
- **Múltiplos Listeners**: Vários sistemas podem reagir ao mesmo evento
- **Performance**: Operações não-críticas não bloqueiam o fluxo principal

#### 🛡️ **Robustez**
- **Retry Automático**: Sistema de retry para falhas temporárias
- **Deduplicação**: Prevenção de processamento duplicado
- **Logs Detalhados**: Rastreamento completo de eventos

#### 🔧 **Extensibilidade**
```php
// Fácil adição de novos listeners
#[Listener]
class SMSNotificationListener implements ListenerInterface
{
    public function process(object $event): void
    {
        // Enviar SMS quando saque for processado
    }
}
```

## 📊 API Endpoints

### 🏦 Contas
- `GET /account/{id}` - Detalhes da conta
- `GET /account/{id}/balance` - Saldo da conta
- `GET /account/{id}/withdraws` - Histórico de saques

### 💸 Saques
- `POST /account/{id}/balance/withdraw` - Criar saque
  ```json
  {
    "method": "PIX",
    "pix": {
      "type": "email",
      "key": "cliente@example.com"
    },
    "amount": 100.00,
    "scheduled": false,
    "scheduled_for": "2024-12-25 10:00:00"
  }
  ```

## 📁 Estrutura do Projeto

```
├── app/
│   ├── Command/              # Comandos CLI
│   ├── Controller/           # Controladores HTTP
│   ├── Crontab/             # Tarefas agendadas
│   ├── Domain/              # Lógica de domínio
│   │   ├── Command/         # Commands do domínio
│   │   ├── DTO/             # Data Transfer Objects
│   │   └── Event/           # Eventos do domínio
│   ├── Listener/            # Event Listeners
│   ├── Model/               # Modelos Eloquent
│   ├── Repository/          # Repositórios
│   └── Service/             # Serviços de negócio
├── config/                  # Configurações
├── docker/                  # Configurações Docker
├── migrations/              # Migrações do banco
├── seeders/                 # Seeders do banco
├── storage/                 # Arquivos de armazenamento
└── test/                    # Testes
```

## 🧪 Testando a API

### 📮 Collection do Postman

O projeto inclui uma **collection completa do Postman** (`HyperF-PIX-Example.postman_collection.json`) com:

#### 🔧 **Configuração**
- **Environment**: Variáveis configuráveis
- **Base URL**: `{{base_url}}` (http://localhost:8080)
- **Account ID**: `{{account_id}}` (ID de conta válida)

#### 📋 **Requests Incluídos**
1. **API Info** - Informações da API
2. **Account Details** - Detalhes da conta
3. **Account Balance** - Saldo da conta
4. **Withdraw (Instant)** - Saque instantâneo
5. **Withdraw (Scheduled)** - Saque agendado
6. **Withdraw History** - Histórico de saques

#### 🚀 **Como Usar**
1. **Importe** a collection no Postman
2. **Configure** as variáveis de ambiente:
   - `base_url`: http://localhost:8080
   - `account_id`: Use um ID de conta válido
3. **Execute** os requests na ordem desejada

### 🎯 **IDs de Conta Disponíveis**
Após executar os seeders, você encontrará os IDs das contas em:
- **Arquivo**: `storage/client_ids.txt`
- **Comando**: `docker-compose exec app php bin/hyperf.php db:seed`

## 🔧 Comandos de Desenvolvimento

### 🐳 Docker
```bash
# Iniciar aplicação
./start.sh

# Parar aplicação
./stop.sh

# Ver logs
docker-compose logs -f app

# Acessar container
docker-compose exec app bash
```

### 🗄️ Banco de Dados
```bash
# Executar migrações
docker-compose exec app php bin/hyperf.php migrate

# Executar seeders
docker-compose exec app php bin/hyperf.php db:seed

# Limpar e recriar banco
docker-compose down -v && ./start.sh
```

### ⚙️ Comandos CLI
```bash
# Processar saques agendados
docker-compose exec app php bin/hyperf.php withdraw:process-scheduled

# Listar comandos disponíveis
docker-compose exec app php bin/hyperf.php list
```

## 🔍 Monitoramento e Logs

### 📧 **Emails de Teste**
- **Mailhog**: http://localhost:8025
- **Visualização**: Interface web para ver emails enviados
- **Debug**: Verificar se notificações estão sendo enviadas

### 📊 **Logs da Aplicação**
```bash
# Logs em tempo real
docker-compose logs -f app

# Logs específicos
docker-compose logs app | grep "WithdrawProcessed"
```

### 🔄 **Sistema de Eventos**
- **Logs de Eventos**: Rastreamento completo de eventos
- **Deduplicação**: Logs de eventos duplicados ignorados
- **Performance**: Métricas de processamento

## 🚀 Deploy e Produção

### 🔧 **Configurações de Produção**
- **Workers**: Ajustar `OPTION_WORKER_NUM` conforme CPU
- **Redis**: Configurar cluster Redis para alta disponibilidade
- **Email**: Substituir Mailhog por serviço real (SMTP/SES)
- **SSL**: Configurar HTTPS e certificados

### 📈 **Escalabilidade**
- **Load Balancer**: Nginx/HAProxy para múltiplas instâncias
- **Filas Assíncronas**: Implementar jobs para processamento em background
- **Cache**: Redis para cache de consultas frequentes
- **Database**: Replicação MySQL para leitura

## 🤝 Contribuição

1. **Fork** o projeto
2. **Crie** uma branch para sua feature
3. **Commit** suas mudanças
4. **Push** para a branch
5. **Abra** um Pull Request
