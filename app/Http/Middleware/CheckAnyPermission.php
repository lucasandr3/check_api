<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAnyPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!$request->user() || !$request->user()->hasAnyPermission($permissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Permissão insuficiente.',
                'permissions_required' => $permissions
            ], 403);
        }

        return $next($request);
    }
}
