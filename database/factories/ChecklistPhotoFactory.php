<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tenant;
use App\Models\Checklist;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistPhoto>
 */
class ChecklistPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $descriptions = [
            'Foto do motor',
            'Foto dos pneus',
            'Foto dos freios',
            'Foto da suspensão',
            'Foto da bateria',
            'Foto dos filtros'
        ];
        
        return [
            'tenant_id' => Tenant::factory(),
            'checklist_id' => Checklist::factory(),
            'filename' => fake()->uuid() . '.jpg',
            'path' => 'checklists/' . fake()->uuid() . '.jpg',
            'mime_type' => fake()->randomElement($mimeTypes),
            'size' => fake()->numberBetween(100000, 5000000), // 100KB to 5MB
            'description' => fake()->randomElement($descriptions),
        ];
    }
}
