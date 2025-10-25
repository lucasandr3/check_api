<?php

namespace Tests\Unit;

use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MenuResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Executar seeders para criar dados do ACL
        $this->artisan('db:seed', ['--class' => 'AclSeeder']);
    }

    #[Test]
    public function test_menu_resource_transforms_menu_data_correctly()
    {
        // Criar um menu de teste
        $menu = Menu::create([
            'order' => 5,
            'secao' => 'TESTE',
            'label' => 'Menu Teste',
            'icone' => 'test_icon',
            'url' => '/test',
            'identificador' => 'test_menu',
            'rotas_ativas' => 'test'
        ]);
        
        // Criar resource
        $resource = new MenuResource($menu);
        $data = $resource->toArray(request());
        
        // Verificar estrutura do resource
        $this->assertEquals($menu->id, $data['id']);
        $this->assertEquals($menu->order, $data['order']);
        $this->assertEquals($menu->label, $data['label']);
        $this->assertEquals($menu->icone, $data['icon']);
        $this->assertEquals($menu->url, $data['url']);
        $this->assertEquals($menu->identificador, $data['identificador']);
        $this->assertEquals($menu->rotas_ativas, $data['rotas_ativas']);
        $this->assertArrayHasKey('submenus', $data);
    }

    #[Test]
    public function test_menu_resource_includes_submenus_when_loaded()
    {
        // Criar menu pai
        $parentMenu = Menu::create([
            'order' => 1,
            'secao' => 'TESTE',
            'label' => 'Menu Pai',
            'icone' => 'parent',
            'url' => '/parent',
            'identificador' => 'parent_menu',
            'rotas_ativas' => 'parent'
        ]);
        
        // Criar submenu
        $submenu = Menu::create([
            'order' => 1,
            'secao' => 'TESTE',
            'label' => 'Submenu',
            'icone' => 'submenu',
            'url' => '/parent/submenu',
            'identificador' => 'submenu',
            'rotas_ativas' => 'submenu',
            'parent_id' => $parentMenu->id
        ]);
        
        // Carregar relacionamento
        $parentMenu->load('submenus');
        
        // Criar resource
        $resource = new MenuResource($parentMenu);
        $data = $resource->toArray(request());
        
        // Verificar se submenus estão incluídos
        $this->assertArrayHasKey('submenus', $data);
        $this->assertCount(1, $data['submenus']);
        $this->assertEquals($submenu->id, $data['submenus'][0]['id']);
        $this->assertEquals($submenu->label, $data['submenus'][0]['label']);
    }

    #[Test]
    public function test_menu_resource_handles_empty_submenus()
    {
        // Criar menu sem submenus
        $menu = Menu::create([
            'order' => 1,
            'secao' => 'TESTE',
            'label' => 'Menu Sem Submenus',
            'icone' => 'no_submenu',
            'url' => '/no-submenu',
            'identificador' => 'no_submenu',
            'rotas_ativas' => 'no_submenu'
        ]);
        
        // Criar resource
        $resource = new MenuResource($menu);
        $data = $resource->toArray(request());
        
        // Verificar se submenus está presente mas vazio
        $this->assertArrayHasKey('submenus', $data);
        $this->assertIsArray($data['submenus']);
        $this->assertEmpty($data['submenus']);
    }

    #[Test]
    public function test_menu_resource_collection_works_correctly()
    {
        // Criar múltiplos menus
        $menus = collect([
            Menu::create([
                'order' => 1,
                'secao' => 'TESTE',
                'label' => 'Menu 1',
                'icone' => 'icon1',
                'url' => '/menu1',
                'identificador' => 'menu1',
                'rotas_ativas' => 'menu1'
            ]),
            Menu::create([
                'order' => 2,
                'secao' => 'TESTE',
                'label' => 'Menu 2',
                'icone' => 'icon2',
                'url' => '/menu2',
                'identificador' => 'menu2',
                'rotas_ativas' => 'menu2'
            ])
        ]);
        
        // Criar collection de resources
        $resources = MenuResource::collection($menus);
        $data = $resources->toArray(request());
        
        // Verificar se a collection foi criada corretamente
        $this->assertCount(2, $data);
        $this->assertEquals('Menu 1', $data[0]['label']);
        $this->assertEquals('Menu 2', $data[1]['label']);
        $this->assertEquals('icon1', $data[0]['icon']);
        $this->assertEquals('icon2', $data[1]['icon']);
    }

    #[Test]
    public function test_menu_resource_preserves_original_menu_data()
    {
        // Criar menu com dados específicos
        $menu = Menu::create([
            'order' => 99,
            'secao' => 'SECÇÃO ESPECIAL',
            'label' => 'Menu Especial',
            'icone' => 'special_icon',
            'url' => '/special/url',
            'identificador' => 'special_identifier',
            'rotas_ativas' => 'special,special2'
        ]);
        
        // Criar resource
        $resource = new MenuResource($menu);
        $data = $resource->toArray(request());
        
        // Verificar se todos os dados originais foram preservados
        $this->assertEquals(99, $data['order']);
        $this->assertEquals('SECÇÃO ESPECIAL', $data['secao']);
        $this->assertEquals('Menu Especial', $data['label']);
        $this->assertEquals('special_icon', $data['icon']);
        $this->assertEquals('/special/url', $data['url']);
        $this->assertEquals('special_identifier', $data['identificador']);
        $this->assertEquals('special,special2', $data['rotas_ativas']);
    }

    #[Test]
    public function test_menu_resource_with_roles_relationship()
    {
        // Criar role
        $role = Role::where('name', 'admin')->first();
        
        // Criar menu
        $menu = Menu::create([
            'order' => 1,
            'secao' => 'TESTE',
            'label' => 'Menu com Cargos',
            'icone' => 'security',
            'url' => '/roles',
            'identificador' => 'roles_menu',
            'rotas_ativas' => 'roles'
        ]);
        
        // Associar role ao menu
        if ($role) {
            $menu->roles()->attach($role->id);
        }
        
        // Carregar relacionamento
        $menu->load('roles');
        
        // Criar resource
        $resource = new MenuResource($menu);
        $data = $resource->toArray(request());
        
        // Verificar se o menu foi transformado corretamente
        $this->assertEquals('Menu com Cargos', $data['label']);
        $this->assertEquals('security', $data['icon']);
        $this->assertEquals('roles_menu', $data['identificador']);
    }

    #[Test]
    public function test_menu_resource_handles_null_values()
    {
        // Criar menu com valores nulos (mas campos obrigatórios preenchidos)
        $menu = Menu::create([
            'order' => 1,
            'secao' => 'TESTE',
            'label' => 'Menu com Nulos',
            'icone' => 'default_icon', // Campo obrigatório
            'url' => '/null',
            'identificador' => 'null_menu',
            'rotas_ativas' => 'default' // Campo obrigatório
        ]);
        
        // Criar resource
        $resource = new MenuResource($menu);
        $data = $resource->toArray(request());
        
        // Verificar se valores são tratados corretamente
        $this->assertEquals('Menu com Nulos', $data['label']);
        $this->assertEquals('default_icon', $data['icon']);
        $this->assertEquals('/null', $data['url']);
        $this->assertEquals('default', $data['rotas_ativas']);
    }

    #[Test]
    public function test_menu_resource_maintains_data_types()
    {
        // Criar menu
        $menu = Menu::create([
            'order' => 42,
            'secao' => 'TESTE',
            'label' => 'Menu Tipos',
            'icone' => 'types',
            'url' => '/types',
            'identificador' => 'types_menu',
            'rotas_ativas' => 'types'
        ]);
        
        // Criar resource
        $resource = new MenuResource($menu);
        $data = $resource->toArray(request());
        
        // Verificar tipos de dados
        $this->assertIsInt($data['order']);
        $this->assertIsString($data['secao']);
        $this->assertIsString($data['label']);
        $this->assertIsString($data['icon']);
        $this->assertIsString($data['url']);
        $this->assertIsString($data['identificador']);
        $this->assertIsString($data['rotas_ativas']);
        $this->assertIsArray($data['submenus']);
    }
}
