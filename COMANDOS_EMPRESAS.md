# 🎯 Comandos Corretos para o Sistema de Empresas

## 📋 Comandos Disponíveis

### **1. Executar Migration**
```bash
php artisan tenant:migrate 1000
```

### **2. Testar Sistema de Empresas**
```bash
php artisan test:company-system 1000
```

### **3. Executar Seeder de Empresas**
```bash
php artisan tenant:seed 1000 --class=CompanySeeder
```

### **4. Executar Todos os Seeders**
```bash
php artisan tenant:seed 1000
```

### **5. Executar para Todos os Tenants**
```bash
php artisan tenant:seed 1000 --all
```

## 🚀 Sequência Recomendada

```bash
# 1. Executar a migration para renomear offices → companies
php artisan tenant:migrate 1000

# 2. Testar se o sistema está funcionando
php artisan test:company-system 1000

# 3. Criar empresas de exemplo
php artisan tenant:seed 1000 --class=CompanySeeder
```

## ✅ O que foi Criado

1. **Migration**: Renomeia `offices` → `companies` e `office_id` → `company_id`
2. **Modelo Company**: Com todos os relacionamentos
3. **Trait BelongsToCompany**: Para facilitar uso nos modelos
4. **Controller CompanyController**: CRUD completo
5. **Middleware CompanyMiddleware**: Para identificar empresa atual
6. **Seeder CompanySeeder**: Dados de exemplo
7. **Factory CompanyFactory**: Para testes
8. **Comando tenant:seed**: Para executar seeders em tenants
9. **Comando test:company-system**: Para testar o sistema

## 🎉 Pronto para Usar!

Agora você pode:
- ✅ Criar múltiplas empresas dentro de cada tenant
- ✅ Separar dados por empresa usando `company_id`
- ✅ Usar headers, query params ou URL params para identificar empresa
- ✅ Ter isolamento completo de dados entre empresas
- ✅ Escalar facilmente para muitas empresas por tenant
