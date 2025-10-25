<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class TenancyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se já há um tenant ativo
        if (Tenant::hasCurrent()) {
            return $next($request);
        }

        // Lista de rotas que não precisam de tenancy
        $publicRoutes = [
            '/',
            '/test',
            '/auth/login',
            '/up'
        ];

        // Se for uma rota pública, não tentar inicializar tenant
        if (in_array($request->path(), $publicRoutes)) {
            Log::debug("Rota pública - sem tenancy: {$request->path()}");
            return $next($request);
        }

        // 1. Tentar identificar por account_id na URL/header
        $accountId = $this->extractAccountIdFromRequest($request);
        if ($accountId) {
            $tenant = Tenant::find($accountId);
            if ($tenant && $tenant->status === 'active') {
                $tenant->makeCurrent();
                Log::info("Tenant inicializado por account_id: {$tenant->id}");
                return $next($request);
            }
        }

        // 2. Tentar identificar por domínio (opcional - para compatibilidade)
        $host = $request->getHost();
        $tenant = TenantDomain::findTenantByDomain($host);
        if (!$tenant) {
            $tenant = TenantDomain::findTenantBySubdomain($host);
        }
        
        if ($tenant && $tenant->status === 'active') {
            $tenant->makeCurrent();
            Log::info("Tenant inicializado por domínio: {$tenant->id} ({$host})");
            return $next($request);
        }

        // 3. Tentar identificar pelo usuário autenticado
        if (auth()->check()) {
            $tenantId = auth()->user()->tenant_id ?? null;
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant && $tenant->status === 'active') {
                    $tenant->makeCurrent();
                    Log::info("Tenant inicializado por usuário: {$tenant->id} para usuário: " . auth()->user()->email);
                    return $next($request);
                } else {
                    Log::warning("Tenant não encontrado ou inativo para ID: {$tenantId}");
                }
            } else {
                Log::warning("Usuário autenticado sem tenant_id: " . auth()->user()->email);
            }
        }

        // Se chegou aqui e não é rota pública, retornar erro
        Log::warning("Tenant não identificado para rota: {$request->path()}");
        
        return response()->json([
            'error' => 'Tenant não identificado',
            'message' => 'Não foi possível identificar o tenant. Use account_id na URL, header ou faça login.',
            'examples' => [
                'Header: X-Account-ID: 1000',
                'URL: /api/tenant/1000/dashboard',
                'Query: ?account_id=1000',
                'Subdomínio: 1000.check-api.com'
            ]
        ], 400);
    }

    /**
     * Extrai o account_id da requisição
     */
    private function extractAccountIdFromRequest(Request $request): ?string
    {
        // 1. Header X-Account-ID
        if ($request->hasHeader('X-Account-ID')) {
            return $request->header('X-Account-ID');
        }

        // 2. Query parameter
        if ($request->has('account_id')) {
            return $request->get('account_id');
        }

        // 3. URL padrão /api/tenant/{account_id}/...
        $path = $request->path();
        if (preg_match('/^api\/tenant\/(\w+)/', $path, $matches)) {
            return $matches[1];
        }

        // 4. URL padrão /{account_id}/api/...
        if (preg_match('/^(\w+)\/api/', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
