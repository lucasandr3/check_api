<?php

namespace App\Services;

use App\Models\User;

class UIPermissionService
{
    /**
     * Obter todas as permissões de UI para o usuário
     */
    public function getUserUIPermissions(User $user): array
    {
        return [
            'users' => $this->getUserPermissions($user),
            'roles' => $this->getRolePermissions($user),
            'permissions' => $this->getPermissionPermissions($user),
            'menus' => $this->getMenuPermissions($user),
            'services' => $this->getServicePermissions($user),
            'checklists' => $this->getChecklistPermissions($user),
            'clients' => $this->getClientPermissions($user),
            'vehicles' => $this->getVehiclePermissions($user),
            'offices' => $this->getOfficePermissions($user),
            'reports' => $this->getReportPermissions($user),
            'system' => $this->getSystemPermissions($user),
        ];
    }

    /**
     * Permissões de usuários
     */
    private function getUserPermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('admin.users.view'),
            'can_create' => $user->hasPermission('admin.users.create'),
            'can_edit' => $user->hasPermission('admin.users.edit'),
            'can_delete' => $user->hasPermission('admin.users.delete'),
            'can_manage_roles' => $user->hasPermission('admin.users.manage_roles'),
        ];
    }

    /**
     * Permissões de roles
     */
    private function getRolePermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('admin.roles.view'),
            'can_create' => $user->hasPermission('admin.roles.create'),
            'can_edit' => $user->hasPermission('admin.roles.edit'),
            'can_delete' => $user->hasPermission('admin.roles.delete'),
            'can_manage_permissions' => $user->hasPermission('admin.roles.manage_permissions'),
        ];
    }

    /**
     * Permissões de permissões
     */
    private function getPermissionPermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('admin.permissions.view'),
            'can_create' => $user->hasPermission('admin.permissions.create'),
            'can_edit' => $user->hasPermission('admin.permissions.edit'),
            'can_delete' => $user->hasPermission('admin.permissions.delete'),
        ];
    }

    /**
     * Permissões de menus
     */
    private function getMenuPermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('admin.menus.view'),
            'can_create' => $user->hasPermission('admin.menus.create'),
            'can_edit' => $user->hasPermission('admin.menus.edit'),
            'can_delete' => $user->hasPermission('admin.menus.delete'),
            'can_manage_roles' => $user->hasPermission('admin.menus.manage_roles'),
        ];
    }

    /**
     * Permissões de serviços
     */
    private function getServicePermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('services.view'),
            'can_create' => $user->hasPermission('services.create'),
            'can_edit' => $user->hasPermission('services.edit'),
            'can_delete' => $user->hasPermission('services.delete'),
            'can_update_status' => $user->hasPermission('services.update_status'),
        ];
    }

    /**
     * Permissões de checklists
     */
    private function getChecklistPermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('checklists.view'),
            'can_create' => $user->hasPermission('checklists.create'),
            'can_edit' => $user->hasPermission('checklists.edit'),
            'can_delete' => $user->hasPermission('checklists.delete'),
            'can_upload_photos' => $user->hasPermission('checklists.upload_photos'),
            'can_generate_pdf' => $user->hasPermission('checklists.generate_pdf'),
        ];
    }

    /**
     * Permissões de clientes
     */
    private function getClientPermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('clients.view'),
            'can_create' => $user->hasPermission('clients.create'),
            'can_edit' => $user->hasPermission('clients.edit'),
            'can_delete' => $user->hasPermission('clients.delete'),
        ];
    }

    /**
     * Permissões de veículos
     */
    private function getVehiclePermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('vehicles.view'),
            'can_create' => $user->hasPermission('vehicles.create'),
            'can_edit' => $user->hasPermission('vehicles.edit'),
            'can_delete' => $user->hasPermission('vehicles.delete'),
        ];
    }

    /**
     * Permissões de escritórios
     */
    private function getOfficePermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('offices.view'),
            'can_create' => $user->hasPermission('offices.create'),
            'can_edit' => $user->hasPermission('offices.edit'),
            'can_delete' => $user->hasPermission('offices.delete'),
        ];
    }

    /**
     * Permissões de relatórios
     */
    private function getReportPermissions(User $user): array
    {
        return [
            'can_view' => $user->hasPermission('reports.view'),
            'can_export' => $user->hasPermission('reports.export'),
            'can_generate' => $user->hasPermission('reports.generate'),
        ];
    }

    /**
     * Permissões de sistema
     */
    private function getSystemPermissions(User $user): array
    {
        return [
            'can_access_admin_panel' => $user->hasPermission('admin.panel'),
            'can_manage_tenants' => $user->hasPermission('admin.tenants'),
            'can_view_logs' => $user->hasPermission('admin.logs'),
            'can_manage_backups' => $user->hasPermission('admin.backups'),
        ];
    }

    /**
     * Verificar se o usuário tem permissão para acessar um módulo específico
     */
    public function canAccessModule(User $user, string $module): bool
    {
        $permissions = $this->getUserUIPermissions($user);
        
        if (!isset($permissions[$module])) {
            return false;
        }
        
        // Verificar se tem pelo menos uma permissão de visualização
        return $permissions[$module]['can_view'] ?? false;
    }

    /**
     * Obter permissões de um módulo específico
     */
    public function getModulePermissions(User $user, string $module): array
    {
        $permissions = $this->getUserUIPermissions($user);
        return $permissions[$module] ?? [];
    }

    /**
     * Verificar se o usuário pode executar uma ação específica
     */
    public function canExecuteAction(User $user, string $module, string $action): bool
    {
        $permissions = $this->getModulePermissions($user, $module);
        $actionKey = 'can_' . $action;
        
        return $permissions[$actionKey] ?? false;
    }
}
