<?php

namespace Database\Factories;
use App\Models\EcoleFil;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EcoleFil>
 */
class EcoleFilFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = EcoleFil::class;

    public function definition(): array
    {
        return [
            'id_etablissement' => \App\Models\Etablissement::inRandomOrder()->first()?->id,
            'id_filiere' => \App\Models\Filiere::inRandomOrder()->first()?->id,
        ];
    }
}
