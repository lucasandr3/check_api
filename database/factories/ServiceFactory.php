<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tenant;
use App\Models\Office;
use App\Models\Vehicle;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['Manutenção', 'Revisão', 'Troca de óleo', 'Alinhamento', 'Balanceamento', 'Freios', 'Suspensão'];
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        
        return [
            'tenant_id' => Tenant::factory(),
            'office_id' => Office::factory(),
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(), // mecânico responsável
            'status' => fake()->randomElement($statuses),
            'type' => fake()->randomElement($types),
            'description' => fake()->paragraph(),
            'estimated_cost' => fake()->randomFloat(2, 50, 500),
            'final_cost' => fake()->optional()->randomFloat(2, 50, 500),
            'start_date' => fake()->optional()->date(),
            'end_date' => fake()->optional()->date(),
            'observations' => fake()->optional()->sentence(),
        ];
    }
}
