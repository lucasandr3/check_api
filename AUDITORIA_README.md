# Sistema de Auditoria - FixCar API

## 📋 Visão Geral

O sistema de auditoria implementa rastreamento completo de todas as operações realizadas na API, incluindo:

- **Operações CRUD** em modelos (Menu, User, Role, Permission, Service, Checklist, Office)
- **Eventos de autenticação** (login, logout, tentativas falhadas)
- **Acesso a rotas** específicas
- **Mudanças de dados** com valores antigos e novos
- **Metadados de segurança** (IP, User Agent, timestamps)

## 🏗️ Arquitetura

### Componentes Principais

1. **Modelo AuditLog** - Armazena todos os logs de auditoria
2. **Observers** - Capturam eventos dos modelos automaticamente
3. **Listeners** - Processam eventos de forma assíncrona
4. **Events** - Disparam eventos de auditoria
5. **Middleware** - Audita rotas específicas
6. **Controller** - API para consulta dos logs
7. **Commands** - Limpeza automática de logs antigos

### Fluxo de Auditoria

```
Modelo → Observer → Event → Listener → AuditLog (BD)
   ↓
Rota → Middleware → AuditLog (BD)
   ↓
Auth → Event → Listener → AuditLog (BD)
```

## 🚀 Instalação

### 1. Migration

```bash
php artisan migrate
```

### 2. Seeders

```bash
php artisan db:seed --class=AuditPermissionSeeder
```

### 3. Observers (já registrados automaticamente)

Os observers são registrados automaticamente no `EventServiceProvider`.

## 📊 Estrutura da Tabela

### audit_logs

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | ID único do log |
| `event_type` | string | Tipo do evento (created, updated, deleted, login, etc) |
| `auditable_type` | string | Classe do modelo auditado |
| `auditable_id` | bigint | ID do modelo auditado |
| `user_id` | bigint | ID do usuário que executou a ação |
| `user_email` | string | Email do usuário |
| `ip_address` | string | IP do usuário |
| `user_agent` | string | User Agent do navegador |
| `route_name` | string | Nome da rota acessada |
| `method` | string | Método HTTP |
| `description` | text | Descrição da ação |
| `old_values` | json | Valores antigos (para updates) |
| `new_values` | json | Valores novos |
| `changed_fields` | json | Campos que foram alterados |
| `metadata` | json | Metadados adicionais |
| `created_at` | timestamp | Data/hora do log |
| `updated_at` | timestamp | Data/hora da última atualização |

## 🔍 Uso da API

### Endpoints Disponíveis

#### 1. Listar Logs
```http
GET /api/audit/logs
```

**Parâmetros de Filtro:**
- `page` - Número da página
- `per_page` - Itens por página (padrão: 15)
- `event_type` - Tipo de evento
- `model` - Classe do modelo
- `user_id` - ID do usuário
- `start_date` - Data inicial (YYYY-MM-DD)
- `end_date` - Data final (YYYY-MM-DD)

**Exemplo:**
```bash
curl -X GET "http://localhost:8000/api/audit/logs?event_type=created&model=App\Models\User&per_page=20" \
  -H "Authorization: Bearer {token}"
```

#### 2. Visualizar Log Específico
```http
GET /api/audit/logs/{id}
```

#### 3. Estatísticas
```http
GET /api/audit/statistics
```

Retorna:
- Total de logs
- Distribuição por tipo de evento
- Distribuição por modelo
- Atividade recente
- Top usuários

#### 4. Modelos Auditados
```http
GET /api/audit/models
```

#### 5. Exportar CSV
```http
GET /api/audit/export?start_date=2024-01-01&end_date=2024-12-31
```

## 🛠️ Comandos Artisan

### Limpar Logs Antigos

```bash
# Ver o que seria removido (dry-run)
php artisan audit:clean --days=90 --dry-run

# Remover logs com mais de 90 dias
php artisan audit:clean --days=90

# Forçar remoção sem confirmação
php artisan audit:clean --days=90 --force

# Manter logs por 30 dias
php artisan audit:clean --days=30
```

## 🔐 Segurança

### Campos Sensíveis Removidos

- **Senhas** e confirmações
- **Tokens** de API
- **Headers** de autorização
- **Cookies** de sessão

### Níveis de Segurança

- **Alto**: Tentativas de login falhadas
- **Médio**: Login/logout, exclusões
- **Baixo**: Criações, atualizações

## 📈 Monitoramento

### Métricas Importantes

1. **Volume de Logs** - Monitorar crescimento
2. **Tipos de Eventos** - Identificar padrões
3. **Usuários Ativos** - Rastrear atividade
4. **Modelos Mais Auditados** - Foco de mudanças
5. **Tentativas de Login Falhadas** - Segurança

### Alertas Recomendados

- Logs de exclusão em massa
- Múltiplas tentativas de login falhadas
- Acesso a rotas sensíveis
- Mudanças em permissões/roles

## 🔧 Configuração

### Variáveis de Ambiente

```env
# Habilitar auditoria detalhada
AUDIT_DETAILED=true

# Manter logs por X dias
AUDIT_RETENTION_DAYS=90

# Tamanho máximo de valores JSON
AUDIT_MAX_JSON_SIZE=10000
```

