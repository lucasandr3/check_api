<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Http\Resources\MenuGroupedResource;
use App\Http\Resources\UIPermissionResource;
use App\Models\User;
use App\Services\UIPermissionService;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Endpoints para autenticação JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Fazer login na API",
     *     description="Autentica o usuário e retorna um token JWT",
     *     tags={"Autenticação"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="João Silva"),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="tenant_id", type="string", example="1000"),
     *                     @OA\Property(property="company_id", type="integer", example=1)
     *                 ),
     *                 @OA\Property(property="tenant", type="object", nullable=true,
     *                     @OA\Property(property="id", type="string", example="1000"),
     *                     @OA\Property(property="name", type="string", example="Empresa ABC"),
     *                     @OA\Property(property="schema", type="string", example="tenant_1000")
     *                 ),
     *                 @OA\Property(property="companies", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Empresa Principal"),
     *                     @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *                     @OA\Property(property="email", type="string", example="contato@empresa.com"),
     *                     @OA\Property(property="phone", type="string", example="(11) 99999-9999")
     *                 )),
     *                 @OA\Property(property="permissions", type="object"),
     *                 @OA\Property(property="menus", type="array", @OA\Items(
     *                     @OA\Property(property="secao", type="string", example="PRINCIPAL"),
     *                     @OA\Property(property="menus", type="array", @OA\Items(type="object"))
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Credenciais inválidas")
     *         )
     *     )
     * )
     */
    public function login(LoginFormRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = Auth::user();
        
        // Criar token Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;
        
        // Obter tenant atual do middleware
        $currentTenant = \App\Models\Tenant::current();
        
        // Obter empresas do tenant
        $companies = \App\Models\Company::where('tenant_id', $currentTenant->id)
            ->select('id', 'name', 'cnpj', 'email', 'phone')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tenant_id' => $user->tenant_id,
                    'company_id' => $user->company_id,
                ],
                'tenant' => $currentTenant ? [
                    'id' => $currentTenant->id,
                    'name' => $currentTenant->name,
                    'schema' => $currentTenant->schema_name,
                ] : null,
                'companies' => $companies,
                'permissions' => new UIPermissionResource($this->getUIPermissions($user)),
                'menus' => new MenuGroupedResource($user->getAccessibleMenus())
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/me",
     *     summary="Obter dados do usuário autenticado",
     *     description="Retorna informações do usuário atualmente logado",
     *     tags={"Autenticação"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João Silva"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="tenant_id", type="string", example="793ae398-9d4a-4b70-a9a0-220dc8dbc2cc"),
     *                 @OA\Property(property="office_id", type="integer", example=1),
     *                 @OA\Property(property="office", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Oficina Central"),
     *                     @OA\Property(property="email", type="string", example="contato@oficinacentral.com"),
     *                     @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *                     @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
     *                     @OA\Property(property="address", type="string", example="Rua das Oficinas, 123")
     *                 ),
     *                 @OA\Property(property="permissions", type="object"),
     *                 @OA\Property(property="menus", type="array", @OA\Items(
     *                     @OA\Property(property="secao", type="string", example="PRINCIPAL"),
     *                     @OA\Property(property="menus", type="array", @OA\Items(type="object"))
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();
        
        // Carregar os dados da empresa
        $user->load('company');
        
        // Obter tenant atual
        $currentTenant = \App\Models\Tenant::current();
        
        // Obter empresas do tenant
        $companies = \App\Models\Company::where('tenant_id', $currentTenant->id)
            ->select('id', 'name', 'cnpj', 'email', 'phone')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'company_id' => $user->company_id,
                'company' => $user->company ? [
                    'id' => $user->company->id,
                    'name' => $user->company->name,
                    'email' => $user->company->email,
                    'cnpj' => $user->company->cnpj,
                    'phone' => $user->company->phone,
                    'address' => $user->company->address
                ] : null,
                'companies' => $companies,
                'permissions' => new UIPermissionResource($this->getUIPermissions($user)),
                'menus' => new MenuGroupedResource($user->getAccessibleMenus())
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Fazer logout da API",
     *     description="Invalida o token JWT atual",
     *     tags={"Autenticação"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Renovar token JWT",
     *     description="Gera um novo token JWT válido",
     *     tags={"Autenticação"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\JsonContent(
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             )
     *         )
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        $token = Auth::guard('api')->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/permissions/check",
     *     summary="Verificar permissões específicas",
     *     description="Verifica se o usuário tem permissões específicas para UI",
     *     tags={"Autenticação"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="module",
     *         in="query",
     *         description="Módulo para verificar permissões",
     *         required=true,
     *         @OA\Schema(type="string", example="services")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Ação para verificar permissão",
     *         required=false,
     *         @OA\Schema(type="string", example="create")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissões verificadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="module", type="string", example="services"),
     *                 @OA\Property(property="action", type="string", example="create"),
     *                 @OA\Property(property="has_permission", type="boolean", example=true),
     *                 @OA\Property(property="permissions", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Parâmetros inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Módulo é obrigatório")
     *         )
     *     )
     * )
     */
    public function checkPermissions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string',
            'action' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = Auth::guard('api')->user();
        $module = $request->input('module');
        $action = $request->input('action');

        $permissionService = new UIPermissionService();
        
        if ($action) {
            $hasPermission = $permissionService->canExecuteAction($user, $module, $action);
            $permissions = $permissionService->getModulePermissions($user, $module);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'module' => $module,
                    'action' => $action,
                    'has_permission' => $hasPermission,
                    'permissions' => $permissions
                ]
            ]);
        }

        // Se não especificar ação, retorna todas as permissões do módulo
        $canAccess = $permissionService->canAccessModule($user, $module);
        $permissions = $permissionService->getModulePermissions($user, $module);

        return response()->json([
            'success' => true,
            'data' => [
                'module' => $module,
                'can_access' => $canAccess,
                'permissions' => $permissions
            ]
        ]);
    }

    /**
     * Obter permissões de UI para o frontend Angular
     * Estas permissões são apenas para interface, não para validação de segurança
     */
    private function getUIPermissions(User $user): array
    {
        $permissionService = new UIPermissionService();
        return $permissionService->getUserUIPermissions($user);
    }
}
