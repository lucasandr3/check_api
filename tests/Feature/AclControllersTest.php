<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\Tenant;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Feature\WithTenant;
use PHPUnit\Framework\Attributes\Test;

class AclControllersTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected $user;
    protected $adminRole;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar o tenant
        $this->setUpTenant();
        
        // Executar seeders para criar dados do ACL
        $this->artisan('db:seed', ['--class' => 'AclSeeder']);
        
        // Obter role de admin
        $this->adminRole = Role::where('name', 'admin')->first();
        
        // Criar usuário admin
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => Office::factory()->create([
                'tenant_id' => $this->tenant->id
            ])->id,
            'email' => 'admin@example.com',
            'password' => bcrypt('123456')
        ]);
        
        // Associar role de admin ao usuário
        if ($this->adminRole) {
            $this->user->roles()->attach($this->adminRole->id);
        }
        
        // Fazer login e obter token
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => '123456'
        ]);
        
        $this->token = $response->json('data.access_token');
    }

    #[Test]
    public function test_menu_controller_index_returns_user_menus()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/menus');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'order',
                    'label',
                    'icon',
                    'url',
                    'identificador',
                    'submenus',
                    'rotas_ativas'
                ]
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function test_menu_controller_all_returns_all_menus_for_admin()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/menus/all');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'order',
                    'label',
                    'icon',
                    'url',
                    'identificador',
                    'submenus',
                    'rotas_ativas'
                ]
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
    }

    #[Test]
    public function test_menu_controller_store_creates_new_menu()
    {
        $menuData = [
            'order' => 10,
            'secao' => 'TESTE',
            'label' => 'Menu Teste',
            'icone' => 'test',
            'url' => '/test',
            'identificador' => 'test',
            'rotas_ativas' => 'test',
            'roles' => [$this->adminRole->id]
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/menus', $menuData);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Menu criado com sucesso', $response->json('message'));
        
        // Verificar se o menu foi criado no banco
        $this->assertDatabaseHas('menus', [
            'identificador' => 'test',
            'label' => 'Menu Teste'
        ]);
    }

    #[Test]
    public function test_role_controller_index_returns_roles()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/roles');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'display_name',
                    'description',
                    'permissions'
                ]
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function test_role_controller_store_creates_new_role()
    {
        $roleData = [
            'name' => 'test_role',
            'display_name' => 'Role Teste',
            'description' => 'Role para testes',
            'permissions' => [1, 2, 3] // IDs de permissões existentes
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/roles', $roleData);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Role criado com sucesso', $response->json('message'));
        
        // Verificar se o role foi criado no banco
        $this->assertDatabaseHas('roles', [
            'name' => 'test_role',
            'display_name' => 'Role Teste'
        ]);
    }

    #[Test]
    public function test_permission_controller_index_returns_permissions()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/permissions');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'display_name',
                    'description'
                ]
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function test_permission_controller_store_creates_new_permission()
    {
        $permissionData = [
            'name' => 'test.permission',
            'display_name' => 'Permissão Teste',
            'description' => 'Permissão para testes'
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/permissions', $permissionData);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Permissão criada com sucesso', $response->json('message'));
        
        // Verificar se a permissão foi criada no banco
        $this->assertDatabaseHas('permissions', [
            'name' => 'test.permission',
            'display_name' => 'Permissão Teste'
        ]);
    }

    #[Test]
    public function test_user_controller_index_returns_users_for_admin()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/users');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'tenant_id',
                    'office_id',
                    'roles'
                ]
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function test_user_controller_store_creates_new_user()
    {
        $userData = [
            'name' => 'Usuário Teste',
            'email' => 'testuser@example.com',
            'password' => '123456',
            'tenant_id' => $this->tenant->id,
            'office_id' => Office::factory()->create([
                'tenant_id' => $this->tenant->id
            ])->id,
            'roles' => [$this->adminRole->id]
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/users', $userData);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Usuário criado com sucesso', $response->json('message'));
        
        // Verificar se o usuário foi criado no banco
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'name' => 'Usuário Teste'
        ]);
    }

    #[Test]
    public function test_user_controller_permissions_returns_user_permissions()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/users/{$this->user->id}/permissions");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'display_name',
                    'description'
                ]
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function test_unauthorized_access_is_denied()
    {
        // Criar usuário sem permissões de admin
        $regularUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => Office::factory()->create([
                'tenant_id' => $this->tenant->id
            ])->id,
            'email' => 'regular@example.com',
            'password' => bcrypt('123456')
        ]);
        
        // Fazer login com usuário regular
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'regular@example.com',
            'password' => '123456'
        ]);
        
        $regularToken = $loginResponse->json('data.access_token');
        
        // Tentar acessar endpoint que requer permissão de admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $regularToken
        ])->getJson('/api/users');
        
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Acesso negado. Permissão insuficiente.'
        ]);
    }

    #[Test]
    public function test_menu_controller_update_updates_existing_menu()
    {
        // Criar um menu para atualizar
        $menu = Menu::create([
            'order' => 99,
            'secao' => 'TESTE',
            'label' => 'Menu Original',
            'icone' => 'original',
            'url' => '/original',
            'identificador' => 'original',
            'rotas_ativas' => 'original'
        ]);
        
        $updateData = [
            'label' => 'Menu Atualizado',
            'icone' => 'updated',
            'roles' => [$this->adminRole->id]
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/menus/{$menu->id}", $updateData);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Menu atualizado com sucesso', $response->json('message'));
        
        // Verificar se o menu foi atualizado no banco
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'label' => 'Menu Atualizado',
            'icone' => 'updated'
        ]);
    }

    #[Test]
    public function test_menu_controller_destroy_deletes_menu()
    {
        // Criar um menu para deletar
        $menu = Menu::create([
            'order' => 99,
            'secao' => 'TESTE',
            'label' => 'Menu para Deletar',
            'icone' => 'delete',
            'url' => '/delete',
            'identificador' => 'delete',
            'rotas_ativas' => 'delete'
        ]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/menus/{$menu->id}");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Menu excluído com sucesso', $response->json('message'));
        
        // Verificar se o menu foi deletado do banco
        $this->assertDatabaseMissing('menus', [
            'id' => $menu->id
        ]);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }
}
