<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obter o tenant atual
        $currentTenant = Tenant::current();
        $tenantId = $currentTenant ? $currentTenant->id : '1000';

        return [
            'tenant_id' => $tenantId,
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'cnpj' => $this->faker->unique()->numerify('##.###.###/####-##'),
        ];
    }

    /**
     * Indicate that the company is a main company.
     */
    public function main(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Empresa Principal',
            'email' => 'contato@empresaprincipal.com',
        ]);
    }

    /**
     * Indicate that the company is a branch.
     */
    public function branch(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Filial ' . $this->faker->city(),
            'email' => 'filial@empresaprincipal.com',
        ]);
    }
}
