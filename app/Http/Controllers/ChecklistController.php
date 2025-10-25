<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Checklist;
use App\Models\ChecklistPhoto;
use App\Http\Resources\ChecklistResource;
use App\Http\Resources\PaginatedResource;
use App\Http\Requests\ChecklistFormRequest;
use App\Http\Requests\ChecklistPhotoFormRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Checklists",
 *     description="Endpoints para gerenciamento de checklists de serviços"
 * )
 */
class ChecklistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checklists",
     *     summary="Listar todos os checklists",
     *     description="Retorna uma lista paginada de todos os checklists da oficina",
     *     tags={"Checklists"},
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
     *         description="Lista de checklists retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Checklist")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $checklists = Checklist::with(['user', 'office', 'photos'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(new PaginatedResource($checklists, ChecklistResource::class));
    }

    /**
     * @OA\Post(
     *     path="/api/checklists",
     *     summary="Criar um novo checklist",
     *     description="Cria um novo checklist para um serviço",
     *     tags={"Checklists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_id","items"},
     *             @OA\Property(property="service_id", type="integer", example=1, description="ID do serviço"),
     *             @OA\Property(property="status", type="string", enum={"pending","in_progress","completed"}, default="pending", description="Status do checklist"),
     *             @OA\Property(property="items", type="array", @OA\Items(type="string"), example={"Verificar óleo","Verificar filtros","Testar freios"}, description="Itens do checklist"),
     *             @OA\Property(property="observations", type="string", example="Observações adicionais", description="Observações")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Checklist criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Checklist criado com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/Checklist")
     *         )
     *     )
     * )
     */
    public function store(ChecklistFormRequest $request): JsonResponse
    {
        $checklist = Checklist::create([
            'tenant_id' => auth()->user()->tenant_id,
            'office_id' => $request->office_id,
            'service_id' => $request->service_id,
            'user_id' => $request->user_id,
            'items' => $request->items,
            'observations' => $request->observations,
        ]);

        return response()->json([
            'data' => new ChecklistResource($checklist->load(['service.vehicle.client', 'user', 'office'])),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/checklists/{id}",
     *     summary="Obter detalhes de um checklist",
     *     description="Retorna os detalhes completos de um checklist específico",
     *     tags={"Checklists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do checklist",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do checklist retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Checklist")
     *         )
     *     )
     * )
     */
    public function show(Checklist $checklist): JsonResponse
    {
        return response()->json([
            'data' => new ChecklistResource($checklist->load([
                'service.vehicle.client', 
                'user', 
                'office', 
                'photos'
            ]))
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/checklists/{id}",
     *     summary="Atualizar um checklist",
     *     description="Atualiza os dados de um checklist existente",
     *     tags={"Checklists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do checklist",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ChecklistUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checklist atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Checklist atualizado com sucesso"),
     *             @OA\Property(property="data", ref="#/components/schemas/Checklist")
     *         )
     *     )
     * )
     */
    public function update(ChecklistFormRequest $request, Checklist $checklist): JsonResponse
    {
        $checklist->update($request->validated());

        return response()->json([
            'data' => new ChecklistResource($checklist->load([
                'service.vehicle.client', 
                'user', 
                'office', 
                'photos'
            ]))
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/checklists/{id}",
     *     summary="Excluir um checklist",
     *     description="Remove um checklist do sistema",
     *     tags={"Checklists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do checklist",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checklist excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Checklist excluído com sucesso")
     *         )
     *     )
     * )
     */
    public function destroy(Checklist $checklist): JsonResponse
    {
        // Remover fotos associadas
        foreach ($checklist->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
            $photo->delete();
        }

        $checklist->delete();

        return response()->json([
            'message' => 'Checklist removido com sucesso',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/checklists/{id}/photos",
     *     summary="Upload de fotos para checklist",
     *     description="Faz upload de uma ou mais fotos para um checklist específico",
     *     tags={"Checklists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do checklist",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="photos[]", type="array", @OA\Items(type="string", format="binary"), description="Arquivos de foto"),
     *                 @OA\Property(property="descriptions[]", type="array", @OA\Items(type="string"), description="Descrições das fotos")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fotos enviadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Fotos enviadas com sucesso"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ChecklistPhoto"))
     *         )
     *     )
     * )
     */
    public function uploadPhotos(ChecklistPhotoFormRequest $request, Checklist $checklist): JsonResponse
    {

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $index => $photo) {
            $filename = time() . '_' . $index . '.' . $photo->getClientOriginalExtension();
            $path = $photo->storeAs('checklist_photos', $filename, 'public');
            
            $checklistPhoto = ChecklistPhoto::create([
                'tenant_id' => $checklist->tenant_id,
                'checklist_id' => $checklist->id,
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
                'description' => $request->input("descriptions.{$index}"),
            ]);

            $uploadedPhotos[] = $checklistPhoto;
        }

        return response()->json([
            'message' => 'Fotos enviadas com sucesso',
            'photos' => $uploadedPhotos,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/checklists/{id}/pdf",
     *     summary="Gerar PDF do checklist",
     *     description="Gera um arquivo PDF com os detalhes do checklist",
     *     tags={"Checklists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do checklist",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF gerado com sucesso",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     )
     * )
     */
    public function generatePdf(Checklist $checklist)
    {
        $checklist->load(['service.vehicle.client', 'user', 'office', 'photos']);

        $pdf = Pdf::loadView('pdfs.checklist', compact('checklist'));

        return $pdf->download("checklist-{$checklist->id}.pdf")->header('Content-Disposition', 'attachment; filename="checklist-' . $checklist->id . '.pdf"');
    }

    /**
     * @OA\Get(
     *     path="/api/checklists/service/{serviceId}",
     *     summary="Listar checklists por serviço",
     *     description="Retorna todos os checklists de um serviço específico",
     *     tags={"Checklists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         description="ID do serviço",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checklists do serviço retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Checklist")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function byService(Request $request, int $serviceId): JsonResponse
    {
        $checklists = Checklist::where('service_id', $serviceId)
            ->with(['service.vehicle.client', 'user', 'office', 'photos'])
            ->paginate(10);

        return response()->json(new PaginatedResource($checklists, ChecklistResource::class));
    }
}
