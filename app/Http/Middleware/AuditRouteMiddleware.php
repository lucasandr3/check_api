<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditRouteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Auditoria apenas para métodos que modificam dados
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->auditRoute($request, $response);
        }

        return $response;
    }

    /**
     * Registrar auditoria da rota
     */
    private function auditRoute(Request $request, Response $response): void
    {
        try {
            $user = Auth::user();
            $routeName = $request->route()?->getName();
            
            // Pular auditoria para rotas específicas se necessário
            if ($this->shouldSkipAudit($routeName)) {
                return;
            }

            // Determinar tipo de evento baseado no método HTTP
            $eventType = $this->getEventTypeFromMethod($request->method());
            
            // Obter dados da requisição
            $requestData = $this->getRequestData($request);
            
            // Obter dados da resposta
            $responseData = $this->getResponseData($response);

            // Criar log de auditoria de forma assíncrona
            dispatch(function () use ($request, $response, $user, $routeName, $eventType, $requestData, $responseData) {
                AuditLog::create([
                    'event_type' => $eventType,
                    'auditable_type' => 'Route',
                    'auditable_id' => 0,
                    'user_id' => $user?->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route_name' => $routeName,
                    'method' => $request->method(),
                    'description' => "Acesso à rota: {$routeName}",
                    'old_values' => null,
                    'new_values' => [
                        'request_data' => $requestData,
                        'response_status' => $response->getStatusCode(),
                        'response_data' => $responseData
                    ],
                    'metadata' => [
                        'model_class' => 'Route',
                        'event_type' => $eventType,
                        'timestamp' => now()->toISOString(),
                        'url' => $request->fullUrl(),
                        'params' => $request->all(),
                        'headers' => $this->getSafeHeaders($request)
                    ]
                ]);
            })->afterResponse();

        } catch (\Exception $e) {
            // Log do erro mas não interromper a execução
            \Log::error('Erro ao criar auditoria de rota', [
                'error' => $e->getMessage(),
                'route' => $request->route()?->getName(),
                'method' => $request->method()
            ]);
        }
    }

    /**
     * Verificar se deve pular a auditoria
     */
    private function shouldSkipAudit(?string $routeName): bool
    {
        if (!$routeName) {
            return true;
        }

        // Rotas que não precisam de auditoria
        $skipRoutes = [
            'audit.logs',
            'audit.statistics',
            'audit.models',
            'audit.export'
        ];

        return in_array($routeName, $skipRoutes);
    }

    /**
     * Obter tipo de evento baseado no método HTTP
     */
    private function getEventTypeFromMethod(string $method): string
    {
        return match($method) {
            'POST' => 'route_create',
            'PUT', 'PATCH' => 'route_update',
            'DELETE' => 'route_delete',
            default => 'route_access'
        };
    }

    /**
     * Obter dados da requisição
     */
    private function getRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remover campos sensíveis
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_token'];
        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }

    /**
     * Obter dados da resposta
     */
    private function getResponseData(Response $response): array
    {
        try {
            $content = $response->getContent();
            $decoded = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            
            return ['raw_content' => substr($content, 0, 1000)]; // Limitar tamanho
        } catch (\Exception $e) {
            return ['error' => 'Não foi possível decodificar resposta'];
        }
    }

    /**
     * Obter headers seguros da requisição
     */
    private function getSafeHeaders(Request $request): array
    {
        $headers = $request->headers->all();
        
        // Remover headers sensíveis
        $sensitiveHeaders = ['authorization', 'cookie', 'x-csrf-token'];
        foreach ($sensitiveHeaders as $header) {
            unset($headers[$header]);
        }
        
        return $headers;
    }
}
