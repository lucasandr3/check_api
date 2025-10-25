<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Feature\WithTenant;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected $user;

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
    }

    #[Test]
    public function test_user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => '123456'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'tenant_id',
                    'office_id'
                ],
                'office' => [
                    'id',
                    'name',
                    'email',
                    'cnpj',
                    'phone',
                    'address'
                ],
                'permissions',
                'menus' => [
                    '*' => [
                        'secao',
                        'menus' => [
                            '*' => [
                                'id',
                                'order',
                                'secao',
                                'label',
                                'icon',
                                'url',
                                'identificador',
                                'submenus',
                                'rotas_ativas'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Login realizado com sucesso', $response->json('message'));
        
        // Verificar se os dados da oficina foram retornados
        $office = $response->json('data.office');
        $this->assertNotNull($office);
        $this->assertArrayHasKey('id', $office);
        $this->assertArrayHasKey('name', $office);
        $this->assertArrayHasKey('email', $office);
        $this->assertArrayHasKey('cnpj', $office);
        $this->assertArrayHasKey('phone', $office);
        $this->assertArrayHasKey('address', $office);
        
        // Verificar se os menus foram retornados agrupados por seção
        $menus = $response->json('data.menus');
        $this->assertIsArray($menus);
        $this->assertGreaterThan(0, count($menus));
        
        // Verificar se cada seção tem a estrutura correta
        foreach ($menus as $secao) {
            $this->assertArrayHasKey('secao', $secao);
            $this->assertArrayHasKey('menus', $secao);
            $this->assertIsArray($secao['menus']);
            $this->assertGreaterThan(0, count($secao['menus']));
            
            // Verificar se os menus estão ordenados por ordem
            $orders = collect($secao['menus'])->pluck('order')->toArray();
            $sortedOrders = $orders;
            sort($sortedOrders);
            $this->assertEquals($sortedOrders, $orders, 'Menus devem estar ordenados por ordem');
        }
    }

    #[Test]
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Credenciais inválidas'
        ]);
    }

    #[Test]
    public function test_login_validation_requires_email_and_password()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }
}
