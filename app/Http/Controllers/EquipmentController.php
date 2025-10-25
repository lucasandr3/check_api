<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Equipment",
 *     description="Endpoints para gestão de equipamentos"
 * )
 */
class EquipmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/equipment",
     *     summary="Listar equipamentos",
     *     description="Retorna lista paginada de equipamentos",
     *     tags={"Equipment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nome, marca ou modelo",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo de equipamento",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "maintenance", "inactive", "repair"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de equipamentos retornada com sucesso"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Equipment::where('office_id', $user->office_id)
            ->with(['client:id,name', 'office:id,name']);

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $equipment = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipamentos listados com sucesso'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/equipment",
     *     summary="Criar novo equipamento",
     *     description="Cria um novo equipamento no sistema",
     *     tags={"Equipment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "brand", "model", "client_id"},
     *             @OA\Property(property="name", type="string", example="Gerador Diesel 100kVA"),
     *             @OA\Property(property="type", type="string", example="generator"),
     *             @OA\Property(property="brand", type="string", example="Caterpillar"),
     *             @OA\Property(property="model", type="string", example="C4.4"),
     *             @OA\Property(property="serial_number", type="string", example="CAT123456"),
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="acquisition_date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="warranty_expiration", type="string", format="date", example="2026-01-15"),
     *             @OA\Property(property="location", type="string", example="Galpão A - Setor 1"),
     *             @OA\Property(property="observations", type="string", example="Equipamento novo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Equipamento criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:100',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'serial_number' => 'nullable|string|max:100|unique:equipment,serial_number',
                'client_id' => 'required|exists:clients,id',
                'acquisition_date' => 'nullable|date',
                'warranty_expiration' => 'nullable|date|after:acquisition_date',
                'location' => 'nullable|string|max:255',
                'observations' => 'nullable|string|max:1000',
            ]);

            // Usar record pattern para criar o equipamento
            $equipmentData = new EquipmentRecord(
                name: $validated['name'],
                type: $validated['type'],
                brand: $validated['brand'],
                model: $validated['model'],
                serialNumber: $validated['serial_number'] ?? null,
                clientId: $validated['client_id'],
                acquisitionDate: $validated['acquisition_date'] ?? null,
                warrantyExpiration: $validated['warranty_expiration'] ?? null,
                location: $validated['location'] ?? null,
                observations: $validated['observations'] ?? null,
                officeId: $user->office_id
            );

            $equipment = DB::transaction(function () use ($equipmentData) {
                return Equipment::create($equipmentData->toArray());
            });

            $equipment->load(['client:id,name', 'office:id,name']);

            return response()->json([
                'success' => true,
                'data' => $equipment,
                'message' => 'Equipamento criado com sucesso'
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
     *     path="/api/equipment/{id}",
     *     summary="Obter equipamento específico",
     *     description="Retorna dados detalhados de um equipamento",
     *     tags={"Equipment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do equipamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipamento encontrado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipamento não encontrado"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $equipment = Equipment::where('office_id', $user->office_id)
            ->with([
                'client:id,name,email',
                'office:id,name',
                'checklists' => function ($query) {
                    $query->latest()->limit(5);
                },
                'maintenanceRecords' => function ($query) {
                    $query->latest()->limit(5);
                },
                'maintenanceSchedules' => function ($query) {
                    $query->where('is_active', true)->orderBy('next_due_date');
                }
            ])
            ->find($id);

        if (!$equipment) {
            return response()->json([
                'success' => false,
                'message' => 'Equipamento não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipamento encontrado com sucesso'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/equipment/{id}",
     *     summary="Atualizar equipamento",
     *     description="Atualiza dados de um equipamento existente",
     *     tags={"Equipment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do equipamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Gerador Diesel 100kVA"),
     *             @OA\Property(property="type", type="string", example="generator"),
     *             @OA\Property(property="brand", type="string", example="Caterpillar"),
     *             @OA\Property(property="model", type="string", example="C4.4"),
     *             @OA\Property(property="serial_number", type="string", example="CAT123456"),
     *             @OA\Property(property="status", type="string", enum={"active", "maintenance", "inactive", "repair"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipamento atualizado com sucesso"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $equipment = Equipment::where('office_id', $user->office_id)->find($id);

            if (!$equipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipamento não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|max:100',
                'brand' => 'sometimes|required|string|max:100',
                'model' => 'sometimes|required|string|max:100',
                'serial_number' => 'nullable|string|max:100|unique:equipment,serial_number,' . $id,
                'client_id' => 'sometimes|required|exists:clients,id',
                'acquisition_date' => 'nullable|date',
                'warranty_expiration' => 'nullable|date|after:acquisition_date',
                'status' => 'sometimes|required|in:active,maintenance,inactive,repair',
                'location' => 'nullable|string|max:255',
                'observations' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($equipment, $validated) {
                $equipment->update($validated);
            });

            $equipment->load(['client:id,name', 'office:id,name']);

            return response()->json([
                'success' => true,
                'data' => $equipment,
                'message' => 'Equipamento atualizado com sucesso'
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
     *     path="/api/equipment/{id}",
     *     summary="Remover equipamento",
     *     description="Remove um equipamento do sistema",
     *     tags={"Equipment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do equipamento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Equipamento removido com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Equipamento não encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $equipment = Equipment::where('office_id', $user->office_id)->find($id);

            if (!$equipment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipamento não encontrado'
                ], 404);
            }

            // Verificar se há checklists ou manutenções associadas
            $hasAssociatedRecords = $equipment->checklists()->exists() || 
                                   $equipment->maintenanceRecords()->exists();

            if ($hasAssociatedRecords) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível remover equipamento com checklists ou manutenções associadas'
                ], 422);
            }

            DB::transaction(function () use ($equipment) {
                $equipment->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Equipamento removido com sucesso'
            ]);

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
 * Record para criar equipamentos com validação de tipos
 */
readonly class EquipmentRecord
{
    public function __construct(
        public string $name,
        public string $type,
        public string $brand,
        public string $model,
        public ?string $serialNumber,
        public int $clientId,
        public ?string $acquisitionDate,
        public ?string $warrantyExpiration,
        public ?string $location,
        public ?string $observations,
        public int $officeId,
        public string $status = 'active'
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serialNumber,
            'client_id' => $this->clientId,
            'acquisition_date' => $this->acquisitionDate,
            'warranty_expiration' => $this->warrantyExpiration,
            'location' => $this->location,
            'observations' => $this->observations,
            'office_id' => $this->officeId,
            'status' => $this->status,
        ];
    }
}
