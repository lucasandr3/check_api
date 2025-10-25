<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfficeFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('admin.offices');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $officeId = $this->route('office')?->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('offices')->ignore($officeId)
            ],
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('offices')->ignore($officeId)
            ],
            'tenant_id' => 'required|exists:tenants,id',
            'is_active' => 'boolean'
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
            'name.required' => 'O nome da oficina é obrigatório.',
            'name.unique' => 'Este nome de oficina já está em uso.',
            'name.max' => 'O nome da oficina não pode ter mais de 255 caracteres.',
            'address.required' => 'O endereço é obrigatório.',
            'address.max' => 'O endereço não pode ter mais de 500 caracteres.',
            'phone.required' => 'O telefone é obrigatório.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser válido.',
            'email.unique' => 'Este email já está em uso.',
            'tenant_id.required' => 'O tenant é obrigatório.',
            'tenant_id.exists' => 'O tenant selecionado não existe.',
            'is_active.boolean' => 'O status ativo deve ser verdadeiro ou falso.'
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
            'name' => 'nome da oficina',
            'address' => 'endereço',
            'phone' => 'telefone',
            'email' => 'email',
            'tenant_id' => 'tenant',
            'is_active' => 'status ativo'
        ];
    }
}
