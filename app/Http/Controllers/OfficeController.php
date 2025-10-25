<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Office;
use App\Http\Resources\OfficeResource;
use App\Http\Resources\PaginatedResource;
use App\Http\Requests\OfficeFormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Oficinas",
 *     description="Endpoints para gerenciamento de oficinas"
 * )
 */
class OfficeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/offices",
     *     summary="Listar todas as oficinas",
     *     description="Retorna uma lista paginada de todas as oficinas do tenant",
     *     tags={"Oficinas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de oficinas retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Office")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $offices = Office::with(['tenant'])
            ->orderBy('name')
            ->paginate(10);

        return new PaginatedResource($offices, OfficeResource::class);
    }

    /**
     * @OA\Post(
     *     path="/api/offices",
     *     summary="Criar uma nova oficina",
     *     description="Cria uma nova oficina para o tenant",
     *     tags={"Oficinas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","address","phone"},
     *             @OA\Property(property="name", type="string", example="Oficina Central", description="Nome da oficina"),
     *             @OA\Property(property="address", type="string", example="Rua das Oficinas, 123", description="Endereço da oficina"),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-9999", description="Telefone da oficina"),
     *             @OA\Property(property="email", type="string", format="email", example="contato@oficina.com", description="Email da oficina")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Oficina criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Oficina criada com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/Office")
     *         )
     *     )
     * )
     */
    public function store(OfficeFormRequest $request): JsonResponse
    {
        $office = Office::create($request->validated());

        return response()->json([
            'message' => 'Oficina criada com sucesso',
            'office' => new OfficeResource($office->load('tenant')),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/offices/{id}",
     *     summary="Obter detalhes de uma oficina",
     *     description="Retorna os detalhes completos de uma oficina específica",
     *     tags={"Oficinas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da oficina",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da oficina retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Office")
     *         )
     *     )
     * )
     */
    public function show(Office $office): JsonResponse
    {
        return new OfficeResource($office->load(['tenant', 'users', 'services']));
    }

    /**
     * @OA\Put(
     *     path="/api/offices/{id}",
     *     summary="Atualizar uma oficina",
     *     description="Atualiza os dados de uma oficina existente",
     *     tags={"Oficinas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da oficina",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OfficeUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Oficina atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Oficina atualizada com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/Office")
     *         )
     *     )
     * )
     */
    public function update(OfficeFormRequest $request, Office $office): JsonResponse
    {
        $office->update($request->validated());

        return response()->json([
            'message' => 'Oficina atualizada com sucesso',
            'office' => new OfficeResource($office->fresh()->load('tenant')),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/offices/{id}",
     *     summary="Excluir uma oficina",
     *     description="Remove uma oficina do sistema",
     *     tags={"Oficinas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da oficina",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Oficina excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Oficina excluída com sucesso")
     *         )
     *     )
     * )
     */
    public function destroy(Office $office): JsonResponse
    {
        $office->delete();

        return response()->json([
            'message' => 'Oficina removida com sucesso',
        ]);
    }
}
