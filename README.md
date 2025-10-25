# FixCar API - Sistema Multitenant para Oficinas Mecânicas

Sistema de API multitenant desenvolvido em Laravel para gerenciamento de oficinas mecânicas, com suporte a múltiplos tenants, cada um podendo ter várias oficinas.

## 🚀 Funcionalidades Principais

- **Multitenancy**: Cada tenant pode ter múltiplas oficinas
- **Autenticação JWT**: API segura com autenticação JWT
- **Gestão de Serviços**: Controle completo de serviços automotivos
- **Checklists**: Sistema de checklists com fotos e geração de PDF
- **Orçamentos**: Gestão de orçamentos para clientes
- **Webhooks**: Integração com Evolution API para notificações
- **Notificações WhatsApp**: Envio automático de mensagens via WhatsApp

## 📋 Pré-requisitos

- PHP 8.1+
- Composer
- MySQL 8.0+ ou PostgreSQL 12+
- Node.js 16+ (para compilação de assets)
- Git

## 🛠️ Instalação

### 1. Clone o repositório
```bash
git clone <repository-url>
cd fixcar_api
```

### 2. Instale as dependências PHP
```bash
composer install
```

### 3. Instale as dependências Node.js
```bash
npm install
```

### 4. Configure o ambiente
```bash
cp .env.example .env
```

### 5. Configure as variáveis de ambiente
Edite o arquivo `.env` com as seguintes configurações:

```env
# Configurações do Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fixcar_api
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# Configurações de Storage
STORAGE_DISK=local
FILESYSTEM_DISK=local

# Configurações JWT
JWT_SECRET=sua_chave_jwt_secreta
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Configurações da Evolution API
EVOLUTION_API_KEY=sua_chave_api_evolution
EVOLUTION_API_URL=https://sua-evolution-api.com

# Configurações de Tenancy
TENANCY_DATABASE_AUTO_CREATE=true
TENANCY_DATABASE_AUTO_UPDATE=true
TENANCY_DATABASE_AUTO_DELETE=true
```

### 6. Gere a chave da aplicação
```bash
php artisan key:generate
```

### 7. Gere a chave JWT
```bash
php artisan jwt:secret
```

### 8. Execute as migrações
```bash
php artisan migrate
```

### 9. Execute os seeders
```bash
php artisan db:seed
```

### 10. Configure o storage
```bash
php artisan storage:link
```

### 11. Compile os assets (opcional)
```bash
npm run build
```

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

- **tenants**: Informações dos tenants
- **offices**: Oficinas de cada tenant
- **users**: Usuários do sistema (mecânicos, gerentes)
- **clients**: Clientes das oficinas
- **vehicles**: Veículos dos clientes
- **services**: Serviços realizados
- **checklists**: Checklists dos serviços
- **checklist_photos**: Fotos dos checklists
- **quotes**: Orçamentos para clientes

### Relacionamentos

- Tenant → Offices (1:N)
- Office → Users, Clients, Vehicles, Services (1:N)
- Client → Vehicles (1:N)
- Vehicle → Services (1:N)
- Service → Checklists, Quotes (1:N)
- Checklist → Photos (1:N)

## 🔐 Autenticação JWT

### Endpoints de Autenticação

- `POST /api/auth/login` - Login do usuário
- `POST /api/auth/logout` - Logout do usuário
- `POST /api/auth/refresh` - Renovar token JWT
- `GET /api/auth/me` - Informações do usuário logado

### Exemplo de Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "mecanico11@fixcarteste.com",
    "password": "password"
  }'
