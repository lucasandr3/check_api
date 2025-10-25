<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tenant;
use App\Models\Office;
use App\Models\Service;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checklist>
 */
class ChecklistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'completed'];
        
        // Exemplo de itens do checklist
        $checklistItems = [
            'Verificação de óleo do motor',
            'Verificação de nível de água',
            'Verificação de pneus',
            'Verificação de freios',
            'Verificação de suspensão',
            'Verificação de iluminação',
            'Verificação de bateria',
            'Verificação de filtros'
        ];
        
        return [
            'tenant_id' => Tenant::factory(),
            'office_id' => Office::factory(),
            'service_id' => Service::factory(),
            'user_id' => User::factory(), // mecânico que fez o checklist
            'status' => fake()->randomElement($statuses),
            'items' => fake()->randomElements($checklistItems, fake()->numberBetween(5, 8)),
            'observations' => fake()->optional()->sentence(),
        ];
    }
}
