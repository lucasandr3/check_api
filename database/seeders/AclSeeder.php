<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AclSeeder extends Seeder
{
    public function run(): void
    {
        // Criar permissões básicas para Sistema de Checklist de Frotas e Equipamentos
        $permissions = [
            // Permissões administrativas
            ['name' => 'admin.users', 'display_name' => 'Administrar Usuários', 'description' => 'Permissão para administrar usuários'],
            ['name' => 'admin.roles', 'display_name' => 'Administrar Roles', 'description' => 'Permissão para administrar roles'],
            ['name' => 'admin.permissions', 'display_name' => 'Administrar Permissões', 'description' => 'Permissão para administrar permissões'],
            ['name' => 'admin.menus', 'display_name' => 'Administrar Menus', 'description' => 'Permissão para administrar menus'],
            
            // === CORE DO SISTEMA: CHECKLISTS ===
            ['name' => 'checklists.view', 'display_name' => 'Visualizar Checklists', 'description' => 'Permissão para visualizar checklists de frotas e equipamentos'],
            ['name' => 'checklists.create', 'display_name' => 'Criar Checklists', 'description' => 'Permissão para criar checklists de frotas e equipamentos'],
            ['name' => 'checklists.edit', 'display_name' => 'Editar Checklists', 'description' => 'Permissão para editar checklists'],
            ['name' => 'checklists.delete', 'display_name' => 'Excluir Checklists', 'description' => 'Permissão para excluir checklists'],
            ['name' => 'checklists.upload_photos', 'display_name' => 'Upload de Fotos em Checklists', 'description' => 'Permissão para fazer upload de fotos em checklists'],
            ['name' => 'checklists.generate_pdf', 'display_name' => 'Gerar PDF de Checklists', 'description' => 'Permissão para gerar PDF de checklists'],
            ['name' => 'checklists.manage', 'display_name' => 'Gerenciar Checklists', 'description' => 'Permissão completa para gerenciar checklists'],
            
            // === TEMPLATES DE CHECKLIST ===
            ['name' => 'checklist_templates.view', 'display_name' => 'Visualizar Templates', 'description' => 'Permissão para visualizar templates de checklist'],
            ['name' => 'checklist_templates.create', 'display_name' => 'Criar Templates', 'description' => 'Permissão para criar templates de checklist'],
            ['name' => 'checklist_templates.edit', 'display_name' => 'Editar Templates', 'description' => 'Permissão para editar templates de checklist'],
            ['name' => 'checklist_templates.delete', 'display_name' => 'Excluir Templates', 'description' => 'Permissão para excluir templates de checklist'],
            ['name' => 'checklist_templates.manage', 'display_name' => 'Gerenciar Templates', 'description' => 'Permissão completa para gerenciar templates'],
            
            // === FROTAS (VEÍCULOS) ===
            ['name' => 'vehicles.view', 'display_name' => 'Visualizar Veículos', 'description' => 'Permissão para visualizar veículos da frota'],
            ['name' => 'vehicles.create', 'display_name' => 'Cadastrar Veículos', 'description' => 'Permissão para cadastrar novos veículos'],
            ['name' => 'vehicles.edit', 'display_name' => 'Editar Veículos', 'description' => 'Permissão para editar dados de veículos'],
            ['name' => 'vehicles.delete', 'display_name' => 'Excluir Veículos', 'description' => 'Permissão para excluir veículos'],
            ['name' => 'vehicles.manage', 'display_name' => 'Gerenciar Frota', 'description' => 'Permissão completa para gerenciar frota de veículos'],
            
            // === EQUIPAMENTOS ===
            ['name' => 'equipment.view', 'display_name' => 'Visualizar Equipamentos', 'description' => 'Permissão para visualizar equipamentos'],
            ['name' => 'equipment.create', 'display_name' => 'Cadastrar Equipamentos', 'description' => 'Permissão para cadastrar novos equipamentos'],
            ['name' => 'equipment.edit', 'display_name' => 'Editar Equipamentos', 'description' => 'Permissão para editar dados de equipamentos'],
            ['name' => 'equipment.delete', 'display_name' => 'Excluir Equipamentos', 'description' => 'Permissão para excluir equipamentos'],
            ['name' => 'equipment.manage', 'display_name' => 'Gerenciar Equipamentos', 'description' => 'Permissão completa para gerenciar equipamentos'],
            
            // === CONTROLE DE PNEUS ===
            ['name' => 'tires.view', 'display_name' => 'Visualizar Controle de Pneus', 'description' => 'Permissão para visualizar registros de pneus'],
            ['name' => 'tires.create', 'display_name' => 'Registrar Pneus', 'description' => 'Permissão para registrar instalação/troca de pneus'],
            ['name' => 'tires.edit', 'display_name' => 'Editar Registros de Pneus', 'description' => 'Permissão para editar registros de pneus'],
            ['name' => 'tires.delete', 'display_name' => 'Excluir Registros de Pneus', 'description' => 'Permissão para excluir registros de pneus'],
            ['name' => 'tires.reports', 'display_name' => 'Relatórios de Pneus', 'description' => 'Permissão para gerar relatórios de desgaste e vida útil'],
            ['name' => 'tires.manage', 'display_name' => 'Gerenciar Pneus', 'description' => 'Permissão completa para controle de pneus'],
            
            // === MANUTENÇÃO ===
            ['name' => 'maintenance.view', 'display_name' => 'Visualizar Manutenções', 'description' => 'Permissão para visualizar registros de manutenção'],
            ['name' => 'maintenance.create', 'display_name' => 'Registrar Manutenções', 'description' => 'Permissão para registrar manutenções'],
            ['name' => 'maintenance.edit', 'display_name' => 'Editar Manutenções', 'description' => 'Permissão para editar registros de manutenção'],
            ['name' => 'maintenance.delete', 'display_name' => 'Excluir Manutenções', 'description' => 'Permissão para excluir registros de manutenção'],
            ['name' => 'maintenance.schedule', 'display_name' => 'Agendar Manutenções', 'description' => 'Permissão para agendar manutenções preventivas'],
            ['name' => 'maintenance.manage', 'display_name' => 'Gerenciar Manutenções', 'description' => 'Permissão completa para gerenciar manutenções'],
            
            // === RELATÓRIOS ===
            ['name' => 'reports.view', 'display_name' => 'Visualizar Relatórios', 'description' => 'Permissão para visualizar relatórios do sistema'],
            ['name' => 'reports.export', 'display_name' => 'Exportar Relatórios', 'description' => 'Permissão para exportar relatórios em PDF/Excel'],
            ['name' => 'reports.generate', 'display_name' => 'Gerar Relatórios', 'description' => 'Permissão para gerar relatórios personalizados'],
            ['name' => 'reports.manage', 'display_name' => 'Gerenciar Relatórios', 'description' => 'Permissão completa para gerenciar relatórios'],
            
            // === DASHBOARD ===
            ['name' => 'dashboard.view', 'display_name' => 'Visualizar Dashboard', 'description' => 'Permissão para acessar dashboard com estatísticas'],
            ['name' => 'dashboard.analytics', 'display_name' => 'Analytics Avançado', 'description' => 'Permissão para visualizar analytics detalhados'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Criar roles focados em Sistema de Checklist de Frotas e Equipamentos
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrador',
                'description' => 'Acesso total ao sistema de checklist de frotas e equipamentos',
                'permissions' => Permission::all()->pluck('id')->toArray()
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Administrador do sistema de checklist',
                'permissions' => Permission::whereIn('name', [
                    'admin.users', 'admin.roles', 'admin.permissions', 'admin.menus',
                    'checklists.view', 'checklists.create', 'checklists.edit', 'checklists.delete', 'checklists.upload_photos', 'checklists.generate_pdf', 'checklists.manage',
                    'checklist_templates.view', 'checklist_templates.create', 'checklist_templates.edit', 'checklist_templates.delete', 'checklist_templates.manage',
                    'vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete', 'vehicles.manage',
                    'equipment.view', 'equipment.create', 'equipment.edit', 'equipment.delete', 'equipment.manage',
                    'tires.view', 'tires.create', 'tires.edit', 'tires.delete', 'tires.reports', 'tires.manage',
                    'maintenance.view', 'maintenance.create', 'maintenance.edit', 'maintenance.delete', 'maintenance.schedule', 'maintenance.manage',
                    'reports.view', 'reports.export', 'reports.generate', 'reports.manage',
                    'dashboard.view', 'dashboard.analytics'
                ])->pluck('id')->toArray()
            ],
            [
                'name' => 'supervisor',
                'display_name' => 'Supervisor de Frota',
                'description' => 'Supervisiona checklists e manutenção da frota',
                'permissions' => Permission::whereIn('name', [
                    'checklists.view', 'checklists.create', 'checklists.edit', 'checklists.upload_photos', 'checklists.generate_pdf',
                    'checklist_templates.view', 'checklist_templates.create', 'checklist_templates.edit',
                    'vehicles.view', 'vehicles.create', 'vehicles.edit',
                    'equipment.view', 'equipment.create', 'equipment.edit',
                    'tires.view', 'tires.create', 'tires.edit', 'tires.reports',
                    'maintenance.view', 'maintenance.create', 'maintenance.edit', 'maintenance.schedule',
                    'reports.view', 'reports.export', 'reports.generate',
                    'dashboard.view'
                ])->pluck('id')->toArray()
            ],
            [
                'name' => 'inspector',
                'display_name' => 'Inspetor de Checklist',
                'description' => 'Realiza checklists e inspeções de frotas e equipamentos',
                'permissions' => Permission::whereIn('name', [
                    'checklists.view', 'checklists.create', 'checklists.upload_photos',
                    'checklist_templates.view',
                    'vehicles.view',
                    'equipment.view',
                    'tires.view', 'tires.create',
                    'maintenance.view', 'maintenance.create',
                    'dashboard.view'
                ])->pluck('id')->toArray()
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Visualizador',
                'description' => 'Acesso apenas de visualização aos relatórios',
                'permissions' => Permission::whereIn('name', [
                    'checklists.view',
                    'checklist_templates.view',
                    'vehicles.view',
                    'equipment.view',
                    'tires.view',
                    'maintenance.view',
                    'reports.view',
                    'dashboard.view'
                ])->pluck('id')->toArray()
            ]
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
            $role->permissions()->sync($permissions);
        }

        // Criar menus focados em Sistema de Checklist de Frotas e Equipamentos
        $menus = [
            // === SEÇÃO PRINCIPAL ===
            [
                'order' => 1,
                'secao' => 'CHECKLIST & INSPEÇÃO',
                'label' => 'Dashboard',
                'icone' => 'dashboard',
                'url' => '/painel',
                'identificador' => 'dashboard',
                'rotas_ativas' => 'dashboard',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor', 'inspector', 'viewer']
            ],
            [
                'order' => 2,
                'secao' => 'CHECKLIST & INSPEÇÃO',
                'label' => 'Checklists',
                'icone' => 'fact_check',
                'url' => '/checklists',
                'identificador' => 'checklists',
                'rotas_ativas' => 'checklists',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor', 'inspector', 'viewer']
            ],
            [
                'order' => 3,
                'secao' => 'CHECKLIST & INSPEÇÃO',
                'label' => 'Templates de Checklist',
                'icone' => 'assignment',
                'url' => '/templates-checklist',
                'identificador' => 'checklist-templates',
                'rotas_ativas' => 'checklist-templates',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor']
            ],
            
            // === SEÇÃO FROTA ===
            [
                'order' => 4,
                'secao' => 'GESTÃO DE FROTA',
                'label' => 'Veículos',
                'icone' => 'directions_car',
                'url' => '/veiculos',
                'identificador' => 'vehicles',
                'rotas_ativas' => 'vehicles',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor', 'inspector', 'viewer']
            ],
            [
                'order' => 5,
                'secao' => 'GESTÃO DE FROTA',
                'label' => 'Equipamentos',
                'icone' => 'precision_manufacturing',
                'url' => '/equipamentos',
                'identificador' => 'equipment',
                'rotas_ativas' => 'equipment',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor', 'inspector', 'viewer']
            ],
            [
                'order' => 6,
                'secao' => 'GESTÃO DE FROTA',
                'label' => 'Controle de Pneus',
                'icone' => 'tire_repair',
                'url' => '/pneus',
                'identificador' => 'tires',
                'rotas_ativas' => 'tires',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor', 'inspector', 'viewer']
            ],
            
            // === SEÇÃO MANUTENÇÃO ===
            [
                'order' => 7,
                'secao' => 'MANUTENÇÃO',
                'label' => 'Registros de Manutenção',
                'icone' => 'build',
                'url' => '/manutencao',
                'identificador' => 'maintenance',
                'rotas_ativas' => 'maintenance',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor', 'inspector', 'viewer']
            ],
            [
                'order' => 8,
                'secao' => 'MANUTENÇÃO',
                'label' => 'Agendamento',
                'icone' => 'schedule',
                'url' => '/agendamento-manutencao',
                'identificador' => 'maintenance-schedule',
                'rotas_ativas' => 'maintenance-schedule',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor']
            ],
            
            // === SEÇÃO RELATÓRIOS ===
            [
                'order' => 9,
                'secao' => 'RELATÓRIOS',
                'label' => 'Relatórios',
                'icone' => 'assessment',
                'url' => '/relatorios',
                'identificador' => 'reports',
                'rotas_ativas' => 'reports',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin', 'supervisor', 'viewer']
            ],
            
            // === SEÇÃO ADMINISTRAÇÃO ===
            [
                'order' => 10,
                'secao' => 'ADMINISTRAÇÃO',
                'label' => 'Usuários',
                'icone' => 'people',
                'url' => '/usuarios',
                'identificador' => 'users',
                'rotas_ativas' => 'users',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin']
            ],
            [
                'order' => 11,
                'secao' => 'ADMINISTRAÇÃO',
                'label' => 'Cargos e Permissões',
                'icone' => 'admin_panel_settings',
                'url' => '/cargos-permissoes',
                'identificador' => 'roles',
                'rotas_ativas' => 'roles',
                'parent_id' => null,
                'roles' => ['super_admin', 'admin']
            ]
        ];

        foreach ($menus as $menuData) {
            $roles = $menuData['roles'];
            unset($menuData['roles']);
            
            $menu = Menu::updateOrCreate(
                ['identificador' => $menuData['identificador']],
                $menuData
            );
            
            // Associar o menu aos roles
            $roleIds = Role::whereIn('name', $roles)->pluck('id')->toArray();
            $menu->roles()->sync($roleIds);
        }
    }
}
