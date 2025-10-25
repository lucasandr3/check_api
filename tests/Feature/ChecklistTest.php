<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Checklist;
use App\Models\Service;
use App\Models\User;
use App\Models\Office;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\ChecklistPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\WithTenant;

class ChecklistTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected $office;
    protected $user;
    protected $service;
    protected $checklist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        
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
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'mechanic'
        ]);

        $client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '987654321'
        ]);

        $vehicle = Vehicle::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'client_id' => $client->id,
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate' => 'ABC1234'
        ]);

        $this->service = Service::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'vehicle_id' => $vehicle->id,
            'user_id' => $this->user->id,
            'type' => 'maintenance',
            'status' => 'in_progress',
            'description' => 'Test service'
        ]);

        $this->checklist = Checklist::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'service_id' => $this->service->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'items' => ['item1', 'item2'],
            'observations' => 'Test observations'
        ]);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }

    public function test_it_can_create_checklist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/checklists', [
                'office_id' => $this->office->id,
                'service_id' => $this->service->id,
                'user_id' => $this->user->id,
                'status' => 'pending',
                'items' => ['item1', 'item2', 'item3'],
                'observations' => 'New checklist'
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'items',
                'observations',
                'created_at',
                'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('checklists', [
            'office_id' => $this->office->id,
            'service_id' => $this->service->id,
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);
    }

    public function test_it_can_list_all_checklists()
    {
        Checklist::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'service_id' => $this->service->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/checklists');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'status',
                    'items',
                    'observations',
                    'created_at',
                    'updated_at'
                ]
            ],
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]
        ]);
    }

    public function test_it_can_show_checklist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/checklists/{$this->checklist->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $this->checklist->id,
                'status' => $this->checklist->status
            ]
        ]);
    }

    public function test_it_can_update_checklist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/checklists/{$this->checklist->id}", [
                'status' => 'completed',
                'observations' => 'Updated observations'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $this->checklist->id,
                'status' => 'completed',
                'observations' => 'Updated observations'
            ]
        ]);

        $this->assertDatabaseHas('checklists', [
            'id' => $this->checklist->id,
            'status' => 'completed',
            'observations' => 'Updated observations'
        ]);
    }

    public function test_it_can_delete_checklist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->deleteJson("/api/checklists/{$this->checklist->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('checklists', [
            'id' => $this->checklist->id
        ]);
    }

    public function test_it_can_upload_photos_to_checklist()
    {
        Storage::fake('public');

        $photos = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg')
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/checklists/{$this->checklist->id}/photos", [
                'photos' => $photos,
                'descriptions' => ['Photo 1', 'Photo 2']
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'photos' => [
                '*' => [
                    'id',
                    'filename',
                    'path',
                    'description'
                ]
            ]
        ]);

        // Verificar que as fotos foram criadas no banco
        $this->assertDatabaseCount('checklist_photos', 2);
        
        // Verificar que as fotos pertencem ao checklist correto
        $this->assertDatabaseHas('checklist_photos', [
            'checklist_id' => $this->checklist->id,
        ]);
    }

    public function test_it_can_generate_pdf_for_checklist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/checklists/{$this->checklist->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="checklist-' . $this->checklist->id . '.pdf"');
    }

    public function test_it_can_list_checklists_by_service()
    {
        Checklist::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => $this->office->id,
            'service_id' => $this->service->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/checklists/service/{$this->service->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'status',
                    'items',
                    'observations',
                    'created_at',
                    'updated_at'
                ]
            ],
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]
        ]);
    }
}
