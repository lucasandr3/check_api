<?php

namespace App\Listeners;

use App\Events\ServiceStatusUpdated;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $whatsappService;

    /**
     * Create the event listener.
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Handle the event.
     */
    public function handle(ServiceStatusUpdated $event): void
    {
        try {
            $service = $event->service;
            $client = $service->client;
            $vehicle = $service->vehicle;

            if (!$client || !$vehicle) {
                Log::warning('Cliente ou veículo não encontrado para notificação WhatsApp', [
                    'service_id' => $service->id,
                ]);
                return;
            }

            $vehicleInfo = "{$vehicle->brand} {$vehicle->model} ({$vehicle->plate})";
            
            $success = $this->whatsappService->sendServiceStatusUpdate(
                $client->phone,
                $client->name,
                $vehicleInfo,
                $event->oldStatus,
                $event->newStatus
            );

            if ($success) {
                Log::info('Notificação WhatsApp enviada com sucesso', [
                    'service_id' => $service->id,
                    'client_phone' => $client->phone,
                    'old_status' => $event->oldStatus,
                    'new_status' => $event->newStatus,
                ]);
            } else {
                Log::error('Falha ao enviar notificação WhatsApp', [
                    'service_id' => $service->id,
                    'client_phone' => $client->phone,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar notificação WhatsApp', [
                'service_id' => $event->service->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
