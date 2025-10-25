<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Office;
use App\Models\Service;
use App\Models\Checklist;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\WithTenant;
use PHPUnit\Framework\Attributes\Test;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar o tenant
        $this->setUpTenant();
        
        // Executar seeders para criar dados do ACL
        $this->artisan('db:seed', ['--class' => 'AclSeeder']);
        
        // Criar um usuário de teste
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => Office::factory()->create([
                'tenant_id' => $this->tenant->id
            ])->id,
            'email' => 'test@example.com',
            'password' => bcrypt('123456')
        ]);
        
        // Associar role de admin ao usuário
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $this->user->roles()->attach($adminRole->id);
        }

        // Fazer login para obter token
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => '123456'
        ]);
        
        $this->token = $response->json('data.access_token');
    }

    #[Test]
    public function test_can_get_dashboard_stats()
    {
        // Criar alguns dados de teste
        $this->createTestData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/dashboard/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_services',
                'pending_services',
                'in_progress_services',
                'completed_services',
                'total_checklists',
                'completed_checklists',
                'pending_checklists',
                'total_clients',
                'total_vehicles',
                'total_users',
                'total_quotes',
                'monthly_revenue',
                'monthly_services',
                'services_by_status',
                'services_by_type',
                'recent_activities'
            ]
        ]);

        $this->assertTrue($response->json('success'));
        
        $data = $response->json('data');
        $this->assertEquals(3, $data['total_services']);
        $this->assertEquals(1, $data['pending_services']);
        $this->assertEquals(1, $data['in_progress_services']);
        $this->assertEquals(1, $data['completed_services']);
        $this->assertEquals(2, $data['total_checklists']);
        $this->assertEquals(1, $data['total_clients']);
        $this->assertEquals(1, $data['total_vehicles']);
    }

    #[Test]
    public function test_can_get_chart_data()
    {
        $this->createTestData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/dashboard/chart-data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'services_timeline',
                'revenue_timeline',
                'checklists_completion',
                'top_service_types'
            ]
        ]);

        $this->assertTrue($response->json('success'));
    }

    #[Test]
    public function test_can_get_chart_data_with_period_parameter()
    {
        $this->createTestData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/dashboard/chart-data?period=7d');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    #[Test]
    public function test_can_get_quick_actions()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/dashboard/quick-actions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'label',
                    'icon',
                    'url',
                    'color'
                ]
            ]
        ]);

        $this->assertTrue($response->json('success'));
        
        $actions = $response->json('data');
        $this->assertGreaterThan(0, count($actions));
    }

    #[Test]
    public function test_unauthorized_access_is_denied()
    {
        // Criar usuário sem permissão de dashboard
        $userWithoutPermission = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->user->office_id,
            'email' => 'nopermission@example.com',
            'password' => bcrypt('123456')
        ]);

        $operatorRole = \App\Models\Role::where('name', 'operator')->first();
        if ($operatorRole) {
            $userWithoutPermission->roles()->attach($operatorRole->id);
        }

        // Fazer login com usuário sem permissão
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'nopermission@example.com',
            'password' => '123456'
        ]);
        
        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/dashboard/stats');

        $response->assertStatus(403);
    }

    #[Test]
    public function test_unauthenticated_access_is_denied()
    {
        // Limpar qualquer autenticação anterior
        $this->withoutMiddleware();
        
        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(401);
    }

    /**
     * Criar dados de teste para o dashboard
     */
    private function createTestData(): void
    {
        $officeId = $this->user->office_id;

        // Criar cliente
        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId
        ]);

        // Criar veículo
        $vehicle = Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId,
            'client_id' => $client->id
        ]);

        // Criar serviços
        Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId,
            'vehicle_id' => $vehicle->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'type' => 'Manutenção',
            'estimated_cost' => 150.00
        ]);

        Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId,
            'vehicle_id' => $vehicle->id,
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'type' => 'Revisão',
            'estimated_cost' => 200.00
        ]);

        Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId,
            'vehicle_id' => $vehicle->id,
            'user_id' => $this->user->id,
            'status' => 'completed',
            'type' => 'Troca de óleo',
            'estimated_cost' => 80.00
        ]);

        // Criar checklists
        Checklist::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId,
            'service_id' => 1,
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        Checklist::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId,
            'service_id' => 2,
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        // Criar cotação
        Quote::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $officeId,
            'service_id' => 1
        ]);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }
}
