<?php

namespace Database\Factories;
use App\Models\FilAnnee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FilAnnee>
 */
class FilAnneeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = FilAnnee::class;

    public function definition(): array
    {
        return [
            'id_fil' => \App\Models\Filiere::inRandomOrder()->first()?->id,
            'id_annee' => \App\Models\Annee::inRandomOrder()->first()?->id,
        ];
    }
}
