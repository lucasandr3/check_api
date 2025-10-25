<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('admin.menus');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menuId = $this->route('menu')?->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menus')->ignore($menuId)
            ],
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'parent_id' => [
                'nullable',
                'integer',
                'exists:menus,id',
                Rule::notIn([$menuId]) // Evita que um menu seja pai de si mesmo
            ],
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
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
            'name.required' => 'O nome do menu é obrigatório.',
            'name.unique' => 'Este nome de menu já está em uso.',
            'name.max' => 'O nome do menu não pode ter mais de 255 caracteres.',
            'icon.max' => 'O ícone não pode ter mais de 100 caracteres.',
            'route.max' => 'A rota não pode ter mais de 255 caracteres.',
            'parent_id.exists' => 'O menu pai selecionado não existe.',
            'parent_id.not_in' => 'Um menu não pode ser pai de si mesmo.',
            'order.integer' => 'A ordem deve ser um número inteiro.',
            'order.min' => 'A ordem deve ser maior ou igual a 0.',
            'is_active.boolean' => 'O status ativo deve ser verdadeiro ou falso.',
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
            'name' => 'nome do menu',
            'icon' => 'ícone',
            'route' => 'rota',
            'parent_id' => 'menu pai',
            'order' => 'ordem',
            'is_active' => 'status ativo',
            'roles' => 'roles'
        ];
    }
}
