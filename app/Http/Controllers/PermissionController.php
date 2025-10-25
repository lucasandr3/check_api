<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Http\Requests\PermissionFormRequest;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/permissions",
     *     summary="Listar permissões",
     *     description="Retorna todas as permissões do sistema",
     *     tags={"Permissões"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Permissões retornadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Permission"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/permissions",
     *     summary="Criar nova permissão",
     *     description="Cria uma nova permissão no sistema",
     *     tags={"Permissões"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","display_name"},
     *             @OA\Property(property="name", type="string", example="admin.users"),
     *             @OA\Property(property="display_name", type="string", example="Gerenciar Usuários"),
     *             @OA\Property(property="description", type="string", example="Permissão para gerenciar usuários do sistema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permissão criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Permission")
     *         )
     *     )
     * )
     */
    public function store(PermissionFormRequest $request): JsonResponse
    {
        $permission = Permission::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso',
            'data' => $permission
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/permissions/{id}",
     *     summary="Atualizar permissão",
     *     description="Atualiza uma permissão existente",
     *     tags={"Permissões"},
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
     *             @OA\Property(property="name", type="string", example="admin.users"),
     *             @OA\Property(property="display_name", type="string", example="Gerenciar Usuários"),
     *             @OA\Property(property="description", type="string", example="Permissão para gerenciar usuários do sistema")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissão atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Permission")
     *         )
     *     )
     * )
     */
    public function update(PermissionFormRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso',
            'data' => $permission
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/permissions/{id}",
     *     summary="Excluir permissão",
     *     description="Exclui uma permissão do sistema",
     *     tags={"Permissões"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissão excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permissão excluída com sucesso")
     *         )
     *     )
     * )
     */
    public function destroy(Permission $permission): JsonResponse
    {
        // A autorização é feita pelo middleware de permissões

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso'
        ]);
    }
}