```

## 📡 Webhooks

### Endpoint de Webhook
- `POST /api/webhook/evolution` - Recebe webhooks da Evolution API

### Exemplos de Payloads

#### Atualização de Serviço
```json
{
  "type": "service_update",
  "service_id": 1,
  "old_status": "pending",
  "new_status": "in_progress",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

#### Criação de Checklist
```json
{
  "type": "checklist_create",
  "service_id": 1,
  "user_id": 1,
  "items": [
    "Verificação de óleo",
    "Verificação de pneus",
    "Verificação de freios"
  ],
  "timestamp": "2024-01-15T10:30:00Z"
}
```

#### Upload de Foto
```json
{
  "type": "photo_upload",
  "checklist_id": 1,
  "filename": "motor_001.jpg",
  "description": "Foto do motor",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## 🚗 API de Serviços

### Endpoints Principais

- `GET /api/services` - Listar serviços
- `POST /api/services` - Criar serviço
- `GET /api/services/{id}` - Visualizar serviço
- `PUT /api/services/{id}` - Atualizar serviço
- `DELETE /api/services/{id}` - Excluir serviço
- `GET /api/services/status/{status}` - Filtrar por status

### Exemplo de Criação de Serviço
```bash
curl -X POST http://localhost:8000/api/services \
  -H "Authorization: Bearer {seu_token_jwt}" \
  -H "Content-Type: application/json" \
  -d '{
    "vehicle_id": 1,
    "type": "Manutenção",
    "description": "Troca de óleo e filtros",
    "estimated_cost": 150.00
  }'
```

## 📋 API de Checklists

### Endpoints Principais

- `GET /api/checklists` - Listar checklists
- `POST /api/checklists` - Criar checklist
- `GET /api/checklists/{id}` - Visualizar checklist
- `PUT /api/checklists/{id}` - Atualizar checklist
- `DELETE /api/checklists/{id}` - Excluir checklist
- `POST /api/checklists/{id}/photos` - Upload de fotos
- `GET /api/checklists/{id}/pdf` - Gerar PDF
- `GET /api/checklists/service/{service_id}` - Por serviço

### Upload de Fotos
```bash
curl -X POST http://localhost:8000/api/checklists/1/photos \
  -H "Authorization: Bearer {seu_token_jwt}" \
  -F "photos[]=@foto1.jpg" \
  -F "photos[]=@foto2.jpg" \
  -F "descriptions[]=Foto do motor" \
  -F "descriptions[]=Foto dos pneus"
```

## 🏢 API de Oficinas

### Endpoints Principais

- `GET /api/offices` - Listar oficinas
- `POST /api/offices` - Criar oficina
- `GET /api/offices/{id}` - Visualizar oficina
- `PUT /api/offices/{id}` - Atualizar oficina
- `DELETE /api/offices/{id}` - Excluir oficina

## 🔄 Eventos e Notificações

### Evento ServiceStatusUpdated
Quando o status de um serviço é alterado, o sistema automaticamente:

1. Dispara o evento `ServiceStatusUpdated`
2. Executa o listener `SendWhatsAppNotification`
3. Envia mensagem via WhatsApp para o cliente

### Exemplo de Atualização de Status
```bash
curl -X PUT http://localhost:8000/api/services/1 \
  -H "Authorization: Bearer {seu_token_jwt}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed"
  }'
```

## 🧪 Testes

### Executar Testes
```bash
# Executar todos os testes
php artisan test

# Executar testes específicos
php artisan test --filter=ServiceTest
php artisan test --filter=ChecklistTest
```

### Testes Disponíveis

- **ServiceTest**: Valida criação, atualização e exclusão de serviços
- **ChecklistTest**: Valida criação de checklists com fotos
- **WebhookTest**: Valida recebimento de webhooks
- **NotificationTest**: Valida disparo de notificações

## 📊 Dados de Teste

Após executar `php artisan db:seed`, você terá:

- 1 Tenant de teste: "FixCar Teste"
- 2 Oficinas
- 6 Usuários (3 por oficina)
- 10 Clientes (5 por oficina)
- 10 Veículos (1 por cliente)
- 5 Serviços
- Checklists com fotos
- Orçamentos

### Credenciais de Teste
- **Email**: mecanico11@fixcarteste.com
- **Senha**: password

## 🚀 Comandos Úteis

### Artisan Commands
```bash
# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Recriar banco de dados
php artisan migrate:fresh --seed

