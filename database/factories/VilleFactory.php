<?php

namespace Database\Factories;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ville>
 */
class VilleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\Ville::class;
     
    public function definition(): array
    {
        return [
            'nom_ville' => $this->faker->unique()->word(),
        ];
    }
}
