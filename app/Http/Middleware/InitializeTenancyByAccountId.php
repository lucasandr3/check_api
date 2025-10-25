<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByAccountId
{
    protected $tenancy;
    protected $resolver;

    public function __construct(Tenancy $tenancy, DomainTenantResolver $resolver)
    {
        $this->tenancy = $tenancy;
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Primeiro, tentar resolver por domínio (comportamento padrão)
        try {
            $tenant = $this->resolver->resolve($request);
            if ($tenant) {
                $this->tenancy->initialize($tenant);
                return $next($request);
            }
        } catch (\Exception $e) {
            // Se falhar, continuar para tentar por account_id
        }

        // Se não conseguiu resolver por domínio, tentar por account_id na URL
        $accountId = $this->extractAccountIdFromRequest($request);
        
        if ($accountId) {
            $tenant = Tenant::find($accountId);
            
            if ($tenant) {
                $this->tenancy->initialize($tenant);
                return $next($request);
            }
        }

        // Se chegou aqui, não conseguiu identificar o tenant
        return response()->json([
            'error' => 'Tenant não identificado',
            'message' => 'Não foi possível identificar o tenant pela URL ou domínio'
        ], 404);
    }

    /**
     * Extrai o account_id da requisição
     * Suporta vários formatos:
     * - /api/tenant/1000/dashboard
     * - /1000/api/dashboard  
     * - Header: X-Account-ID
     * - Query param: account_id
     */
    private function extractAccountIdFromRequest(Request $request): ?string
    {
        // 1. Verificar header X-Account-ID
        if ($request->hasHeader('X-Account-ID')) {
            return $request->header('X-Account-ID');
        }

        // 2. Verificar query parameter
        if ($request->has('account_id')) {
            return $request->get('account_id');
        }

        // 3. Verificar na URL - padrão /api/tenant/{account_id}/...
        $path = $request->path();
        if (preg_match('/^api\/tenant\/(\d+)/', $path, $matches)) {
            return $matches[1];
        }

        // 4. Verificar na URL - padrão /{account_id}/api/...
        if (preg_match('/^(\d+)\/api/', $path, $matches)) {
            return $matches[1];
        }

        // 5. Verificar subdomínio - padrão 1000.domain.com
        $host = $request->getHost();
        if (preg_match('/^(\d+)\./', $host, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
