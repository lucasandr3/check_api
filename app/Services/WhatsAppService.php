<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.evolution.api_key');
        $this->baseUrl = config('services.evolution.base_url', 'https://api.evolution.com.br');
    }

    /**
     * Enviar mensagem via WhatsApp
     */
    public function sendMessage(string $phone, string $message): bool
    {
        try {
            // Simular envio para Evolution API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/message/sendText', [
                'number' => $this->formatPhone($phone),
                'text' => $message,
            ]);

            if ($response->successful()) {
                Log::info('Mensagem WhatsApp enviada com sucesso', [
                    'phone' => $phone,
                    'message' => $message,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('Erro ao enviar mensagem WhatsApp', [
                'phone' => $phone,
                'message' => $message,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar mensagem WhatsApp', [
                'phone' => $phone,
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enviar mensagem de atualização de status do serviço
     */
    public function sendServiceStatusUpdate(string $phone, string $clientName, string $vehicleInfo, string $oldStatus, string $newStatus): bool
    {
        $statusMessages = [
            'pending' => 'pendente',
            'in_progress' => 'em andamento',
            'completed' => 'concluído',
            'cancelled' => 'cancelado',
        ];

        $oldStatusPt = $statusMessages[$oldStatus] ?? $oldStatus;
        $newStatusPt = $statusMessages[$newStatus] ?? $newStatus;

        $message = "Olá {$clientName}! 👋\n\n";
        $message .= "O status do seu serviço para o veículo {$vehicleInfo} foi atualizado:\n";
        $message .= "📊 Status anterior: {$oldStatusPt}\n";
        $message .= "✅ Novo status: {$newStatusPt}\n\n";

        if ($newStatus === 'completed') {
            $message .= "🎉 Seu serviço foi concluído com sucesso!\n";
            $message .= "Você pode retirar seu veículo na oficina.\n\n";
        } elseif ($newStatus === 'in_progress') {
            $message .= "🔧 Seu serviço está sendo executado.\n";
            $message .= "Em breve entraremos em contato com mais novidades.\n\n";
        }

        $message .= "Obrigado por escolher nossos serviços! 🚗✨";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Enviar notificação de checklist concluído
     */
    public function sendChecklistCompleted(string $phone, string $clientName, string $vehicleInfo): bool
    {
        $message = "Olá {$clientName}! 👋\n\n";
        $message .= "✅ O checklist do seu veículo {$vehicleInfo} foi concluído!\n\n";
        $message .= "📋 Todos os itens foram verificados e documentados.\n";
        $message .= "📸 Fotos foram tiradas para registro.\n\n";
        $message .= "Em breve você receberá um relatório completo.\n";
        $message .= "Obrigado pela confiança! 🚗✨";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Formatar número de telefone para padrão internacional
     */
    protected function formatPhone(string $phone): string
    {
        // Remove caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Adiciona código do país se não existir
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '11') {
            $phone = '55' . $phone;
        } elseif (strlen($phone) === 10) {
            $phone = '5511' . $phone;
        }

        return $phone;
    }
}