# Listar rotas
php artisan route:list

# Criar novo tenant
php artisan make:tenant "Nome da Empresa"

# Verificar status do sistema
php artisan about
```

### Composer Commands
```bash
# Atualizar dependências
composer update

# Otimizar autoloader
composer dump-autoload

# Verificar vulnerabilidades
composer audit
```

## 🔧 Configurações Adicionais

### Configuração de Tenancy
O sistema usa o pacote `stancl/tenancy` com as seguintes configurações:

- **Tenancy por domínio**: Cada tenant tem seu próprio subdomínio
- **Banco de dados por tenant**: Cada tenant pode ter seu próprio banco
- **Scoping automático**: Todas as consultas são automaticamente filtradas por tenant

### Configuração de Storage
- **Disco local**: Para desenvolvimento
- **Disco S3**: Para produção (configurável)
- **Upload de fotos**: Salvas em `storage/app/checklists/`

### Configuração de JWT
- **TTL**: 60 minutos
- **Refresh TTL**: 14 dias
- **Algoritmo**: HS256

## 🚨 Troubleshooting

### Problemas Comuns

1. **Erro de conexão com banco**
   - Verifique as credenciais no `.env`
   - Certifique-se de que o banco existe

2. **Erro de permissão de storage**
   - Execute `php artisan storage:link`
   - Verifique permissões da pasta `storage/`

3. **Erro JWT**
   - Execute `php artisan jwt:secret`
   - Verifique `JWT_SECRET` no `.env`

4. **Erro de tenancy**
   - Verifique se o middleware está registrado
   - Confirme configurações no `config/tenancy.php`

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📚 Documentação

### Documentação da API
- **Scramble**: [SCRAMBLE_README.md](SCRAMBLE_README.md) - Documentação completa do Scramble e sistema de permissões
- **Scramble Exemplos**: [SCRAMBLE_EXAMPLES.md](SCRAMBLE_EXAMPLES.md) - Exemplos práticos de anotações PHPDoc
- **Form Requests**: [FORM_REQUESTS_README.md](FORM_REQUESTS_README.md) - Documentação dos Form Requests implementados

### Documentação do Sistema
- **ACL**: [ACL_README.md](ACL_README.md) - Sistema de Controle de Acesso
- **Angular ACL**: [ANGULAR_ACL_README.md](ANGULAR_ACL_README.md) - Implementação no Frontend
- **Testes ACL**: [TESTES_ACL_README.md](TESTES_ACL_README.md) - Guia de testes

## 📞 Suporte

Para suporte e dúvidas:

- **Email**: suporte@fixcar.com
- **Documentação**: [docs.fixcar.com](https://docs.fixcar.com)
- **Issues**: [GitHub Issues](https://github.com/fixcar/api/issues)

## 🚀 Setup Rápido

Para configurar o projeto completo em ambiente local:

```bash
# Reset completo (se houver problemas)
php artisan reset:project

# OU setup normal
php artisan setup:project
```

Este comando cria:
- ✅ Tenants (1000 e 1001)
- ✅ Empresas para cada tenant
- ✅ Usuários com tenant_id e company_id corretos
- ✅ Permissões e roles
- ✅ Dados de teste

### 🔑 Credenciais de Login

Após o setup, você terá:

**Tenant 1000 (Empresa ABC):**
- `admin@tenant1000.com` / `password` (Admin)
- `operador@tenant1000.com` / `password` (Operador)
- `gerente@tenant1000.com` / `password` (Gerente)

**Tenant 1001 (Empresa XYZ):**
- `admin@tenant1001.com` / `password` (Admin)
- `operador@tenant1001.com` / `password` (Operador)
- `gerente@tenant1001.com` / `password` (Gerente)

### 🧪 Teste de Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@tenant1000.com","password":"password"}'
```

---

**Desenvolvido com ❤️ pela equipe FixCar**
