<?php

namespace Database\Factories;
use App\Models\Annee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Annee>
 */
class AnneeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = App\Models\Annee::class;

    public function definition(): array
    {
        return [
            'libannee' => $this->faker->word(),
        ];
    }
}
