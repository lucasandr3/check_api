<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tenant;
use App\Models\Office;
use App\Models\Client;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Volkswagen', 'Fiat', 'Hyundai', 'Renault'];
        $models = ['Corolla', 'Civic', 'Focus', 'Onix', 'Gol', 'Uno', 'HB20', 'Sandero'];
        $colors = ['Branco', 'Preto', 'Prata', 'Vermelho', 'Azul', 'Verde', 'Cinza'];
        
        return [
            'tenant_id' => Tenant::factory(),
            'office_id' => Office::factory(),
            'client_id' => Client::factory(),
            'brand' => fake()->randomElement($brands),
            'model' => fake()->randomElement($models),
            'year' => fake()->numberBetween(2010, 2024),
            'color' => fake()->randomElement($colors),
            'plate' => fake()->regexify('[A-Z]{3}[0-9]{4}'), // Brazilian plate format
            'chassis' => fake()->regexify('[A-Z0-9]{17}'), // VIN format
            'observations' => fake()->optional()->sentence(),
        ];
    }
}
