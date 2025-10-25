<?php

namespace Tests\Feature;

use App\Events\ServiceStatusUpdated;
use App\Models\Client;
use App\Models\Office;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected $office;
    protected $user;
    protected $client;
    protected $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar o tenant
        $this->setUpTenant();
        
        // Criar dados de teste usando factories
        $this->office = Office::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Office',
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => 'test@office.com'
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'name' => 'Test User',
            'email' => 'test@user.com',
            'password' => bcrypt('password'),
            'role' => 'mechanic'
        ]);

        $this->client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'phone' => '987654321',
            'cpf_cnpj' => '12345678901'
        ]);

        $this->vehicle = Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'client_id' => $this->client->id,
            'brand' => 'Test Brand',
            'model' => 'Test Model',
            'year' => 2020,
            'color' => 'Red',
            'plate' => 'ABC1234'
        ]);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }

    #[Test]
    public function it_can_create_a_service()
    {
        $serviceData = [
            'vehicle_id' => $this->vehicle->id,
            'type' => 'Manutenção Preventiva',
            'description' => 'Troca de óleo e filtros',
            'estimated_cost' => 150.00,
            'start_date' => '2024-01-15',
            'observations' => 'Serviço de rotina',
            'user_id' => $this->user->id,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/services', $serviceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'service' => [
                    'id',
                    'type',
                    'description',
                    'status',
                    'estimated_cost',
                    'start_date',
                    'observations',
                ]
            ]);

        $this->assertDatabaseHas('services', [
            'vehicle_id' => $this->vehicle->id,
            'office_id' => $this->vehicle->office_id,
            'type' => 'Manutenção Preventiva',
            'description' => 'Troca de óleo e filtros',
        ]);
    }

    #[Test]
    public function it_can_update_service_status_and_trigger_notification()
    {
        Event::fake();

        $service = Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->user->id,
            'type' => 'Manutenção',
            'description' => 'Test service',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/services/{$service->id}", [
                'status' => 'in_progress'
            ]);

        $response->assertStatus(200);

        Event::assertDispatched(ServiceStatusUpdated::class, function ($event) use ($service) {
            return $event->service->id === $service->id &&
                   $event->oldStatus === 'pending' &&
                   $event->newStatus === 'in_progress';
        });
    }

    #[Test]
    public function it_can_list_services()
    {
        // Criar alguns serviços de teste
        Service::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'description',
                        'status',
                    ]
                ],
                'pagination'
            ]);
    }

    #[Test]
    public function it_can_filter_services_by_status()
    {
        // Criar serviços com diferentes status
        Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/services/status/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'description',
                        'status',
                    ]
                ],
                'pagination'
            ]);

        // Verificar que só retorna serviços com status 'pending'
        $response->assertJson([
            'data' => [
                [
                    'status' => 'pending'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_service()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/services', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vehicle_id', 'type', 'description']);
    }

    #[Test]
    public function it_can_delete_a_service()
    {
        $service = Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}
