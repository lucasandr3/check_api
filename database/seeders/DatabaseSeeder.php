<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Office;
use App\Models\User;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\Checklist;
use App\Models\ChecklistPhoto;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar tenant de teste
        $tenant = Tenant::create([
            'id' => '1000',
            'schema_name' => 'tenant_1000',
            'database_name' => 'check_api',
            'status' => 'active',
            'data' => [
                'name' => 'FixCar Teste',
                'company_info' => [
                    'cnpj' => '12.345.678/0001-90',
                    'phone' => '(11) 99999-9999',
                    'email' => 'contato@fixcarteste.com',
                    'address' => 'Rua das Oficinas, 123 - São Paulo/SP'
                ]
            ],
            'settings' => [
                'timezone' => 'America/Sao_Paulo',
                'currency' => 'BRL',
                'language' => 'pt_BR'
            ]
        ]);

        // Criar 2 oficinas
        $offices = [];
        for ($i = 1; $i <= 2; $i++) {
            $offices[] = Office::create([
                'tenant_id' => $tenant->id,
                'name' => "Oficina FixCar {$i}",
                'address' => "Rua da Oficina {$i}, " . ($i * 100) . " - São Paulo/SP",
                'phone' => "(11) 9999" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'email' => "oficina{$i}@fixcarteste.com",
                'cnpj' => fake()->numerify('##.###.###/####-##')
            ]);
        }

        // Executar o seeder de ACL primeiro
        $this->call(AclSeeder::class);
        
        // Executar o seeder de permissões de auditoria
        $this->call(AuditPermissionSeeder::class);

        // Criar usuários (mecânicos)
        $users = [];
        foreach ($offices as $office) {
            for ($j = 1; $j <= 3; $j++) {
                $users[] = User::create([
                    'tenant_id' => $tenant->id,
                    'office_id' => $office->id,
                    'name' => "Mecânico " . ($office->id * 10 + $j),
                    'email' => "mecanico" . ($office->id * 10 + $j) . "@fixcarteste.com",
                    'password' => bcrypt('password')
                ]);
            }
        }

        // Associar roles aos usuários
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $operatorRole = \App\Models\Role::where('name', 'operator')->first();
        
        if ($adminRole && $operatorRole) {
            foreach ($users as $index => $user) {
                // Primeiro usuário será admin, os outros serão operadores
                if ($index === 0) {
                    $user->roles()->attach($adminRole->id);
                } else {
                    $user->roles()->attach($operatorRole->id);
                }
            }
        } else {
            $this->command->error('❌ Roles não encontrados! Verifique se o AclSeeder foi executado corretamente.');
        }

        // Criar clientes
        $clients = [];
        foreach ($offices as $office) {
            for ($k = 1; $k <= 5; $k++) {
                $clients[] = Client::create([
                    'tenant_id' => $tenant->id,
                    'office_id' => $office->id,
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'cpf_cnpj' => fake()->numerify('###.###.###-##'),
                    'address' => fake()->address()
                ]);
            }
        }

        // Criar veículos
        $vehicles = [];
        foreach ($clients as $client) {
            $vehicles[] = Vehicle::create([
                'tenant_id' => $tenant->id,
                'office_id' => $client->office_id,
                'client_id' => $client->id,
                'brand' => fake()->randomElement(['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Volkswagen']),
                'model' => fake()->randomElement(['Corolla', 'Civic', 'Focus', 'Onix', 'Gol']),
                'year' => fake()->numberBetween(2015, 2024),
                'color' => fake()->randomElement(['Branco', 'Preto', 'Prata', 'Vermelho', 'Azul']),
                'plate' => fake()->regexify('[A-Z]{3}[0-9]{4}'),
                'chassis' => fake()->regexify('[A-Z0-9]{17}'),
                'observations' => fake()->optional()->sentence()
            ]);
        }

        // Criar 5 serviços
        $services = [];
        for ($s = 1; $s <= 5; $s++) {
            $vehicle = $vehicles[array_rand($vehicles)];
            $user = $users[array_rand($users)];
            
            $services[] = Service::create([
                'tenant_id' => $tenant->id,
                'office_id' => $vehicle->office_id,
                'vehicle_id' => $vehicle->id,
                'user_id' => $user->id,
                'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
                'type' => fake()->randomElement(['Manutenção', 'Revisão', 'Troca de óleo', 'Alinhamento', 'Freios']),
                'description' => fake()->paragraph(),
                'estimated_cost' => fake()->randomFloat(2, 100, 800),
                'final_cost' => fake()->optional()->randomFloat(2, 100, 800),
                'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
                'end_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
                'observations' => fake()->optional()->sentence()
            ]);
        }

        // Criar checklists para alguns serviços
        foreach ($services as $service) {
            if ($service->status !== 'pending') {
                $checklist = Checklist::create([
                    'tenant_id' => $tenant->id,
                    'office_id' => $service->office_id,
                    'service_id' => $service->id,
                    'user_id' => $service->user_id,
                    'status' => 'completed',
                    'items' => [
                        'Verificação de óleo do motor',
                        'Verificação de nível de água',
                        'Verificação de pneus',
                        'Verificação de freios',
                        'Verificação de suspensão',
                        'Verificação de iluminação'
                    ],
                    'observations' => fake()->optional()->sentence()
                ]);

                // Criar algumas fotos para o checklist
                for ($p = 1; $p <= 3; $p++) {
                    ChecklistPhoto::create([
                        'tenant_id' => $tenant->id,
                        'checklist_id' => $checklist->id,
                        'filename' => "checklist_{$checklist->id}_foto_{$p}.jpg",
                        'path' => "checklists/checklist_{$checklist->id}_foto_{$p}.jpg",
                        'mime_type' => 'image/jpeg',
                        'size' => fake()->numberBetween(500000, 2000000),
                        'description' => fake()->randomElement(['Foto do motor', 'Foto dos pneus', 'Foto dos freios'])
                    ]);
                }
            }
        }

        // Criar alguns equipamentos de exemplo
        $equipments = [];
        foreach ($clients as $client) {
            $equipments[] = \App\Models\Equipment::create([
                'tenant_id' => $tenant->id,
                'office_id' => $office->id,
                'client_id' => $client->id,
                'name' => 'Gerador Diesel ' . $client->name,
                'type' => 'generator',
                'brand' => 'Caterpillar',
                'model' => 'C4.4',
                'serial_number' => 'CAT' . rand(100000, 999999),
                'acquisition_date' => now()->subMonths(rand(1, 24)),
                'warranty_expiration' => now()->addMonths(rand(6, 36)),
                'status' => 'active',
                'location' => 'Galpão Principal',
                'observations' => 'Equipamento em bom estado'
            ]);
        }

        $this->command->info('✅ Tenant de teste criado com sucesso!');
        $this->command->info("📊 Resumo dos dados criados:");
        $this->command->info("- 1 Tenant: " . $tenant->data['name']);
        $this->command->info("- " . count($offices) . " Oficinas");
        $this->command->info("- " . count($users) . " Usuários");
        $this->command->info("- " . count($clients) . " Clientes");
        $this->command->info("- " . count($vehicles) . " Veículos");
        $this->command->info("- " . count($services) . " Serviços");
        $this->command->info("- " . count(Checklist::where('tenant_id', $tenant->id)->get()) . " Checklists");
        $this->command->info("- " . count($equipments) . " Equipamentos");
    }
}
