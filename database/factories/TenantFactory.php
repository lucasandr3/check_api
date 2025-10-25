<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->company();
        $domain = strtolower(str_replace([' ', '&', ',', '.'], ['', 'e', '', ''], $companyName)) . '.fixcar.com';
        $database = 'tenant_' . strtolower(str_replace([' ', '&', ',', '.'], ['_', 'e', '', ''], $companyName));
        
        return [
            'name' => $companyName,
            'domain' => $domain,
            'database' => $database,
            'data' => [
                'company_info' => [
                    'cnpj' => fake()->numerify('##.###.###/####-##'),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->companyEmail(),
                    'address' => fake()->address()
                ],
                'settings' => [
                    'timezone' => 'America/Sao_Paulo',
                    'currency' => 'BRL',
                    'language' => 'pt_BR'
                ]
            ],
        ];
    }
}
