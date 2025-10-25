# Sistema de Multi-Tenancy Customizado

Este documento descreve o sistema de multi-tenancy customizado implementado para o projeto Check API, usando PostgreSQL com schemas separados.

## Visão Geral

O sistema utiliza **schemas PostgreSQL** para separar os dados de cada tenant, mantendo uma única base de dados (`check_api`) com schemas nomeados como `tenant_1000`, `tenant_1001`, etc.

### Características Principais

- ✅ **Sem dependências externas** - Sistema totalmente customizado
- ✅ **PostgreSQL Schemas** - Isolamento completo de dados
- ✅ **IDs customizados** - Use IDs numéricos como 1000, 1001
- ✅ **Identificação flexível** - Por header, URL, domínio ou usuário
- ✅ **Migrations automáticas** - Execução automática para novos tenants
- ✅ **Comandos Artisan** - Gerenciamento completo via CLI

## Estrutura do Sistema

### Modelos Principais

#### `App\Models\Tenant`
Modelo principal que gerencia tenants e schemas.

**Principais métodos:**
- `makeCurrent()` - Ativa o tenant (configura search_path)
- `createSchema()` - Cria schema PostgreSQL
- `deleteSchema()` - Remove schema PostgreSQL
- `runMigrations()` - Executa migrations no schema do tenant
- `schemaExists()` - Verifica se schema existe

#### `App\Models\TenantDomain`
Gerencia domínios associados aos tenants.

### Middleware

#### `App\Http\Middleware\TenancyMiddleware`
Identifica e ativa tenants automaticamente baseado em:

1. **Header** `X-Account-ID: 1000`
2. **Query Parameter** `?account_id=1000`
3. **URL Pattern** `/api/tenant/1000/...`
4. **Subdomínio** `1000.check-api.com`
5. **Usuário autenticado** (campo `tenant_id`)

## Comandos Artisan

### Criar Tenant
```bash
# Criar tenant sem domínio (mesmo domínio)
php artisan tenant:create 1000 "Empresa ABC" --run-migrations

# Criar tenant com domínio específico (opcional)
php artisan tenant:create 1000 "Empresa ABC" --domain=empresa-abc.localhost --run-migrations
```

**Parâmetros:**
- `id` - ID do tenant (ex: 1000)
- `name` - Nome do tenant
- `--domain` - Domínio específico (opcional - para compatibilidade)
- `--run-migrations` - Executar migrations automaticamente

### Listar Tenants
```bash
php artisan tenant:list
php artisan tenant:list --status=active
```

### Executar Migrations
```bash
# Para um tenant específico
php artisan tenant:migrate 1000

# Para todos os tenants
php artisan tenant:migrate --all
```

### Deletar Tenant
```bash
php artisan tenant:delete 1000 --force
php artisan tenant:delete 1000 --force --keep-schema
```

### Testar Sistema
```bash
php artisan tenant:test 1000
```

## Configuração de Rotas

### Identificação por URL
```php
// routes/api.php
Route::prefix('tenant/{account_id}')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('checklists', ChecklistController::class);
    // ... outras rotas
});
```

### Identificação por Header
```bash
curl -H "X-Account-ID: 1000" https://api.check-api.com/dashboard
```

### Identificação por Query Parameter
```bash
curl https://api.check-api.com/dashboard?account_id=1000
```

## Estrutura de Banco

### Schema Central (`public`)
```sql
-- Tabela de tenants
tenants (
    id VARCHAR PRIMARY KEY,          -- Ex: "1000"
    data JSONB,                      -- {"name": "Empresa ABC"}
    schema_name VARCHAR UNIQUE,      -- "tenant_1000"
    database_name VARCHAR,           -- Opcional
    status VARCHAR DEFAULT 'active', -- active|inactive|suspended
    settings JSONB,                  -- Configurações extras
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)

-- Tabela de domínios
tenant_domains (
    id SERIAL PRIMARY KEY,
    tenant_id VARCHAR REFERENCES tenants(id),
    domain VARCHAR UNIQUE,           -- "empresa-abc.localhost"
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

### Schema do Tenant (`tenant_1000`)
Cada tenant possui seu próprio schema com todas as tabelas da aplicação:
- `users`
- `offices`
- `clients`
- `vehicles`
- `checklists`
- `equipment`
- `tires`
- `maintenance_schedules`
- `maintenance_records`
- etc.

## Uso no Código

### Verificar Tenant Atual
```php
use App\Models\Tenant;

// Verificar se há tenant ativo
if (Tenant::hasCurrent()) {
    $tenant = Tenant::current();
    echo "Tenant ativo: " . $tenant->name;
}
```

### Ativar Tenant Manualmente
```php
$tenant = Tenant::find('1000');
if ($tenant) {
    $tenant->makeCurrent();
    
    // Agora todas as queries usarão o schema do tenant
    $users = User::all(); // Busca no schema tenant_1000
}
```

### Resetar Tenant
```php
Tenant::forgetCurrent();
// Volta para o schema public
```

## Migrations

### Estrutura de Diretórios
```
database/migrations/
├── 2025_01_01_000001_create_tenants_table.php        # Central
├── 2025_01_01_000002_create_tenant_domains_table.php # Central
└── tenant/                                           # Tenant-specific
    ├── 2024_01_01_000002_create_offices_table.php
    ├── 2024_01_01_000003_add_tenant_and_office_to_users_table.php
    ├── 2024_01_01_000004_create_clients_table.php
    └── ... (todas as migrations do tenant)
```

### Executar Migrations
```bash
# Migrations centrais (schema public)
php artisan migrate

# Migrations do tenant (schema tenant_1000)
php artisan tenant:migrate 1000
```

## Exemplos de Uso

### 1. API com Header
```bash
curl -H "X-Account-ID: 1000" \
     -H "Authorization: Bearer token" \
     https://api.check-api.com/api/checklists
```

### 2. API com URL
```bash
curl -H "Authorization: Bearer token" \
     https://api.check-api.com/api/tenant/1000/checklists
```

### 3. Frontend com Subdomínio
```javascript
// Em 1000.check-api.com
fetch('/api/checklists') // Automaticamente usa tenant 1000
```

## Troubleshooting

### Verificar Schema
```sql
-- Listar schemas
SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'tenant_%';

-- Listar tabelas do tenant
SELECT table_name FROM information_schema.tables WHERE table_schema = 'tenant_1000';
```

### Verificar Search Path
```sql
SHOW search_path;
```

### Logs
```bash
# Verificar logs do Laravel
tail -f storage/logs/laravel.log | grep -i tenant
```

## Vantagens do Sistema Customizado

1. **Controle Total** - Sem dependências externas
2. **Performance** - Schemas PostgreSQL são muito eficientes
3. **Flexibilidade** - Fácil customização e extensão
4. **Simplicidade** - Código limpo e fácil de entender
5. **Manutenibilidade** - Sem complexidade desnecessária
6. **Escalabilidade** - PostgreSQL schemas escalam muito bem

## Próximos Passos

1. **Backup por Tenant** - Implementar backup individual de schemas
2. **Monitoramento** - Métricas por tenant
3. **Cache por Tenant** - Implementar cache isolado
4. **Jobs por Tenant** - Filas separadas por tenant
5. **Logs por Tenant** - Separação de logs

---

**Criado em:** 24/10/2025  
**Versão:** 1.0  
**Sistema:** Laravel + PostgreSQL Multi-Tenancy Customizado
