<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="ChecklistTemplate",
 *     description="Endpoints para gestão de templates de checklist"
 * )
 */
class ChecklistTemplateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/checklist-templates",
     *     summary="Listar templates de checklist",
     *     description="Retorna lista de templates de checklist",
     *     tags={"ChecklistTemplate"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"preventive", "routine", "corrective"})
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoria",
     *         required=false,
     *         @OA\Schema(type="string", enum={"vehicle", "equipment"})
     *     ),
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Mostrar apenas templates ativos",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Templates listados com sucesso"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = ChecklistTemplate::where('office_id', $user->office_id);

        // Aplicar filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        $templates = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
            'message' => 'Templates listados com sucesso'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/checklist-templates",
     *     summary="Criar novo template de checklist",
     *     description="Cria um novo template de checklist",
     *     tags={"ChecklistTemplate"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "category", "items"},
     *             @OA\Property(property="name", type="string", example="Checklist Preventivo Veículo"),
     *             @OA\Property(property="type", type="string", enum={"preventive", "routine", "corrective"}, example="preventive"),
     *             @OA\Property(property="category", type="string", enum={"vehicle", "equipment"}, example="vehicle"),
     *             @OA\Property(property="description", type="string", example="Template padrão para manutenção preventiva"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="item_1"),
     *                     @OA\Property(property="title", type="string", example="Verificar nível de óleo"),
     *                     @OA\Property(property="description", type="string", example="Verificar nível e qualidade do óleo do motor"),
     *                     @OA\Property(property="required", type="boolean", example=true),
     *                     @OA\Property(property="has_photo", type="boolean", example=false),
     *                     @OA\Property(property="has_observation", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template criado com sucesso"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:preventive,routine,corrective',
                'category' => 'required|in:vehicle,equipment',
                'description' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|string|max:50',
                'items.*.title' => 'required|string|max:255',
                'items.*.description' => 'nullable|string|max:500',
                'items.*.required' => 'boolean',
                'items.*.has_photo' => 'boolean',
                'items.*.has_observation' => 'boolean',
            ]);

            // Usar record pattern
            $templateData = new ChecklistTemplateRecord(
                name: $validated['name'],
                type: $validated['type'],
                category: $validated['category'],
                items: $validated['items'],
                description: $validated['description'] ?? null,
                officeId: $user->office_id
            );

            $template = DB::transaction(function () use ($templateData) {
                return ChecklistTemplate::create($templateData->toArray());
            });

            return response()->json([
                'success' => true,
                'data' => $template,
                'message' => 'Template criado com sucesso'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/checklist-templates/{id}",
     *     summary="Obter template específico",
     *     description="Retorna dados detalhados de um template",
     *     tags={"ChecklistTemplate"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do template",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template encontrado com sucesso"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $template = ChecklistTemplate::where('office_id', $user->office_id)
            ->with(['checklists' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->find($id);

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Template encontrado com sucesso'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/checklist-templates/{id}",
     *     summary="Atualizar template",
     *     description="Atualiza um template existente",
     *     tags={"ChecklistTemplate"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do template",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Checklist Preventivo Veículo Atualizado"),
     *             @OA\Property(property="is_active", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template atualizado com sucesso"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $template = ChecklistTemplate::where('office_id', $user->office_id)->find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|in:preventive,routine,corrective',
                'category' => 'sometimes|required|in:vehicle,equipment',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'items' => 'sometimes|required|array|min:1',
                'items.*.id' => 'required_with:items|string|max:50',
                'items.*.title' => 'required_with:items|string|max:255',
                'items.*.description' => 'nullable|string|max:500',
                'items.*.required' => 'boolean',
                'items.*.has_photo' => 'boolean',
                'items.*.has_observation' => 'boolean',
            ]);

            DB::transaction(function () use ($template, $validated) {
                $template->update($validated);
            });

            return response()->json([
                'success' => true,
                'data' => $template->fresh(),
                'message' => 'Template atualizado com sucesso'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/checklist-templates/{id}",
     *     summary="Remover template",
     *     description="Remove um template do sistema",
     *     tags={"ChecklistTemplate"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do template",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template removido com sucesso"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $template = ChecklistTemplate::where('office_id', $user->office_id)->find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], 404);
            }

            // Verificar se há checklists usando este template
            if ($template->checklists()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível remover template com checklists associados'
                ], 422);
            }

            DB::transaction(function () use ($template) {
                $template->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Template removido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/checklist-templates/{id}/duplicate",
     *     summary="Duplicar template",
     *     description="Cria uma cópia de um template existente",
     *     tags={"ChecklistTemplate"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do template",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Cópia do Template")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Template duplicado com sucesso"
     *     )
     * )
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $originalTemplate = ChecklistTemplate::where('office_id', $user->office_id)->find($id);

            if (!$originalTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], 404);
            }

            $newName = $request->get('name', 'Cópia de ' . $originalTemplate->name);

            $duplicatedTemplate = DB::transaction(function () use ($originalTemplate, $newName, $user) {
                return ChecklistTemplate::create([
                    'office_id' => $user->office_id,
                    'name' => $newName,
                    'type' => $originalTemplate->type,
                    'category' => $originalTemplate->category,
                    'items' => $originalTemplate->items,
                    'description' => $originalTemplate->description,
                    'is_active' => false, // Criar como inativo por padrão
                ]);
            });

            return response()->json([
                'success' => true,
                'data' => $duplicatedTemplate,
                'message' => 'Template duplicado com sucesso'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }
}

/**
 * Record para templates de checklist
 */
readonly class ChecklistTemplateRecord
{
    public function __construct(
        public string $name,
        public string $type,
        public string $category,
        public array $items,
        public ?string $description,
        public int $officeId,
        public bool $isActive = true
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'items' => $this->items,
            'description' => $this->description,
            'office_id' => $this->officeId,
            'is_active' => $this->isActive,
        ];
    }
}