### Personalização

#### Adicionar Auditoria a Novos Modelos

1. **Criar Observer:**
```php
php artisan make:observer NovoModeloObserver
```

2. **Implementar métodos:**
```php
public function created(NovoModelo $model): void
{
    // Lógica de auditoria
}
```

3. **Registrar no EventServiceProvider:**
```php
\App\Models\NovoModelo::observe(\App\Observers\NovoModeloObserver::class);
```

#### Auditoria Customizada

```php
use App\Events\AuditEvent;

// Em qualquer lugar do código
AuditEvent::created(
    auditableType: get_class($model),
    auditableId: $model->id,
    newValues: $model->getAttributes()
)->dispatch();
```

## 🚨 Troubleshooting

### Problemas Comuns

#### 1. Logs Não Estão Sendo Criados

**Verificar:**
- Observers registrados no EventServiceProvider
- Permissões de usuário
- Logs de erro em `storage/logs/laravel.log`

#### 2. Performance Lenta

**Soluções:**
- Executar `php artisan audit:clean` regularmente
- Adicionar índices no banco
- Configurar filas para processamento assíncrono

#### 3. Erro de Memória

**Causas:**
- Logs muito grandes
- Muitos campos JSON
- Falta de limpeza automática

### Logs de Debug

```php
// Habilitar logs detalhados
\Log::info('Auditoria', [
    'model' => get_class($model),
    'event' => 'created',
    'data' => $model->getAttributes()
]);
```

## 📚 Exemplos de Uso

### 1. Auditoria de Usuário

```php
// No UserObserver
public function updated(User $user): void
{
    // Campos sensíveis são automaticamente removidos
    AuditEvent::updated(
        auditableType: get_class($user),
        auditableId: $user->id,
        oldValues: $user->getOriginal(),
        newValues: $user->getAttributes(),
        changedFields: $user->getDirty()
    )->dispatch()->afterResponse();
}
```

### 2. Auditoria Customizada

```php
// Em um controller
public function bulkAction(Request $request)
{
    $result = $this->service->bulkAction($request->ids);
    
    // Auditoria customizada
    AuditEvent::created(
        auditableType: 'BulkAction',
        auditableId: 0,
        newValues: [
            'action' => $request->action,
            'ids_count' => count($request->ids),
            'result' => $result
        ]
    )->dispatch()->afterResponse();
    
    return response()->json(['success' => true]);
}
```

### 3. Filtros Avançados

```php
// Buscar logs de um usuário específico em um período
$logs = AuditLog::where('user_id', $userId)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->where('event_type', 'updated')
    ->with('user')
    ->get();
```

## 🔄 Manutenção

### Tarefas Agendadas

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Limpar logs antigos diariamente às 2h
    $schedule->command('audit:clean --days=90 --force')
             ->dailyAt('02:00');
}
```

### Backup dos Logs

```bash
# Exportar logs antes da limpeza
php artisan audit:export --start_date=2024-01-01 --end_date=2024-12-31 > audit_backup.csv

# Limpar logs antigos
php artisan audit:clean --days=90 --force
```

## 📊 Dashboard de Auditoria

### Métricas Principais

1. **Atividade por Período**
   - Gráfico de linha com volume de logs
   - Comparação com períodos anteriores

2. **Distribuição por Tipo**
   - Gráfico de pizza com tipos de evento
   - Filtros por data

3. **Top Usuários**
   - Ranking de usuários mais ativos
   - Detalhes de ações por usuário

4. **Alertas de Segurança**
   - Tentativas de login falhadas
   - Acessos a rotas sensíveis
   - Mudanças em permissões

## 🔗 Integrações

### Webhooks

```php
// Enviar logs críticos para sistemas externos
if ($this->isCriticalEvent($event)) {
    Http::post($webhookUrl, [
        'event' => $event->eventType,
        'user' => $event->userEmail,
        'details' => $event->description,
        'timestamp' => now()->toISOString()
    ]);
}
```

### Notificações

```php
// Notificar administradores sobre eventos críticos
if ($this->isCriticalEvent($event)) {
    Notification::route('mail', 'admin@fixcar.com')
        ->notify(new CriticalAuditEventNotification($event));
}
```

## 📝 Changelog

### v1.0.0 (2025-01-15)
- ✅ Sistema base de auditoria
- ✅ Observers para modelos principais
- ✅ API de consulta de logs
- ✅ Comando de limpeza automática
- ✅ Middleware para auditoria de rotas
- ✅ Eventos de autenticação
- ✅ Exportação CSV
- ✅ Estatísticas e métricas

## 🤝 Contribuição

Para contribuir com o sistema de auditoria:

1. Siga os padrões de código existentes
2. Adicione testes para novas funcionalidades
3. Documente mudanças na API
4. Mantenha compatibilidade com versões anteriores

## 📞 Suporte

Para dúvidas ou problemas:

- **Issues**: GitHub Issues
- **Documentação**: Este arquivo
- **Logs**: `storage/logs/laravel.log`
- **Comandos**: `php artisan list | grep audit`

---

**Sistema de Auditoria FixCar API** - Rastreamento completo e seguro de todas as operações do sistema.
