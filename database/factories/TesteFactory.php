<?php

namespace Database\Factories;

use App\Models\Teste;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teste>
 */
class TesteFactory extends Factory
{
    protected $model = Teste::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(2, true),
            'descricao' => fake()->optional()->sentence(),
            'status' => true,
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn (): array => ['status' => false]);
    }
}
