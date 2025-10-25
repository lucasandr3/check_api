<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the office that the user belongs to.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the services assigned to this user.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the checklists created by this user.
     */
    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    /**
     * Get the roles assigned to this user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }

    /**
     * Check if user has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissions) {
            $query->whereIn('name', $permissions);
        })->exists();
    }

    /**
     * Check if user has all of the specified permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $userPermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })
            ->unique()
            ->values();

        return collect($permissions)->every(function ($permission) use ($userPermissions) {
            return $userPermissions->contains($permission);
        });
    }

    /**
     * Get user's accessible menus based on roles.
     */
    public function getAccessibleMenus()
    {
        // Garantir que os roles estão carregados
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }
        
        $roleIds = $this->roles->pluck('id')->toArray();
        
        if (empty($roleIds)) {
            return collect(); // Retorna coleção vazia se não tiver roles
        }
        
        // Usar uma abordagem mais explícita para evitar ambiguidade
        return Menu::select('menus.*')
            ->join('menu_role', 'menus.id', '=', 'menu_role.menu_id')
            ->whereIn('menu_role.role_id', $roleIds)
            ->whereNull('menus.parent_id')
            ->with(['submenus' => function ($query) use ($roleIds) {
                $query->select('menus.*')
                    ->join('menu_role', 'menus.id', '=', 'menu_role.menu_id')
                    ->whereIn('menu_role.role_id', $roleIds);
            }])
            ->orderBy('menus.order')
            ->distinct()
            ->get();
    }
}
