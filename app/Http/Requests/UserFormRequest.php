<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('admin.users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => $this->isMethod('PUT') ? 'sometimes|string|min:6' : 'required|string|min:6',
            'tenant_id' => 'required|exists:tenants,id',
            'office_id' => 'required|exists:offices,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
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
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser válido.',
            'email.unique' => 'Este email já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'tenant_id.required' => 'O tenant é obrigatório.',
            'tenant_id.exists' => 'O tenant selecionado não existe.',
            'office_id.required' => 'A oficina é obrigatória.',
            'office_id.exists' => 'A oficina selecionada não existe.',
            'roles.array' => 'Os roles devem ser um array.',
            'roles.*.exists' => 'Um dos roles selecionados não existe.'
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
            'name' => 'nome',
            'email' => 'email',
            'password' => 'senha',
            'tenant_id' => 'tenant',
            'office_id' => 'oficina',
            'roles' => 'roles'
        ];
    }
}
