<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Database\Seeders\TenantAclSeeder;
use Database\Seeders\TestDataSeeder;

class SetupProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup completo do projeto - cria tenants, usuários, empresas e dados de teste';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando setup completo do projeto...');
        
        // 1. Executar migrations centrais
        $this->info('📊 Executando migrations centrais...');
        $this->call('migrate');
        
        // 2. Criar tenants
        $this->createTenants();
        
        // 3. Executar migrations dos tenants
        $this->runTenantMigrations();
        
        // 4. Executar ACL uma vez no schema público
        $this->call(TenantAclSeeder::class);
        
        // 5. Criar dados para cada tenant
        $this->createTenantData();
        
        // 6. Criar dados de teste (veículos, equipamentos, etc.)
        $this->info('📊 Criando dados de teste...');
        $this->call(TestDataSeeder::class);
        
        $this->info('✅ Setup completo finalizado!');
        $this->showCredentials();
    }
    
    /**
     * Criar tenants
     */
    private function createTenants(): void
    {
        $this->info('🏢 Criando tenants...');
        
        $tenants = [
            [
                'id' => '1000',
                'name' => 'Empresa ABC',
                'cnpj' => '12.345.678/0001-90',
                'phone' => '(11) 99999-9999',
                'email' => 'contato@empresaabc.com',
                'address' => 'Rua das Empresas, 123 - São Paulo/SP'
            ],
            [
                'id' => '1001',
                'name' => 'Empresa XYZ',
                'cnpj' => '98.765.432/0001-10',
                'phone' => '(21) 88888-8888',
                'email' => 'contato@empresaxyz.com',
                'address' => 'Av. das Indústrias, 456 - Rio de Janeiro/RJ'
            ]
        ];
        
        foreach ($tenants as $tenantData) {
            $tenant = Tenant::updateOrCreate(
                ['id' => $tenantData['id']],
                [
                    'schema_name' => 'tenant_' . $tenantData['id'],
                    'database_name' => 'check_api',
                    'status' => 'active',
                    'data' => [
                        'name' => $tenantData['name'],
                        'company_info' => [
                            'cnpj' => $tenantData['cnpj'],
                            'phone' => $tenantData['phone'],
                            'email' => $tenantData['email'],
                            'address' => $tenantData['address']
                        ]
                    ],
                    'settings' => [
                        'timezone' => 'America/Sao_Paulo',
                        'currency' => 'BRL',
                        'language' => 'pt_BR'
                    ]
                ]
            );
            
            $this->line("  ✅ Tenant criado: {$tenant->data['name']} ({$tenant->id})");
        }
    }
    
    /**
     * Executar migrations dos tenants
     */
    private function runTenantMigrations(): void
    {
        $this->info('📊 Executando migrations dos tenants...');
        
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->call('tenant:migrate', ['tenant' => $tenant->id]);
            $this->line("  ✅ Migrations executadas para tenant {$tenant->id}");
        }
    }
    
    /**
     * Criar dados para cada tenant
     */
    private function createTenantData(): void
    {
        $this->info('👥 Criando dados dos tenants...');
        
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->createDataForTenant($tenant);
        }
    }
    
    /**
     * Criar dados para um tenant específico
     */
    private function createDataForTenant(Tenant $tenant): void
    {
        $this->info("🔍 Processando tenant: {$tenant->data['name']} ({$tenant->id})");
        
        // Ativar tenant
        $tenant->makeCurrent();
        
        // 1. Criar empresas
        $companies = $this->createCompanies($tenant);
        
        // 2. Criar usuários
        $this->createUsers($tenant, $companies);
        
        // Resetar tenant
        Tenant::forgetCurrent();
    }
    
    /**
     * Criar empresas para o tenant
     */
    private function createCompanies(Tenant $tenant): array
    {
        // Tenant 1001 tem apenas empresa principal, sem filiais
        if ($tenant->id === '1001') {
            $companies = [
                [
                    'name' => 'Empresa Principal',
                    'address' => 'Rua Principal, 123 - Centro',
                    'phone' => '(11) 99999-8888',
                    'email' => 'contato@empresaxyz.com',
                    'cnpj' => '98.765.432/0001-10',
                ]
            ];
        } else {
            // Tenant 1000 tem empresa principal + filiais
            $companies = [
                [
                    'name' => 'Empresa Principal',
                    'address' => 'Rua Principal, 123 - Centro',
                    'phone' => '(11) 99999-9999',
                    'email' => 'contato@empresaprincipal.com',
                    'cnpj' => '12.345.678/0001-90',
                ],
                [
                    'name' => 'Filial São Paulo',
                    'address' => 'Av. Paulista, 1000 - Bela Vista',
                    'phone' => '(11) 88888-8888',
                    'email' => 'sp@empresaprincipal.com',
                    'cnpj' => '12.345.678/0002-71',
                ],
                [
                    'name' => 'Filial Rio de Janeiro',
                    'address' => 'Rua da Carioca, 456 - Centro',
                    'phone' => '(21) 77777-7777',
                    'email' => 'rj@empresaprincipal.com',
                    'cnpj' => '12.345.678/0003-52',
                ],
            ];
        }
        
        $createdCompanies = [];
        foreach ($companies as $companyData) {
            $company = Company::create([
                'tenant_id' => $tenant->id,
                'name' => $companyData['name'],
                'address' => $companyData['address'],
                'phone' => $companyData['phone'],
                'email' => $companyData['email'],
                'cnpj' => $companyData['cnpj'],
            ]);
            
            $createdCompanies[] = $company;
            $this->line("    🏢 Empresa criada: {$company->name}");
        }
        
        return $createdCompanies;
    }
    
    /**
     * Criar usuários para o tenant
     */
    private function createUsers(Tenant $tenant, array $companies): void
    {
        $firstCompany = $companies[0];
        
        // Resetar tenant para criar usuários no schema público
        Tenant::forgetCurrent();
        
        $users = [
            [
                'name' => 'Admin ' . $tenant->data['name'],
                'email' => 'admin@tenant' . $tenant->id . '.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'name' => 'Operador ' . $tenant->data['name'],
                'email' => 'operador@tenant' . $tenant->id . '.com',
                'password' => Hash::make('password'),
                'role' => 'operator'
            ],
            [
                'name' => 'Gerente ' . $tenant->data['name'],
                'email' => 'gerente@tenant' . $tenant->id . '.com',
                'password' => Hash::make('password'),
                'role' => 'manager'
            ]
        ];
        
        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'tenant_id' => $tenant->id,
                    'company_id' => $firstCompany->id,
                    'email_verified_at' => now(),
                ])
            );
            
            // Associar role
            $this->assignRoleToUser($user, $roleName);
            
            $this->line("    👤 Usuário criado: {$user->email} ({$roleName})");
        }
        
        // Reativar tenant para continuar o processo
        $tenant->makeCurrent();
    }
    
    /**
     * Atribuir role ao usuário
     */
    private function assignRoleToUser(User $user, string $roleName): void
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('roles')) {
                return;
            }
            
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                return;
            }
            
            if (!DB::getSchemaBuilder()->hasTable('role_user')) {
                return;
            }
            
            $user->roles()->sync([$role->id]);
            
        } catch (\Exception $e) {
            // Ignorar erros de roles
        }
    }
    
    /**
     * Mostrar credenciais de login
     */
    private function showCredentials(): void
    {
        $this->info('');
        $this->info('🔑 Credenciais de Login:');
        $this->info('');
        
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->info("📋 TENANT {$tenant->id} ({$tenant->data['name']}):");
            $this->info("  👤 admin@tenant{$tenant->id}.com / password (Admin)");
            $this->info("  👤 operador@tenant{$tenant->id}.com / password (Operador)");
            $this->info("  👤 gerente@tenant{$tenant->id}.com / password (Gerente)");
            $this->info('');
        }
        
        $this->info('🧪 Teste de Login:');
        $this->info('curl -X POST http://localhost:8000/api/auth/login \\');
        $this->info('  -H "Content-Type: application/json" \\');
        $this->info('  -d \'{"email":"admin@tenant1000.com","password":"password"}\'');
    }
}
