<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Vehicles",
 *     description="Endpoints para gestão de veículos"
 * )
 */
class VehicleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/vehicles",
     *     summary="Listar veículos",
     *     description="Retorna lista paginada de veículos",
     *     tags={"Vehicles"},
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
     *         description="Buscar por placa, marca, modelo ou chassi",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoria",
     *         required=false,
     *         @OA\Schema(type="string", enum={"car", "truck", "motorcycle", "van"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "maintenance", "inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de veículos retornada com sucesso"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Vehicle::where('company_id', $user->company_id)
            ->with(['client:id,name', 'company:id,name']);

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('chassis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $vehicles = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $vehicles,
            'message' => 'Veículos listados com sucesso'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/vehicles",
     *     summary="Cadastrar novo veículo",
     *     description="Cria um novo veículo no sistema",
     *     tags={"Vehicles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"brand", "model", "plate", "year"},
     *             @OA\Property(property="brand", type="string", example="Volkswagen"),
     *             @OA\Property(property="model", type="string", example="Gol"),
     *             @OA\Property(property="year", type="integer", example=2020),
     *             @OA\Property(property="color", type="string", example="Branco"),
     *             @OA\Property(property="plate", type="string", example="ABC-1234"),
     *             @OA\Property(property="chassis", type="string", example="9BW12345678901234"),
     *             @OA\Property(property="fuel_type", type="string", enum={"gasoline", "ethanol", "diesel", "flex"}, example="flex"),
     *             @OA\Property(property="engine", type="string", example="1.0"),
     *             @OA\Property(property="transmission", type="string", enum={"manual", "automatic"}, example="manual"),
     *             @OA\Property(property="category", type="string", enum={"car", "truck", "motorcycle", "van"}, example="car"),
     *             @OA\Property(property="current_km", type="integer", example=50000),
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="acquisition_date", type="string", format="date", example="2020-01-15"),
     *             @OA\Property(property="license_expiration", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="insurance_expiration", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="status", type="string", enum={"active", "maintenance", "inactive"}, example="active"),
     *             @OA\Property(property="observations", type="string", example="Veículo em bom estado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Veículo cadastrado com sucesso"
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
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'color' => 'nullable|string|max:50',
                'plate' => 'required|string|max:10|unique:vehicles,plate',
                'chassis' => 'nullable|string|max:50|unique:vehicles,chassis',
                'fuel_type' => 'nullable|in:gasoline,ethanol,diesel,flex',
                'engine' => 'nullable|string|max:50',
                'transmission' => 'nullable|in:manual,automatic',
                'category' => 'nullable|in:car,truck,motorcycle,van',
                'current_km' => 'nullable|integer|min:0',
                'client_id' => 'nullable|exists:clients,id',
                'acquisition_date' => 'nullable|date',
                'license_expiration' => 'nullable|date',
                'insurance_expiration' => 'nullable|date',
                'status' => 'nullable|in:active,maintenance,inactive',
                'observations' => 'nullable|string|max:1000',
            ]);

            $vehicleData = array_merge($validated, [
                'tenant_id' => $user->tenant_id,
                'company_id' => $user->company_id,
                'status' => $validated['status'] ?? 'active',
            ]);

            $vehicle = DB::transaction(function () use ($vehicleData) {
                return Vehicle::create($vehicleData);
            });

            $vehicle->load(['client:id,name', 'company:id,name']);

            return response()->json([
                'success' => true,
                'data' => $vehicle,
                'message' => 'Veículo cadastrado com sucesso'
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
     *     path="/api/vehicles/{id}",
     *     summary="Obter veículo específico",
     *     description="Retorna dados detalhados de um veículo",
     *     tags={"Vehicles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do veículo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Veículo encontrado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Veículo não encontrado"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $vehicle = Vehicle::where('company_id', $user->company_id)
            ->with([
                'client:id,name,email',
                'company:id,name',
                'checklists' => function ($query) {
                    $query->latest()->limit(5);
                },
                'maintenanceRecords' => function ($query) {
                    $query->latest()->limit(5);
                },
                'maintenanceSchedules' => function ($query) {
                    $query->where('is_active', true)->orderBy('next_due_date');
                },
                'tireRecords' => function ($query) {
                    $query->latest()->limit(4);
                }
            ])
            ->find($id);

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vehicle,
            'message' => 'Veículo encontrado com sucesso'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/vehicles/{id}",
     *     summary="Atualizar veículo",
     *     description="Atualiza dados de um veículo existente",
     *     tags={"Vehicles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do veículo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="brand", type="string", example="Volkswagen"),
     *             @OA\Property(property="model", type="string", example="Gol"),
     *             @OA\Property(property="year", type="integer", example=2020),
     *             @OA\Property(property="plate", type="string", example="ABC-1234"),
     *             @OA\Property(property="current_km", type="integer", example=55000),
     *             @OA\Property(property="status", type="string", enum={"active", "maintenance", "inactive"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Veículo atualizado com sucesso"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $vehicle = Vehicle::where('company_id', $user->company_id)->find($id);

            if (!$vehicle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veículo não encontrado'
                ], 404);
            }

            $validated = $request->validate([
                'brand' => 'sometimes|required|string|max:100',
                'model' => 'sometimes|required|string|max:100',
                'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'color' => 'nullable|string|max:50',
                'plate' => 'sometimes|required|string|max:10|unique:vehicles,plate,' . $id,
                'chassis' => 'nullable|string|max:50|unique:vehicles,chassis,' . $id,
                'fuel_type' => 'nullable|in:gasoline,ethanol,diesel,flex',
                'engine' => 'nullable|string|max:50',
                'transmission' => 'nullable|in:manual,automatic',
                'category' => 'nullable|in:car,truck,motorcycle,van',
                'current_km' => 'nullable|integer|min:0',
                'client_id' => 'nullable|exists:clients,id',
                'acquisition_date' => 'nullable|date',
                'license_expiration' => 'nullable|date',
                'insurance_expiration' => 'nullable|date',
                'status' => 'sometimes|required|in:active,maintenance,inactive',
                'observations' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($vehicle, $validated) {
                $vehicle->update($validated);
            });

            $vehicle->load(['client:id,name', 'company:id,name']);

            return response()->json([
                'success' => true,
                'data' => $vehicle,
                'message' => 'Veículo atualizado com sucesso'
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
     *     path="/api/vehicles/{id}",
     *     summary="Remover veículo",
     *     description="Remove um veículo do sistema",
     *     tags={"Vehicles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do veículo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Veículo removido com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Veículo não encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $vehicle = Vehicle::where('company_id', $user->company_id)->find($id);

            if (!$vehicle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veículo não encontrado'
                ], 404);
            }

            // Verificar se há checklists, serviços ou manutenções associadas
            $hasAssociatedRecords = $vehicle->checklists()->exists() || 
                                   $vehicle->services()->exists() ||
                                   $vehicle->maintenanceRecords()->exists();

            if ($hasAssociatedRecords) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível remover veículo com checklists, serviços ou manutenções associadas'
                ], 422);
            }

            DB::transaction(function () use ($vehicle) {
                $vehicle->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Veículo removido com sucesso'
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

