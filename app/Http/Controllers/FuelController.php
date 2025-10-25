<?php

namespace App\Http\Controllers;

use App\Models\FuelRecord;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Fuel",
 *     description="Endpoints para controle de abastecimento"
 * )
 */
class FuelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/fuel",
     *     summary="Listar registros de abastecimento",
     *     description="Retorna lista paginada de registros de abastecimento",
     *     tags={"Fuel"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="vehicle_id",
     *         in="query",
     *         description="Filtrar por veículo",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Data inicial (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Data final (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de abastecimentos retornada com sucesso"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = FuelRecord::where('office_id', $user->office_id)
            ->with(['vehicle:id,brand,model,plate']);

        // Aplicar filtros
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('start_date')) {
            $query->where('fuel_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('fuel_date', '<=', $request->end_date);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $fuelRecords = $query->orderBy('fuel_date', 'desc')->paginate($perPage);

        // Calcular eficiência para cada registro
        $fuelRecords->getCollection()->transform(function ($record) {
            $record->efficiency = $record->calculateEfficiency();
            return $record;
        });

        return response()->json([
            'success' => true,
            'data' => $fuelRecords,
            'message' => 'Registros de abastecimento listados com sucesso'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/fuel",
     *     summary="Registrar novo abastecimento",
     *     description="Cria um novo registro de abastecimento",
     *     tags={"Fuel"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vehicle_id", "fuel_type", "liters", "price_per_liter", "total_cost", "odometer_reading", "fuel_date"},
     *             @OA\Property(property="vehicle_id", type="integer", example=1),
     *             @OA\Property(property="fuel_type", type="string", enum={"gasoline", "ethanol", "diesel", "flex"}, example="gasoline"),
     *             @OA\Property(property="liters", type="number", format="float", example=45.5),
     *             @OA\Property(property="price_per_liter", type="number", format="float", example=5.89),
     *             @OA\Property(property="total_cost", type="number", format="float", example=267.995),
     *             @OA\Property(property="odometer_reading", type="integer", example=85420),
     *             @OA\Property(property="fuel_station", type="string", example="Posto Shell - Centro"),
     *             @OA\Property(property="driver_name", type="string", example="João Silva"),
     *             @OA\Property(property="fuel_date", type="string", format="datetime", example="2024-01-15 08:30:00"),
     *             @OA\Property(property="observations", type="string", example="Abastecimento completo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Abastecimento registrado com sucesso"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'fuel_type' => 'required|in:gasoline,ethanol,diesel,flex',
                'liters' => 'required|numeric|min:0.1|max:9999.999',
                'price_per_liter' => 'required|numeric|min:0.01|max:999.999',
                'total_cost' => 'required|numeric|min:0.01|max:999999.99',
                'odometer_reading' => 'required|integer|min:0',
                'fuel_station' => 'nullable|string|max:255',
                'driver_name' => 'nullable|string|max:255',
                'fuel_date' => 'required|date',
                'observations' => 'nullable|string|max:1000',
                'receipt_photo' => 'nullable|string|max:255', // URL da foto do comprovante
            ]);

            // Verificar se o veículo pertence ao office do usuário
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                ->where('office_id', $user->office_id)
                ->first();

            if (!$vehicle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veículo não encontrado ou não autorizado'
                ], 404);
            }

            // Validar se a quilometragem é maior que a anterior
            $lastRecord = FuelRecord::where('vehicle_id', $validated['vehicle_id'])
                ->orderBy('fuel_date', 'desc')
                ->first();

            if ($lastRecord && $validated['odometer_reading'] <= $lastRecord->odometer_reading) {
                return response()->json([
                    'success' => false,
                    'message' => 'A quilometragem deve ser maior que o último registro (' . $lastRecord->odometer_reading . ' km)'
                ], 422);
            }

            // Usar record pattern para criar o registro
            $fuelData = new FuelRecordData(
                vehicleId: $validated['vehicle_id'],
                fuelType: $validated['fuel_type'],
                liters: $validated['liters'],
                pricePerLiter: $validated['price_per_liter'],
                totalCost: $validated['total_cost'],
                odometerReading: $validated['odometer_reading'],
                fuelStation: $validated['fuel_station'] ?? null,
                driverName: $validated['driver_name'] ?? null,
                fuelDate: Carbon::parse($validated['fuel_date']),
                observations: $validated['observations'] ?? null,
                receiptPhoto: $validated['receipt_photo'] ?? null,
                officeId: $user->office_id
            );

            $fuelRecord = DB::transaction(function () use ($fuelData, $vehicle) {
                $record = FuelRecord::create($fuelData->toArray());
                
                // Atualizar a quilometragem atual do veículo
                $vehicle->update(['current_km' => $fuelData->odometerReading]);
                
                return $record;
            });

            $fuelRecord->load(['vehicle:id,brand,model,plate']);
            $fuelRecord->efficiency = $fuelRecord->calculateEfficiency();

            return response()->json([
                'success' => true,
                'data' => $fuelRecord,
                'message' => 'Abastecimento registrado com sucesso'
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
     *     path="/api/fuel/stats/{vehicle_id}",
     *     summary="Obter estatísticas de consumo",
     *     description="Retorna estatísticas de consumo de combustível para um veículo",
     *     tags={"Fuel"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="vehicle_id",
     *         in="path",
     *         description="ID do veículo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período em dias (padrão: 30)",
     *         required=false,
     *         @OA\Schema(type="integer", example=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas retornadas com sucesso"
     *     )
     * )
     */
    public function stats(Request $request, int $vehicleId): JsonResponse
    {
        $user = auth()->user();
        $period = $request->get('period', 30);
        
        $vehicle = Vehicle::where('id', $vehicleId)
            ->where('office_id', $user->office_id)
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        }

        $startDate = Carbon::now()->subDays($period);
        
        $records = FuelRecord::where('vehicle_id', $vehicleId)
            ->where('fuel_date', '>=', $startDate)
            ->orderBy('fuel_date')
            ->get();

        if ($records->count() < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_liters' => $records->sum('liters'),
                    'total_cost' => $records->sum('total_cost'),
                    'average_efficiency' => null,
                    'total_distance' => 0,
                    'records_count' => $records->count(),
                    'period_days' => $period,
                ],
                'message' => 'Estatísticas calculadas (dados insuficientes para eficiência)'
            ]);
        }

        // Calcular estatísticas
        $totalDistance = $records->last()->odometer_reading - $records->first()->odometer_reading;
        $totalLiters = $records->sum('liters');
        $totalCost = $records->sum('total_cost');
        $averageEfficiency = $totalLiters > 0 ? round($totalDistance / $totalLiters, 2) : null;
        
        // Calcular eficiência média considerando apenas abastecimentos válidos
        $validEfficiencies = [];
        for ($i = 1; $i < $records->count(); $i++) {
            $efficiency = $records[$i]->calculateEfficiency();
            if ($efficiency !== null && $efficiency > 0 && $efficiency < 50) { // Filtrar valores absurdos
                $validEfficiencies[] = $efficiency;
            }
        }
        
        $averageRealEfficiency = count($validEfficiencies) > 0 
            ? round(array_sum($validEfficiencies) / count($validEfficiencies), 2)
            : null;

        return response()->json([
            'success' => true,
            'data' => [
                'total_liters' => round($totalLiters, 2),
                'total_cost' => round($totalCost, 2),
                'average_efficiency' => $averageRealEfficiency,
                'total_distance' => $totalDistance,
                'records_count' => $records->count(),
                'period_days' => $period,
                'cost_per_km' => $totalDistance > 0 ? round($totalCost / $totalDistance, 3) : null,
                'average_price_per_liter' => round($totalCost / $totalLiters, 3),
            ],
            'message' => 'Estatísticas calculadas com sucesso'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/fuel/{id}",
     *     summary="Obter registro de abastecimento específico",
     *     description="Retorna dados detalhados de um registro de abastecimento",
     *     tags={"Fuel"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do registro",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro encontrado com sucesso"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $fuelRecord = FuelRecord::where('office_id', $user->office_id)
            ->with(['vehicle:id,brand,model,plate,current_km'])
            ->find($id);

        if (!$fuelRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Registro de abastecimento não encontrado'
            ], 404);
        }

        $fuelRecord->efficiency = $fuelRecord->calculateEfficiency();

        return response()->json([
            'success' => true,
            'data' => $fuelRecord,
            'message' => 'Registro encontrado com sucesso'
        ]);
    }
}

/**
 * Record para dados de abastecimento
 */
readonly class FuelRecordData
{
    public function __construct(
        public int $vehicleId,
        public string $fuelType,
        public float $liters,
        public float $pricePerLiter,
        public float $totalCost,
        public int $odometerReading,
        public ?string $fuelStation,
        public ?string $driverName,
        public Carbon $fuelDate,
        public ?string $observations,
        public ?string $receiptPhoto,
        public int $officeId
    ) {}

    public function toArray(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'fuel_type' => $this->fuelType,
            'liters' => $this->liters,
            'price_per_liter' => $this->pricePerLiter,
            'total_cost' => $this->totalCost,
            'odometer_reading' => $this->odometerReading,
            'fuel_station' => $this->fuelStation,
            'driver_name' => $this->driverName,
            'fuel_date' => $this->fuelDate,
            'observations' => $this->observations,
            'receipt_photo' => $this->receiptPhoto,
            'office_id' => $this->officeId,
        ];
    }
}
