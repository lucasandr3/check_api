<?php

namespace App\Http\Controllers;

use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Http\Requests\MenuFormRequest;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/menus",
     *     summary="Listar menus do usuário",
     *     description="Retorna os menus que o usuário tem acesso baseado em suas permissões",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Menus retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Menu"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $menus = $user->getAccessibleMenus();

        return response()->json([
            'success' => true,
            'data' => MenuResource::collection($menus)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/menus/all",
     *     summary="Listar todos os menus (admin)",
     *     description="Retorna todos os menus do sistema para administração",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Menus retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Menu"))
     *         )
     *     )
     * )
     */
    public function all(): JsonResponse
    {
        // A autorização é feita pelo middleware de permissões

        $menus = Menu::whereNull('parent_id')
            ->with('submenus')
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => MenuResource::collection($menus)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/menus",
     *     summary="Criar novo menu",
     *     description="Cria um novo menu no sistema",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order","secao","label","icone","url","identificador","rotas_ativas"},
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="secao", type="string", example="PRINCIPAL"),
     *             @OA\Property(property="label", type="string", example="Dashboard"),
     *             @OA\Property(property="icone", type="string", example="dashboard"),
     *             @OA\Property(property="url", type="string", example="/home"),
     *             @OA\Property(property="identificador", type="string", example="home"),
     *             @OA\Property(property="rotas_ativas", type="string", example="home"),
     *             @OA\Property(property="parent_id", type="integer", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Menu")
     *         )
     *     )
     * )
     */
    public function store(MenuFormRequest $request): JsonResponse
    {
        $menu = Menu::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Menu criado com sucesso',
            'data' => new MenuResource($menu)
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/menus/{id}",
     *     summary="Atualizar menu",
     *     description="Atualiza um menu existente",
     *     tags={"Menus"},
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
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="secao", type="string", example="PRINCIPAL"),
     *             @OA\Property(property="label", type="string", example="Dashboard"),
     *             @OA\Property(property="icone", type="string", example="dashboard"),
     *             @OA\Property(property="url", type="string", example="/home"),
     *             @OA\Property(property="identificador", type="string", example="home"),
     *             @OA\Property(property="rotas_ativas", type="string", example="home"),
     *             @OA\Property(property="parent_id", type="integer", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Menu")
     *         )
     *     )
     * )
     */
    public function update(MenuFormRequest $request, Menu $menu): JsonResponse
    {
        $menu->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Menu atualizado com sucesso',
            'data' => new MenuResource($menu)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/menus/{id}",
     *     summary="Excluir menu",
     *     description="Exclui um menu do sistema",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menu excluído com sucesso")
     *         )
     *     )
     * )
     */
    public function destroy(Menu $menu): JsonResponse
    {
        // A autorização é feita pelo middleware de permissões

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu excluído com sucesso'
        ]);
    }
}
