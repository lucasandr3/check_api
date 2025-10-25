<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('checklists.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'checklist_data' => 'required|array',
            'checklist_data.*.item' => 'required|string|max:255',
            'checklist_data.*.status' => 'required|in:ok,not_ok,na',
            'checklist_data.*.observations' => 'nullable|string',
            'general_observations' => 'nullable|string',
            'technician_signature' => 'nullable|string',
            'client_signature' => 'nullable|string',
            'completed_at' => 'nullable|date'
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
            'service_id.required' => 'O serviço é obrigatório.',
            'service_id.exists' => 'O serviço selecionado não existe.',
            'checklist_data.required' => 'Os dados do checklist são obrigatórios.',
            'checklist_data.array' => 'Os dados do checklist devem ser um array.',
            'checklist_data.*.item.required' => 'O item do checklist é obrigatório.',
            'checklist_data.*.item.max' => 'O item do checklist não pode ter mais de 255 caracteres.',
            'checklist_data.*.status.required' => 'O status do item é obrigatório.',
            'checklist_data.*.status.in' => 'O status deve ser: ok, não ok ou não aplicável.',
            'completed_at.date' => 'A data de conclusão deve ser uma data válida.'
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
            'service_id' => 'serviço',
            'checklist_data' => 'dados do checklist',
            'checklist_data.*.item' => 'item do checklist',
            'checklist_data.*.status' => 'status do item',
            'checklist_data.*.observations' => 'observações do item',
            'general_observations' => 'observações gerais',
            'technician_signature' => 'assinatura do técnico',
            'client_signature' => 'assinatura do cliente',
            'completed_at' => 'data de conclusão'
        ];
    }
}
