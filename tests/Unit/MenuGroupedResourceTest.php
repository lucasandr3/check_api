<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Resources\MenuGroupedResource;
use App\Models\Menu;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\WithTenant;

class MenuGroupedResourceTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_menu_grouped_resource_groups_menus_by_section()
    {
        // Criar menus de teste com diferentes seções
        $menus = collect([
            Menu::create([
                'order' => 2,
                'secao' => 'PRINCIPAL',
                'label' => 'Serviços',
                'icone' => 'build',
                'url' => '/services',
                'identificador' => 'services',
                'rotas_ativas' => 'services'
            ]),
            Menu::create([
                'order' => 1,
                'secao' => 'PRINCIPAL',
                'label' => 'Dashboard',
                'icone' => 'dashboard',
                'url' => '/home',
                'identificador' => 'home',
                'rotas_ativas' => 'home'
            ]),
            Menu::create([
                'order' => 1,
                'secao' => 'ADMINISTRAÇÃO',
                'label' => 'Usuários',
                'icone' => 'person',
                'url' => '/users',
                'identificador' => 'users',
                'rotas_ativas' => 'users'
            ]),
            Menu::create([
                'order' => 2,
                'secao' => 'ADMINISTRAÇÃO',
                'label' => 'Cargos',
                'icone' => 'security',
                'url' => '/roles',
                'identificador' => 'roles',
                'rotas_ativas' => 'roles'
            ])
        ]);

        $resource = new MenuGroupedResource($menus);
        $result = $resource->toArray(request());

        // Verificar se há duas seções
        $this->assertCount(2, $result);

        // Verificar seção PRINCIPAL
        $principalSection = collect($result)->firstWhere('secao', 'PRINCIPAL');
        $this->assertNotNull($principalSection);
        $this->assertCount(2, $principalSection['menus']);
        
        // Verificar se os menus da seção PRINCIPAL estão ordenados
        $this->assertEquals('Dashboard', $principalSection['menus'][0]['label']);
        $this->assertEquals('Serviços', $principalSection['menus'][1]['label']);

        // Verificar seção ADMINISTRAÇÃO
        $adminSection = collect($result)->firstWhere('secao', 'ADMINISTRAÇÃO');
        $this->assertNotNull($adminSection);
        $this->assertCount(2, $adminSection['menus']);
        
        // Verificar se os menus da seção ADMINISTRAÇÃO estão ordenados
        $this->assertEquals('Usuários', $adminSection['menus'][0]['label']);
        $this->assertEquals('Cargos', $adminSection['menus'][1]['label']);
    }

    public function test_menu_grouped_resource_orders_menus_by_order_field()
    {
        // Criar menus com ordem diferente da criação
        $menus = collect([
            Menu::create([
                'order' => 5,
                'secao' => 'TESTE',
                'label' => 'Último',
                'icone' => 'last',
                'url' => '/last',
                'identificador' => 'last',
                'rotas_ativas' => 'last'
            ]),
            Menu::create([
                'order' => 1,
                'secao' => 'TESTE',
                'label' => 'Primeiro',
                'icone' => 'first',
                'url' => '/first',
                'identificador' => 'first',
                'rotas_ativas' => 'first'
            ]),
            Menu::create([
                'order' => 3,
                'secao' => 'TESTE',
                'label' => 'Terceiro',
                'icone' => 'third',
                'url' => '/third',
                'identificador' => 'third',
                'rotas_ativas' => 'third'
            ])
        ]);

        $resource = new MenuGroupedResource($menus);
        $result = $resource->toArray(request());

        $testSection = $result[0];
        $this->assertEquals('TESTE', $testSection['secao']);
        $this->assertCount(3, $testSection['menus']);

        // Verificar se estão ordenados por ordem
        $this->assertEquals('Primeiro', $testSection['menus'][0]['label']);
        $this->assertEquals('Terceiro', $testSection['menus'][1]['label']);
        $this->assertEquals('Último', $testSection['menus'][2]['label']);
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }
}
