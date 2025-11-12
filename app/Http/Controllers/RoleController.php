<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Http\Requests\RoleFormRequest;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Listar roles",
     *     description="Retorna todos os roles do sistema",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Roles retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Role"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Criar novo role",
     *     description="Cria um novo role no sistema",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","display_name"},
     *             @OA\Property(property="name", type="string", example="admin"),
     *             @OA\Property(property="display_name", type="string", example="Administrador"),
     *             @OA\Property(property="description", type="string", example="Acesso total ao sistema"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1,2,3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     )
     * )
     */
    public function store(RoleFormRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role criado com sucesso',
            'data' => $role
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Atualizar role",
     *     description="Atualiza um role existente",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="admin"),
     *             @OA\Property(property="display_name", type="string", example="Administrador"),
     *             @OA\Property(property="description", type="string", example="Acesso total ao sistema"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1,2,3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     )
     * )
     */
    public function update(RoleFormRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role atualizado com sucesso',
            'data' => $role
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Excluir role",
     *     description="Exclui um role do sistema",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role excluído com sucesso")
     *         )
     *     )
     * )
     */
    public function destroy(Role $role): JsonResponse
    {
        // A autorização é feita pelo middleware de permissões

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role excluído com sucesso'
        ]);
    }
}
