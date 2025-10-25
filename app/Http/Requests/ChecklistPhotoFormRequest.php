<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistPhotoFormRequest extends FormRequest
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
            'photos' => 'required|array|min:1|max:10',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'description' => 'nullable|string|max:500'
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
            'photos.required' => 'As fotos são obrigatórias.',
            'photos.array' => 'As fotos devem ser enviadas como um array.',
            'photos.min' => 'Pelo menos uma foto deve ser enviada.',
            'photos.max' => 'Máximo de 10 fotos permitidas.',
            'photos.*.required' => 'Cada foto é obrigatória.',
            'photos.*.image' => 'Cada arquivo deve ser uma imagem.',
            'photos.*.mimes' => 'As imagens devem ser do tipo: jpeg, png, jpg ou gif.',
            'photos.*.max' => 'Cada foto não pode ter mais de 5MB.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.'
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
            'photos' => 'fotos',
            'photos.*' => 'foto',
            'description' => 'descrição'
        ];
    }
}
