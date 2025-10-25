<?php

namespace App\Http\Controllers;

use App\Models\TireRecord;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Tires",
 *     description="Endpoints para controle de pneus"
 * )
 */
class TireController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tires",
     *     summary="Listar registros de pneus",
     *     description="Retorna lista paginada de registros de pneus",
     *     tags={"Tires"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="vehicle_id",
     *         in="query",
     *         description="Filtrar por veículo",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "removed", "rotated"})
     *     ),
     *     @OA\Parameter(
     *         name="position",
     *         in="query",
     *         description="Filtrar por posição",
     *         required=false,
     *         @OA\Schema(type="string", enum={"front_left", "front_right", "rear_left", "rear_right", "spare"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de registros de pneus retornada com sucesso"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = TireRecord::where('office_id', $user->office_id)
            ->with(['vehicle:id,brand,model,plate']);

        // Aplicar filtros
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('position')) {
            $query->where('tire_position', $request->position);
        }

        $tires = $query->orderBy('installation_date', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tires->items(),
            'pagination' => [
                'current_page' => $tires->currentPage(),
                'last_page' => $tires->lastPage(),
                'per_page' => $tires->perPage(),
                'total' => $tires->total(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tires",
     *     summary="Registrar novo pneu",
     *     description="Registra instalação de um novo pneu",
     *     tags={"Tires"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vehicle_id", "tire_position", "tire_brand", "tire_model", "tire_size", "installation_date", "installation_km", "tread_depth_new", "cost"},
     *             @OA\Property(property="vehicle_id", type="integer", example=1),
     *             @OA\Property(property="tire_position", type="string", enum={"front_left", "front_right", "rear_left", "rear_right", "spare"}),
     *             @OA\Property(property="tire_brand", type="string", example="Michelin"),
     *             @OA\Property(property="tire_model", type="string", example="Energy XM2"),
     *             @OA\Property(property="tire_size", type="string", example="195/65R15"),
     *             @OA\Property(property="installation_date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="installation_km", type="integer", example=50000),
     *             @OA\Property(property="tread_depth_new", type="number", format="float", example=8.0),
     *             @OA\Property(property="cost", type="number", format="float", example=350.00),
     *             @OA\Property(property="warranty_km", type="integer", example=40000),
     *             @OA\Property(property="observations", type="string", example="Pneu novo instalado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pneu registrado com sucesso"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'tire_position' => 'required|in:front_left,front_right,rear_left,rear_right,spare',
            'tire_brand' => 'required|string|max:100',
            'tire_model' => 'required|string|max:100',
            'tire_size' => 'required|string|max:50',
            'installation_date' => 'required|date',
            'installation_km' => 'required|integer|min:0',
            'tread_depth_new' => 'required|numeric|min:0|max:20',
            'cost' => 'required|numeric|min:0',
            'warranty_km' => 'nullable|integer|min:0',
            'observations' => 'nullable|string|max:1000',
        ]);

        // Verificar se o veículo pertence à mesma oficina
        $vehicle = Vehicle::where('id', $validated['vehicle_id'])
            ->where('office_id', $user->office_id)
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado ou não pertence à sua oficina'
            ], 404);
        }

        // Verificar se já existe pneu ativo na mesma posição
        $existingTire = TireRecord::where('vehicle_id', $validated['vehicle_id'])
            ->where('tire_position', $validated['tire_position'])
            ->where('status', 'active')
            ->first();

        if ($existingTire) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um pneu ativo nesta posição. Remova o pneu atual antes de instalar um novo.'
            ], 422);
        }

        $validated['office_id'] = $user->office_id;
        $validated['status'] = 'active';

        $tire = TireRecord::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pneu registrado com sucesso',
            'data' => $tire->load('vehicle')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tires/{id}",
     *     summary="Visualizar registro de pneu",
     *     description="Retorna detalhes de um registro de pneu específico",
     *     tags={"Tires"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro de pneu retornado com sucesso"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();

        $tire = TireRecord::where('office_id', $user->office_id)
            ->with(['vehicle'])
            ->find($id);

        if (!$tire) {
            return response()->json([
                'success' => false,
                'message' => 'Registro de pneu não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tire
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/tires/{id}",
     *     summary="Atualizar registro de pneu",
     *     description="Atualiza um registro de pneu existente",
     *     tags={"Tires"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro de pneu atualizado com sucesso"
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();

        $tire = TireRecord::where('office_id', $user->office_id)->find($id);

        if (!$tire) {
            return response()->json([
                'success' => false,
                'message' => 'Registro de pneu não encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'tire_brand' => 'sometimes|string|max:100',
            'tire_model' => 'sometimes|string|max:100',
            'tire_size' => 'sometimes|string|max:50',
            'installation_date' => 'sometimes|date',
            'installation_km' => 'sometimes|integer|min:0',
            'tread_depth_new' => 'sometimes|numeric|min:0|max:20',
            'tread_depth_removal' => 'sometimes|nullable|numeric|min:0|max:20',
            'cost' => 'sometimes|numeric|min:0',
            'warranty_km' => 'sometimes|nullable|integer|min:0',
            'observations' => 'sometimes|nullable|string|max:1000',
        ]);

        $tire->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Registro de pneu atualizado com sucesso',
            'data' => $tire->load('vehicle')
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tires/{id}/remove",
     *     summary="Remover pneu",
     *     description="Registra a remoção de um pneu",
     *     tags={"Tires"},
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
     *             required={"removal_date", "removal_km", "removal_reason"},
     *             @OA\Property(property="removal_date", type="string", format="date"),
     *             @OA\Property(property="removal_km", type="integer"),
     *             @OA\Property(property="removal_reason", type="string", enum={"wear", "damage", "rotation", "replacement"}),
     *             @OA\Property(property="tread_depth_removal", type="number", format="float"),
     *             @OA\Property(property="observations", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pneu removido com sucesso"
     *     )
     * )
     */
    public function remove(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();

        $tire = TireRecord::where('office_id', $user->office_id)
            ->where('status', 'active')
            ->find($id);

        if (!$tire) {
            return response()->json([
                'success' => false,
                'message' => 'Pneu ativo não encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'removal_date' => 'required|date|after_or_equal:' . $tire->installation_date,
            'removal_km' => 'required|integer|min:' . $tire->installation_km,
            'removal_reason' => 'required|in:wear,damage,rotation,replacement',
            'tread_depth_removal' => 'nullable|numeric|min:0|max:' . $tire->tread_depth_new,
            'observations' => 'nullable|string|max:1000',
        ]);

        $tire->update([
            'removal_date' => $validated['removal_date'],
            'removal_km' => $validated['removal_km'],
            'removal_reason' => $validated['removal_reason'],
            'tread_depth_removal' => $validated['tread_depth_removal'] ?? null,
            'status' => 'removed',
            'observations' => $validated['observations'] ?? $tire->observations,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pneu removido com sucesso',
            'data' => $tire->load('vehicle')
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tires/vehicle/{vehicleId}/current",
     *     summary="Pneus atuais do veículo",
     *     description="Retorna os pneus atualmente instalados em um veículo",
     *     tags={"Tires"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="vehicleId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pneus atuais retornados com sucesso"
     *     )
     * )
     */
    public function currentTires(int $vehicleId): JsonResponse
    {
        $user = auth()->user();

        // Verificar se o veículo pertence à oficina do usuário
        $vehicle = Vehicle::where('id', $vehicleId)
            ->where('office_id', $user->office_id)
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        }

        $currentTires = TireRecord::where('vehicle_id', $vehicleId)
            ->where('status', 'active')
            ->orderBy('tire_position')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle' => $vehicle,
                'tires' => $currentTires
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tires/reports/wear",
     *     summary="Relatório de desgaste de pneus",
     *     description="Retorna relatório de desgaste dos pneus ativos",
     *     tags={"Tires"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Relatório de desgaste retornado com sucesso"
     *     )
     * )
     */
    public function wearReport(): JsonResponse
    {
        $user = auth()->user();

        $activeTires = TireRecord::where('office_id', $user->office_id)
            ->where('status', 'active')
            ->with(['vehicle:id,brand,model,plate'])
            ->get();

        $report = $activeTires->map(function ($tire) {
            $usageKm = $tire->usage_km;
            $wearPercentage = $tire->wear_percentage;
            $needsReplacement = $tire->needsReplacement();

            return [
                'id' => $tire->id,
                'vehicle' => $tire->vehicle,
                'position' => $tire->tire_position,
                'brand' => $tire->tire_brand,
                'model' => $tire->tire_model,
                'size' => $tire->tire_size,
                'installation_date' => $tire->installation_date,
                'installation_km' => $tire->installation_km,
                'usage_km' => $usageKm,
                'tread_depth_new' => $tire->tread_depth_new,
                'wear_percentage' => $wearPercentage,
                'needs_replacement' => $needsReplacement,
                'cost' => $tire->cost,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/tires/{id}",
     *     summary="Excluir registro de pneu",
     *     description="Exclui um registro de pneu (apenas se não estiver ativo)",
     *     tags={"Tires"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro excluído com sucesso"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $user = auth()->user();

        $tire = TireRecord::where('office_id', $user->office_id)->find($id);

        if (!$tire) {
            return response()->json([
                'success' => false,
                'message' => 'Registro de pneu não encontrado'
            ], 404);
        }

        if ($tire->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir um pneu ativo. Remova o pneu primeiro.'
            ], 422);
        }

        $tire->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro de pneu excluído com sucesso'
        ]);
    }
}
