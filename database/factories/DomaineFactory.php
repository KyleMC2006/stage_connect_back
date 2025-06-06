<?php

namespace Database\Factories;
use App\Models\Domaine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domaine>
 */
class DomaineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * 
     */


    protected $model = App\Models\Domaine::class;

    public function definition(): array
    {
        return [
            'libdomaine' => $this->faker->word(),
        ];
    }
}
