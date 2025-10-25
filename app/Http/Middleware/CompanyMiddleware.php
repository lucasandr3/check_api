<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tentar obter company_id de diferentes fontes
        $companyId = $request->header('X-Company-ID') 
                  ?? $request->query('company_id')
                  ?? $request->route('company_id');
        
        // Se não encontrou na requisição, tentar do usuário autenticado
        if (!$companyId && auth()->check()) {
            $companyId = auth()->user()->company_id;
        }
        
        // Definir company_id atual no contexto da aplicação
        if ($companyId) {
            app()->instance('current_company_id', $companyId);
        }
        
        return $next($request);
    }
}
