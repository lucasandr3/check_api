# 🏢 Sistema de Multi-Empresas (Companies)

Este documento explica como usar o sistema de múltiplas empresas dentro de cada tenant, substituindo o antigo sistema de "offices".

## 📋 Visão Geral

- **Tabela**: `companies` (anteriormente `offices`)
- **Relacionamento**: Todas as tabelas principais agora usam `company_id`
- **Isolamento**: Cada empresa tem seus próprios dados dentro do tenant
- **Flexibilidade**: Usuários podem trabalhar com múltiplas empresas

## 🗄️ Estrutura do Banco

### Tabela Companies
```sql
companies (
    id BIGINT PRIMARY KEY,
    tenant_id VARCHAR,           -- Referência ao tenant
    name VARCHAR,               -- Nome da empresa
    address TEXT,               -- Endereço completo
    phone VARCHAR,              -- Telefone
    email VARCHAR,              -- Email de contato
    cnpj VARCHAR,               -- CNPJ da empresa
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

### Relacionamentos
Todas as tabelas principais agora fazem referência a `company_id`:
- `users.company_id`
- `vehicles.company_id`
- `clients.company_id`
- `equipment.company_id`
- `checklist_templates.company_id`
- `maintenance_schedules.company_id`
- `maintenance_records.company_id`
- `fuel_records.company_id`
- `tire_records.company_id`
- `checklists.company_id`
- `services.company_id`

## 🚀 Como Usar

### 1. **Criar Empresas**

```bash
# Via Seeder (comando customizado)
php artisan tenant:seed 1000 --class=CompanySeeder

# Via Factory (para testes)
php artisan tinker
>>> Company::factory()->count(3)->create();
>>> Company::factory()->main()->create();
>>> Company::factory()->branch()->create();

# Testar o sistema completo
php artisan test:company-system 1000
```

### 2. **Identificar Empresa Atual**

O sistema identifica a empresa atual através de:

#### Header HTTP
```bash
curl -H "X-Company-ID: 1" https://api.check-api.com/vehicles
```

#### Query Parameter
```bash
curl https://api.check-api.com/vehicles?company_id=1
```

#### URL Parameter
```php
Route::get('/companies/{company_id}/vehicles', [VehicleController::class, 'index']);
```

#### Usuário Autenticado
Se não especificado, usa a empresa do usuário logado (`auth()->user()->company_id`)

### 3. **Middleware de Empresa**

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'company'])->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('vehicles', VehicleController::class);
    // ... outras rotas
});
```

### 4. **Usar nos Modelos**

```php
// Usar o trait BelongsToCompany
use App\Traits\BelongsToCompany;

class Vehicle extends Model
{
    use BelongsToCompany;
    
    // Automaticamente terá:
    // - company() relationship
    // - scopeForCompany($companyId)
    // - scopeForCurrentCompany()
}
```

### 5. **Filtrar por Empresa**

```php
// Filtrar por empresa específica
$vehicles = Vehicle::forCompany(1)->get();

// Filtrar pela empresa atual (do contexto)
$vehicles = Vehicle::forCurrentCompany()->get();

// Usar relacionamento
$company = Company::find(1);
$vehicles = $company->vehicles;
```

## 📊 Exemplos de Uso

### API Endpoints

```bash
# Listar empresas
GET /api/companies

# Criar empresa
POST /api/companies
{
    "name": "Nova Empresa",
    "address": "Rua Nova, 123",
    "phone": "(11) 99999-9999",
    "email": "contato@novaempresa.com",
    "cnpj": "12.345.678/0001-90"
}

# Listar veículos de uma empresa específica
GET /api/companies/1/vehicles

# Estatísticas de uma empresa
GET /api/companies/1/stats
```

### No Código

```php
// Controller
class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::forCurrentCompany()
            ->with(['company', 'client'])
            ->paginate();
            
        return response()->json($vehicles);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required',
            'model' => 'required',
            // ... outros campos
        ]);
        
        // Adicionar company_id automaticamente
        $validated['company_id'] = app('current_company_id');
        
        $vehicle = Vehicle::create($validated);
        
        return response()->json($vehicle, 201);
    }
}
```

## 🔧 Migração do Sistema Antigo

### 1. **Executar Migration**
```bash
php artisan tenant:migrate 1000
```

### 2. **Atualizar Código**
- Substituir `Office` por `Company`
- Substituir `office_id` por `company_id`
- Usar `BelongsToCompany` trait nos modelos
- Atualizar controllers e rotas

### 3. **Testar**
```bash
# Verificar se a migration funcionou
php artisan tenant:test 1000

# Testar sistema de empresas
php artisan test:company-system 1000

# Criar empresas de exemplo
php artisan tenant:seed 1000 --class=CompanySeeder
```

## 📈 Benefícios

### ✅ **Vantagens**
- **Semântica Correta**: "Company" faz mais sentido que "Office"
- **Flexibilidade**: Fácil adicionar/remover empresas
- **Isolamento**: Dados separados por empresa
- **Performance**: Índices otimizados (`tenant_id`, `company_id`)
- **Escalabilidade**: Suporte a muitas empresas por tenant

### 🎯 **Casos de Uso**
- **Grupo Empresarial**: Uma holding com várias empresas
- **Franquias**: Franqueador com múltiplas unidades
- **Filiais**: Empresa principal com filiais regionais
- **Departamentos**: Divisões internas como empresas separadas

## 🚨 Considerações Importantes

### **Segurança**
- Usuários só veem dados da sua empresa (por padrão)
- Middleware garante isolamento entre empresas
- Validação de permissões por empresa

### **Performance**
- Índices compostos em (`tenant_id`, `company_id`)
- Queries otimizadas com scopes
- Lazy loading de relacionamentos

### **Manutenção**
- Backup por tenant (inclui todas as empresas)
- Migrations automáticas para novos tenants
- Seeders para dados iniciais

## 🔍 Troubleshooting

### **Erro: Company não encontrada**
```bash
# Verificar se a empresa existe
php artisan tinker
>>> Company::where('tenant_id', '1000')->get();
```

### **Erro: Foreign key constraint**
```bash
# Verificar relacionamentos
php artisan tinker
>>> Vehicle::whereNull('company_id')->count();
```

### **Erro: Middleware não funciona**
```php
// Verificar se o middleware está registrado
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        // ...
        \App\Http\Middleware\CompanyMiddleware::class,
    ],
];
```

## 📚 Próximos Passos

1. **Implementar permissões por empresa**
2. **Criar relatórios cross-empresas**
3. **Adicionar configurações específicas por empresa**
4. **Implementar transferência de dados entre empresas**
5. **Criar dashboard multi-empresa**
