<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Feature\WithTenant;

abstract class AclTestCase extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected $user;
    protected $adminRole;
    protected $operatorRole;
    protected $superAdminRole;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar o tenant
        $this->setUpTenant();
        
        // Executar seeders para criar dados do ACL
        $this->artisan('db:seed', ['--class' => 'AclSeeder']);
        
        // Obter roles
        $this->adminRole = Role::where('name', 'admin')->first();
        $this->operatorRole = Role::where('name', 'operator')->first();
        $this->superAdminRole = Role::where('name', 'super_admin')->first();
        
        // Criar usuário padrão (admin)
        $this->user = $this->createUserWithRole('admin@example.com', $this->adminRole);
        
        // Fazer login e obter token
        $this->token = $this->loginUser($this->user);
    }

    /**
     * Criar usuário com role específico
     */
    protected function createUserWithRole(string $email, ?Role $role = null): User
    {
        $user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => Office::factory()->create([
                'tenant_id' => $this->tenant->id
            ])->id,
            'email' => $email,
            'password' => bcrypt('123456')
        ]);
        
        if ($role) {
            $user->roles()->attach($role->id);
        }
        
        return $user;
    }

    /**
     * Fazer login com usuário e retornar token
     */
    protected function loginUser(User $user): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '123456'
        ]);
        
        return $response->json('data.access_token');
    }

    /**
     * Criar usuário operador para testes
     */
    protected function createOperatorUser(): User
    {
        return $this->createUserWithRole('operator@example.com', $this->operatorRole);
    }

    /**
     * Criar usuário super admin para testes
     */
    protected function createSuperAdminUser(): User
    {
        return $this->createUserWithRole('superadmin@example.com', $this->superAdminRole);
    }

    /**
     * Criar usuário sem roles para testes
     */
    protected function createUserWithoutRoles(): User
    {
        return $this->createUserWithRole('noroles@example.com');
    }

    /**
     * Verificar se usuário tem permissão específica
     */
    protected function assertUserHasPermission(User $user, string $permission): void
    {
        $this->assertTrue($user->hasPermission($permission), 
            "Usuário {$user->email} deveria ter permissão '{$permission}'");
    }

    /**
     * Verificar se usuário não tem permissão específica
     */
    protected function assertUserDoesNotHavePermission(User $user, string $permission): void
    {
        $this->assertFalse($user->hasPermission($permission), 
            "Usuário {$user->email} não deveria ter permissão '{$permission}'");
    }

    /**
     * Verificar se usuário tem role específico
     */
    protected function assertUserHasRole(User $user, string $roleName): void
    {
        $hasRole = $user->roles->contains('name', $roleName);
        $this->assertTrue($hasRole, 
            "Usuário {$user->email} deveria ter role '{$roleName}'");
    }

    /**
     * Verificar se usuário não tem role específico
     */
    protected function assertUserDoesNotHaveRole(User $user, string $roleName): void
    {
        $hasRole = $user->roles->contains('name', $roleName);
        $this->assertFalse($hasRole, 
            "Usuário {$user->email} não deveria ter role '{$roleName}'");
    }

    /**
     * Verificar se resposta contém menus
     */
    protected function assertResponseContainsMenus($response): void
    {
        $response->assertJsonStructure([
            'data' => [
                'menus' => [
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
            ]
        ]);
        
        $menus = $response->json('data.menus');
        $this->assertIsArray($menus);
        $this->assertGreaterThan(0, count($menus));
    }

    /**
     * Verificar se resposta contém estrutura de sucesso
     */
    protected function assertSuccessResponse($response): void
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
        
        $this->assertTrue($response->json('success'));
    }

    /**
     * Verificar se resposta contém estrutura de erro
     */
    protected function assertErrorResponse($response, int $statusCode = 400): void
    {
        $response->assertStatus($statusCode);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);
        
        $this->assertFalse($response->json('success'));
    }

    /**
     * Verificar se resposta contém estrutura de acesso negado
     */
    protected function assertAccessDeniedResponse($response): void
    {
        $this->assertErrorResponse($response, 403);
        $this->assertStringContainsString('Acesso negado', $response->json('message'));
    }

    /**
     * Verificar se resposta contém estrutura de não autorizado
     */
    protected function assertUnauthorizedResponse($response): void
    {
        $this->assertErrorResponse($response, 401);
    }

    /**
     * Verificar se resposta contém estrutura de validação
     */
    protected function assertValidationResponse($response): void
    {
        $this->assertErrorResponse($response, 422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors'
        ]);
    }

    /**
     * Fazer requisição autenticada
     */
    protected function authenticatedRequest(string $method, string $url, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->$method($url, $data);
    }

    /**
     * Fazer requisição GET autenticada
     */
    protected function authenticatedGet(string $url): \Illuminate\Testing\TestResponse
    {
        return $this->authenticatedRequest('get', $url);
    }

    /**
     * Fazer requisição POST autenticada
     */
    protected function authenticatedPost(string $url, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->authenticatedRequest('post', $url, $data);
    }

    /**
     * Fazer requisição PUT autenticada
     */
    protected function authenticatedPut(string $url, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->authenticatedRequest('put', $url, $data);
    }

    /**
     * Fazer requisição DELETE autenticada
     */
    protected function authenticatedDelete(string $url): \Illuminate\Testing\TestResponse
    {
        return $this->authenticatedRequest('delete', $url);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }
}
