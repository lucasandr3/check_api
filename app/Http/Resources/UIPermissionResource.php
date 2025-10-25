<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UIPermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'users' => [
                'can_view' => $this->resource['users']['can_view'] ?? false,
                'can_create' => $this->resource['users']['can_create'] ?? false,
                'can_edit' => $this->resource['users']['can_edit'] ?? false,
                'can_delete' => $this->resource['users']['can_delete'] ?? false,
                'can_manage_roles' => $this->resource['users']['can_manage_roles'] ?? false,
            ],
            'roles' => [
                'can_view' => $this->resource['roles']['can_view'] ?? false,
                'can_create' => $this->resource['roles']['can_create'] ?? false,
                'can_edit' => $this->resource['roles']['can_edit'] ?? false,
                'can_delete' => $this->resource['roles']['can_delete'] ?? false,
                'can_manage_permissions' => $this->resource['roles']['can_manage_permissions'] ?? false,
            ],
            'permissions' => [
                'can_view' => $this->resource['permissions']['can_view'] ?? false,
                'can_create' => $this->resource['permissions']['can_create'] ?? false,
                'can_edit' => $this->resource['permissions']['can_edit'] ?? false,
                'can_delete' => $this->resource['permissions']['can_delete'] ?? false,
            ],
            'menus' => [
                'can_view' => $this->resource['menus']['can_view'] ?? false,
                'can_create' => $this->resource['menus']['can_create'] ?? false,
                'can_edit' => $this->resource['menus']['can_edit'] ?? false,
                'can_delete' => $this->resource['menus']['can_delete'] ?? false,
                'can_manage_roles' => $this->resource['menus']['can_manage_roles'] ?? false,
            ],
            'services' => [
                'can_view' => $this->resource['services']['can_view'] ?? false,
                'can_create' => $this->resource['services']['can_create'] ?? false,
                'can_edit' => $this->resource['services']['can_edit'] ?? false,
                'can_delete' => $this->resource['services']['can_delete'] ?? false,
                'can_update_status' => $this->resource['services']['can_update_status'] ?? false,
            ],
            'checklists' => [
                'can_view' => $this->resource['checklists']['can_view'] ?? false,
                'can_create' => $this->resource['checklists']['can_create'] ?? false,
                'can_edit' => $this->resource['checklists']['can_edit'] ?? false,
                'can_delete' => $this->resource['checklists']['can_delete'] ?? false,
                'can_upload_photos' => $this->resource['checklists']['can_upload_photos'] ?? false,
                'can_generate_pdf' => $this->resource['checklists']['can_generate_pdf'] ?? false,
            ],
            'clients' => [
                'can_view' => $this->resource['clients']['can_view'] ?? false,
                'can_create' => $this->resource['clients']['can_create'] ?? false,
                'can_edit' => $this->resource['clients']['can_edit'] ?? false,
                'can_delete' => $this->resource['clients']['can_delete'] ?? false,
            ],
            'vehicles' => [
                'can_view' => $this->resource['vehicles']['can_view'] ?? false,
                'can_create' => $this->resource['vehicles']['can_create'] ?? false,
                'can_edit' => $this->resource['vehicles']['can_edit'] ?? false,
                'can_delete' => $this->resource['vehicles']['can_delete'] ?? false,
            ],
            'offices' => [
                'can_view' => $this->resource['offices']['can_view'] ?? false,
                'can_create' => $this->resource['offices']['can_create'] ?? false,
                'can_edit' => $this->resource['offices']['can_edit'] ?? false,
                'can_delete' => $this->resource['offices']['can_delete'] ?? false,
            ],
            'reports' => [
                'can_view' => $this->resource['reports']['can_view'] ?? false,
                'can_export' => $this->resource['reports']['can_export'] ?? false,
                'can_generate' => $this->resource['reports']['can_generate'] ?? false,
            ],
            'system' => [
                'can_access_admin_panel' => $this->resource['system']['can_access_admin_panel'] ?? false,
                'can_manage_tenants' => $this->resource['system']['can_manage_tenants'] ?? false,
                'can_view_logs' => $this->resource['system']['can_view_logs'] ?? false,
                'can_manage_backups' => $this->resource['system']['can_manage_backups'] ?? false,
            ]
        ];
    }
}
