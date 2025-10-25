<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Checklist;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Endpoints para estatísticas e dados do dashboard"
 * )
 */
class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/stats",
     *     summary="Obter estatísticas do dashboard",
     *     description="Retorna estatísticas gerais do sistema para o dashboard",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas retornadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_services", type="integer", example=150),
     *                 @OA\Property(property="pending_services", type="integer", example=25),
     *                 @OA\Property(property="in_progress_services", type="integer", example=15),
     *                 @OA\Property(property="completed_services", type="integer", example=110),
     *                 @OA\Property(property="total_checklists", type="integer", example=120),
     *                 @OA\Property(property="completed_checklists", type="integer", example=95),
     *                 @OA\Property(property="pending_checklists", type="integer", example=25),
     *                 @OA\Property(property="total_clients", type="integer", example=85),
     *                 @OA\Property(property="total_vehicles", type="integer", example=120),
     *                 @OA\Property(property="total_users", type="integer", example=12),
     *                 @OA\Property(property="total_quotes", type="integer", example=45),
     *                 @OA\Property(property="monthly_revenue", type="number", format="float", example=12500.50),
     *                 @OA\Property(property="monthly_services", type="integer", example=35),
     *                 @OA\Property(property="services_by_status", type="object"),
     *                 @OA\Property(property="services_by_type", type="object"),
     *                 @OA\Property(property="recent_activities", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getStats(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }
        
        $currentMonth = Carbon::now()->startOfMonth();
        $currentOfficeId = $user->office_id;

        // Estatísticas básicas
        $stats = [
            'total_services' => Service::where('office_id', $currentOfficeId)->count(),
            'pending_services' => Service::where('office_id', $currentOfficeId)->where('status', 'pending')->count(),
            'in_progress_services' => Service::where('office_id', $currentOfficeId)->where('status', 'in_progress')->count(),
            'completed_services' => Service::where('office_id', $currentOfficeId)->where('status', 'completed')->count(),
            'total_checklists' => Checklist::where('office_id', $currentOfficeId)->count(),
            'completed_checklists' => Checklist::where('office_id', $currentOfficeId)->where('status', 'completed')->count(),
            'pending_checklists' => Checklist::where('office_id', $currentOfficeId)->where('status', 'pending')->count(),
            'total_clients' => Client::where('office_id', $currentOfficeId)->count(),
            'total_vehicles' => Vehicle::where('office_id', $currentOfficeId)->count(),
            'total_users' => User::where('office_id', $currentOfficeId)->count(),
            'total_equipments' => 0, // TODO: Implementar quando criar modelo Equipment
        ];

        // Receita mensal (estimada baseada em serviços)
        $monthlyRevenue = Service::where('office_id', $currentOfficeId)
            ->where('created_at', '>=', $currentMonth)
            ->sum('estimated_cost');
        
        $stats['monthly_revenue'] = round($monthlyRevenue, 2);
        $stats['monthly_services'] = Service::where('office_id', $currentOfficeId)
            ->where('created_at', '>=', $currentMonth)
            ->count();

        // Serviços por status
        $stats['services_by_status'] = Service::where('office_id', $currentOfficeId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Serviços por tipo
        $stats['services_by_type'] = Service::where('office_id', $currentOfficeId)
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Atividades recentes
        $stats['recent_activities'] = $this->getRecentActivities($currentOfficeId);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/chart-data",
     *     summary="Obter dados para gráficos do dashboard",
     *     description="Retorna dados formatados para gráficos e visualizações",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período para os dados (7d, 30d, 90d)",
     *         required=false,
     *         @OA\Schema(type="string", example="30d")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados dos gráficos retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="services_timeline", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="revenue_timeline", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="checklists_completion", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="top_service_types", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function getChartData(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }
        
        $period = request('period', '30d');
        $currentOfficeId = $user->office_id;
        
        $days = match($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30
        };

        $startDate = Carbon::now()->subDays($days);

        // Timeline de serviços
        $servicesTimeline = Service::where('office_id', $currentOfficeId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => $item->total
                ];
            });

        // Timeline de receita
        $revenueTimeline = Service::where('office_id', $currentOfficeId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(estimated_cost) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => round($item->total, 2)
                ];
            });

        // Taxa de conclusão de checklists
        $checklistsCompletion = Checklist::where('office_id', $currentOfficeId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('sum(case when status = "completed" then 1 else 0 end) as completed')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $percentage = $item->total > 0 ? round(($item->completed / $item->total) * 100, 2) : 0;
                return [
                    'date' => $item->date,
                    'total' => $item->total,
                    'completed' => $item->completed,
                    'percentage' => $percentage
                ];
            });

        // Top tipos de serviço
        $topServiceTypes = Service::where('office_id', $currentOfficeId)
            ->where('created_at', '>=', $startDate)
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'total' => $item->total
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'services_timeline' => $servicesTimeline,
                'revenue_timeline' => $revenueTimeline,
                'checklists_completion' => $checklistsCompletion,
                'top_service_types' => $topServiceTypes
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/quick-actions",
     *     summary="Obter ações rápidas disponíveis",
     *     description="Retorna ações rápidas que o usuário pode executar",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Ações rápidas retornadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getQuickActions(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }
        $actions = [];

        // Verificar permissões para determinar ações disponíveis
        if ($user->hasPermission('services.create')) {
            $actions[] = [
                'id' => 'new_service',
                'label' => 'Novo Serviço',
                'icon' => 'add_circle',
                'url' => '/services/create',
                'color' => 'primary'
            ];
        }

        if ($user->hasPermission('checklists.create')) {
            $actions[] = [
                'id' => 'new_checklist',
                'label' => 'Novo Checklist',
                'icon' => 'checklist',
                'url' => '/checklists/create',
                'color' => 'success'
            ];
        }

        if ($user->hasPermission('clients.create')) {
            $actions[] = [
                'id' => 'new_client',
                'label' => 'Novo Cliente',
                'icon' => 'person_add',
                'url' => '/clients/create',
                'color' => 'info'
            ];
        }

        if ($user->hasPermission('maintenance.create')) {
            $actions[] = [
                'id' => 'new_maintenance',
                'label' => 'Nova Manutenção',
                'icon' => 'build',
                'url' => '/maintenance/create',
                'color' => 'warning'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $actions
        ]);
    }

    /**
     * Obter atividades recentes do sistema
     */
    private function getRecentActivities(int $officeId): array
    {
        $activities = [];

        // Últimos serviços criados
        $recentServices = Service::where('office_id', $officeId)
            ->with(['user:id,name', 'vehicle:id,brand,model,plate'])
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentServices as $service) {
            $activities[] = [
                'type' => 'service_created',
                'title' => 'Novo serviço criado',
                'description' => "Serviço {$service->type} para {$service->vehicle->brand} {$service->vehicle->model}",
                'user' => $service->user->name,
                'timestamp' => $service->created_at->diffForHumans(),
                'data' => [
                    'service_id' => $service->id,
                    'status' => $service->status
                ]
            ];
        }

        // Últimos checklists completados
        $recentChecklists = Checklist::where('office_id', $officeId)
            ->where('status', 'completed')
            ->with(['user:id,name', 'service:id,type'])
            ->latest()
            ->limit(3)
            ->get();

        foreach ($recentChecklists as $checklist) {
            $activities[] = [
                'type' => 'checklist_completed',
                'title' => 'Checklist completado',
                'description' => "Checklist do serviço {$checklist->service->type} foi finalizado",
                'user' => $checklist->user->name,
                'timestamp' => $checklist->updated_at->diffForHumans(),
                'data' => [
                    'checklist_id' => $checklist->id,
                    'service_id' => $checklist->service_id
                ]
            ];
        }

        // Ordenar por timestamp e retornar apenas os 8 mais recentes
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, 8);
    }
}
