<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Http\Resources\AuditLogResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/audit/logs",
     *     summary="Listar logs de auditoria",
     *     description="Retorna os logs de auditoria com filtros e paginação",
     *     tags={"Auditoria"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="event_type",
     *         in="query",
     *         description="Tipo de evento (created, updated, deleted, login, logout, etc)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         description="Classe do modelo",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="ID do usuário",
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
     *         description="Logs retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AuditLog")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('model')) {
            $query->where('auditable_type', $request->model);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 15);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AuditLogResource::collection($logs->items()),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/audit/logs/{id}",
     *     summary="Visualizar log de auditoria específico",
     *     description="Retorna detalhes de um log de auditoria específico",
     *     tags={"Auditoria"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Log retornado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/AuditLog")
     *         )
     *     )
     * )
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user');

        return response()->json([
            'success' => true,
            'data' => new AuditLogResource($auditLog)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/audit/statistics",
     *     summary="Estatísticas de auditoria",
     *     description="Retorna estatísticas dos logs de auditoria",
     *     tags={"Auditoria"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas retornadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'events_by_type' => AuditLog::select('event_type', DB::raw('count(*) as count'))
                ->groupBy('event_type')
                ->pluck('count', 'event_type'),
            'events_by_model' => AuditLog::select('auditable_type', DB::raw('count(*) as count'))
                ->groupBy('auditable_type')
                ->pluck('count', 'auditable_type'),
            'recent_activity' => AuditLog::with('user')
                ->latest()
                ->limit(10)
                ->get(['id', 'event_type', 'auditable_type', 'user_id', 'created_at']),
            'top_users' => AuditLog::select('user_id', 'user_email', DB::raw('count(*) as count'))
                ->whereNotNull('user_id')
                ->groupBy('user_id', 'user_email')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/audit/models",
     *     summary="Listar modelos auditados",
     *     description="Retorna lista de modelos que possuem auditoria",
     *     tags={"Auditoria"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Modelos retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array")
     *         )
     *     )
     * )
     */
    public function models(): JsonResponse
    {
        $models = AuditLog::select('auditable_type')
            ->distinct()
            ->pluck('auditable_type')
            ->map(function ($model) {
                return [
                    'class' => $model,
                    'name' => class_basename($model),
                    'count' => AuditLog::where('auditable_type', $model)->count()
                ];
            })
            ->sortBy('name');

        return response()->json([
            'success' => true,
            'data' => $models->values()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/audit/export",
     *     summary="Exportar logs de auditoria",
     *     description="Exporta logs de auditoria em formato CSV",
     *     tags={"Auditoria"},
     *     security={{"bearerAuth":{}}},
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
     *         description="Arquivo CSV gerado com sucesso",
     *         @OA\Header(
     *             header="Content-Type",
     *             description="application/csv",
     *             @OA\Schema(type="string")
     *         )
     *     )
     * )
     */
    public function export(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Cabeçalhos
            fputcsv($file, [
                'ID', 'Tipo de Evento', 'Modelo', 'ID do Modelo', 'Usuário', 'Email',
                'IP', 'User Agent', 'Rota', 'Método', 'Descrição', 'Data/Hora'
            ]);

            // Dados
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->event_type,
                    $log->auditable_type,
                    $log->auditable_id,
                    $log->user?->name ?? 'N/A',
                    $log->user_email ?? 'N/A',
                    $log->ip_address ?? 'N/A',
                    $log->user_agent ?? 'N/A',
                    $log->route_name ?? 'N/A',
                    $log->method ?? 'N/A',
                    $log->description ?? 'N/A',
                    $log->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
