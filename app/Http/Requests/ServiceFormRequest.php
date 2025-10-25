<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('services.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'user_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'observations' => 'nullable|string'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'O veículo é obrigatório.',
            'vehicle_id.exists' => 'O veículo selecionado não existe.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'status.in' => 'O status deve ser: pendente, em andamento, concluído ou cancelado.',
            'type.required' => 'O tipo de serviço é obrigatório.',
            'type.max' => 'O tipo de serviço não pode ter mais de 255 caracteres.',
            'description.required' => 'A descrição é obrigatória.',
            'estimated_cost.numeric' => 'O custo estimado deve ser um número.',
            'estimated_cost.min' => 'O custo estimado deve ser maior ou igual a 0.',
            'start_date.date' => 'A data de início deve ser uma data válida.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'vehicle_id' => 'veículo',
            'user_id' => 'usuário responsável',
            'status' => 'status',
            'type' => 'tipo de serviço',
            'description' => 'descrição',
            'estimated_cost' => 'custo estimado',
            'start_date' => 'data de início',
            'observations' => 'observações'
        ];
    }
}
