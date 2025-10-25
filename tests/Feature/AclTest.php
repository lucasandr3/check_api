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

class AclTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected $user;
    protected $adminRole;
    protected $operatorRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar o tenant
        $this->setUpTenant();
        
        // Executar seeders para criar dados do ACL
        $this->artisan('db:seed', ['--class' => 'AclSeeder']);
        
        // Obter roles criados pelo seeder
        $this->adminRole = Role::where('name', 'admin')->first();
        $this->operatorRole = Role::where('name', 'operator')->first();
        
        // Criar usuários de teste
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
    }

    #[Test]
    public function test_acl_seeder_creates_required_data()
    {
        // Verificar se as permissões foram criadas
        $this->assertGreaterThan(0, Permission::count());
        
        // Verificar se os roles foram criados
        $this->assertGreaterThan(0, Role::count());
        
        // Verificar se os menus foram criados
        $this->assertGreaterThan(0, Menu::count());
        
        // Verificar roles específicos
        $this->assertNotNull(Role::where('name', 'super_admin')->first());
        $this->assertNotNull(Role::where('name', 'admin')->first());
        $this->assertNotNull(Role::where('name', 'operator')->first());
    }

    #[Test]
    public function test_user_has_roles_and_permissions()
    {
        $this->assertTrue($this->user->roles->count() > 0);
        
        $role = $this->user->roles->first();
        $this->assertTrue($role->permissions->count() > 0);
        
        // Verificar se o usuário tem permissões específicas
        $this->assertTrue($this->user->hasPermission('admin.users'));
        $this->assertTrue($this->user->hasPermission('services.view'));
    }

    #[Test]
    public function test_user_can_access_menus_based_on_roles()
    {
        $menus = $this->user->getAccessibleMenus();
        
        $this->assertGreaterThan(0, $menus->count());
        
        // Verificar se o usuário tem acesso ao menu de usuários (admin)
        $usersMenu = $menus->where('identificador', 'users')->first();
        $this->assertNotNull($usersMenu);
        
        // Verificar se o usuário tem acesso ao menu de serviços (todos)
        $servicesMenu = $menus->where('identificador', 'services')->first();
        $this->assertNotNull($servicesMenu);
    }

    #[Test]
    public function test_permission_checking_methods()
    {
        // Testar hasPermission
        $this->assertTrue($this->user->hasPermission('admin.users'));
        $this->assertFalse($this->user->hasPermission('non.existent.permission'));
        
        // Testar hasAnyPermission
        $this->assertTrue($this->user->hasAnyPermission(['admin.users', 'admin.roles']));
        $this->assertFalse($this->user->hasAnyPermission(['non.existent.permission']));
        
        // Testar hasAllPermissions
        $this->assertTrue($this->user->hasAllPermissions(['admin.users', 'services.view']));
        $this->assertFalse($this->user->hasAllPermissions(['admin.users', 'non.existent.permission']));
    }

    #[Test]
    public function test_role_permissions_relationship()
    {
        $role = $this->adminRole;
        
        $this->assertGreaterThan(0, $role->permissions->count());
        
        // Verificar se o role tem permissões específicas
        $permissionNames = $role->permissions->pluck('name')->toArray();
        $this->assertContains('admin.users', $permissionNames);
        $this->assertContains('services.view', $permissionNames);
    }

    #[Test]
    public function test_menu_roles_relationship()
    {
        $menu = Menu::where('identificador', 'users')->first();
        
        if ($menu) {
            $this->assertGreaterThan(0, $menu->roles->count());
            
            // Verificar se o menu está associado ao role de admin
            $roleNames = $menu->roles->pluck('name')->toArray();
            $this->assertContains('admin', $roleNames);
        }
    }

    #[Test]
    public function test_menu_ordering_and_sections()
    {
        $menus = Menu::ordered()->get();
        
        // Verificar se os menus estão ordenados
        $previousOrder = 0;
        foreach ($menus as $menu) {
            $this->assertGreaterThanOrEqual($previousOrder, $menu->order);
            $previousOrder = $menu->order;
        }
        
        // Verificar seções
        $sections = $menus->pluck('secao')->unique()->toArray();
        $this->assertContains('PRINCIPAL', $sections);
        $this->assertContains('ADMINISTRAÇÃO', $sections);
    }

    #[Test]
    public function test_user_without_roles_has_no_menus()
    {
        // Criar usuário sem roles
        $userWithoutRoles = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => Office::factory()->create([
                'tenant_id' => $this->tenant->id
            ])->id,
            'email' => 'noroles@example.com',
            'password' => bcrypt('123456')
        ]);
        
        $menus = $userWithoutRoles->getAccessibleMenus();
        $this->assertEquals(0, $menus->count());
    }

    #[Test]
    public function test_different_roles_have_different_menus()
    {
        // Criar usuário operador
        $operatorUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'office_id' => Office::factory()->create([
                'tenant_id' => $this->tenant->id
            ])->id,
            'email' => 'operator@example.com',
            'password' => bcrypt('123456')
        ]);
        
        if ($this->operatorRole) {
            $operatorUser->roles()->attach($this->operatorRole->id);
        }
        
        $adminMenus = $this->user->getAccessibleMenus();
        $operatorMenus = $operatorUser->getAccessibleMenus();
        
        // O admin deve ter mais menus que o operador
        $this->assertGreaterThan($operatorMenus->count(), $adminMenus->count());
        
        // O operador não deve ter acesso ao menu de usuários
        $operatorUsersMenu = $operatorMenus->where('identificador', 'users')->first();
        $this->assertNull($operatorUsersMenu);
    }

    #[Test]
    public function test_menu_hierarchy_with_submenus()
    {
        // Criar um submenu para teste
        $parentMenu = Menu::where('identificador', 'services')->first();
        
        if ($parentMenu) {
            $submenu = Menu::create([
                'order' => 1,
                'secao' => 'PRINCIPAL',
                'label' => 'Submenu Teste',
                'icone' => 'submenu',
                'url' => '/services/submenu',
                'identificador' => 'services.submenu',
                'rotas_ativas' => 'services.submenu',
                'parent_id' => $parentMenu->id
            ]);
            
            // Associar o submenu aos mesmos roles do menu pai
            $submenu->roles()->attach($parentMenu->roles->pluck('id')->toArray());
            
            // Verificar se o submenu aparece para o usuário
            $userMenus = $this->user->getAccessibleMenus();
            $servicesMenu = $userMenus->where('identificador', 'services')->first();
            
            if ($servicesMenu && $servicesMenu->submenus) {
                $this->assertGreaterThan(0, $servicesMenu->submenus->count());
                $this->assertNotNull($servicesMenu->submenus->where('identificador', 'services.submenu')->first());
            }
        }
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }
}
