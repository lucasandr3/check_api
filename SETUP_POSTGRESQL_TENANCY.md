# 🐘 Configuração PostgreSQL Multi-Tenant

Este guia explica como configurar o sistema para usar PostgreSQL com schemas separados por tenant.

## 📋 Arquitetura

- **Banco Principal**: `check_api` (configuração central, tenants, domínios)
- **Schemas por Tenant**: `tenant_1000`, `tenant_1001`, etc.
- **Identificação**: Por número da conta (ex: 1000, 1001, 1002)

## ⚙️ Configuração do .env

```env
# === CONFIGURAÇÃO POSTGRESQL ===
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=check_api
DB_USERNAME=postgres
DB_PASSWORD=sua_senha_aqui

# === CONFIGURAÇÃO TENANCY ===
TENANCY_CENTRAL_DOMAINS=localhost,127.0.0.1
```

## 🗄️ Configuração do PostgreSQL

### 1. Criar o banco principal
```sql
CREATE DATABASE check_api;
```

### 2. Conectar ao banco e criar extensões (se necessário)
```sql
\c check_api;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
```

## 🚀 Comandos de Gerenciamento

### Testar configuração
```bash
php artisan tenant:test-config
```

### Criar um novo tenant
```bash
php artisan tenant:create-account 1000 "Empresa ABC" "empresa-abc.localhost"
```

### Listar todos os tenants
```bash
php artisan tenant:list
```

### Deletar um tenant
```bash
php artisan tenant:delete 1000
```

### Rodar migrations para um tenant específico
```bash
php artisan tenants:migrate --tenants=1000
```

### Rodar migrations para todos os tenants
```bash
php artisan tenants:migrate
```

### Rodar seeders para um tenant
```bash
php artisan tenants:seed --tenants=1000
```

## 📁 Estrutura dos Schemas

Cada tenant terá seu próprio schema no PostgreSQL:

```
check_api (database)
├── public (schema central)
│   ├── tenants
│   ├── domains
│   └── outras tabelas centrais
├── tenant_1000 (schema do tenant 1000)
│   ├── users
│   ├── vehicles
│   ├── equipment
│   ├── checklists
│   ├── tire_records
│   └── todas as tabelas do tenant
└── tenant_1001 (schema do tenant 1001)
    ├── users
    ├── vehicles
    └── ...
```

## 🔧 Migrations

### Migrations Centrais
Ficam em: `database/migrations/`
- Tenants, domains, configurações centrais

### Migrations de Tenant
Ficam em: `database/migrations/tenant/`
- Todas as tabelas específicas do tenant

## 🌐 Identificação de Tenant

### Por Domínio
```
empresa-abc.localhost → tenant_1000
empresa-xyz.localhost → tenant_1001
```

### Por Subdomínio
```
1000.check-api.com → tenant_1000
1001.check-api.com → tenant_1001
```

## 📝 Exemplo de Uso

### 1. Criar tenant
```bash
php artisan tenant:create-account 1000 "Empresa ABC"
```

### 2. Rodar migrations
```bash
php artisan tenants:migrate --tenants=1000
```

### 3. Rodar seeders
```bash
php artisan tenants:seed --tenants=1000 --class=AclSeeder
```

### 4. Acessar
```
http://account-1000.localhost/api/dashboard/stats
```

## 🔍 Verificação

### Verificar schemas no PostgreSQL
```sql
SELECT schema_name FROM information_schema.schemata 
WHERE schema_name LIKE 'tenant_%';
```

### Verificar tabelas de um schema
```sql
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'tenant_1000';
```

## 🚨 Troubleshooting

### Erro: Schema não encontrado
```bash
# Recriar o schema
php artisan tenant:create-account 1000 "Nome" --force
```

### Erro: Conexão com PostgreSQL
1. Verificar se PostgreSQL está rodando
2. Verificar credenciais no .env
3. Verificar se o banco check_api existe

### Erro: Migrations não rodam
```bash
# Limpar cache
php artisan config:clear
php artisan cache:clear

# Rodar migrations novamente
php artisan tenants:migrate --tenants=1000
```

## 📊 Monitoramento

### Listar todos os tenants e status
```bash
php artisan tenant:list
```

### Verificar tamanho dos schemas
```sql
SELECT 
    schemaname,
    pg_size_pretty(sum(pg_total_relation_size(schemaname||'.'||tablename))::bigint) as size
FROM pg_tables 
WHERE schemaname LIKE 'tenant_%'
GROUP BY schemaname
ORDER BY sum(pg_total_relation_size(schemaname||'.'||tablename)) DESC;
```

## 🎯 Vantagens desta Arquitetura

✅ **Isolamento**: Cada tenant tem seu próprio schema  
✅ **Performance**: Melhor que bancos separados  
✅ **Backup**: Backup único do banco principal  
✅ **Manutenção**: Migrations centralizadas  
✅ **Escalabilidade**: Suporta muitos tenants  
✅ **Segurança**: Isolamento por schema  

## 🔗 Links Úteis

- [Stancl Tenancy Documentation](https://tenancyforlaravel.com/)
- [PostgreSQL Schemas](https://www.postgresql.org/docs/current/ddl-schemas.html)
- [Laravel Multi-Tenancy](https://laravel.com/docs/database#multiple-database-connections)
