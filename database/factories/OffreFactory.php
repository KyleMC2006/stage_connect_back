<?php

namespace Database\Factories;
use App\Models\Offre;
use App\Models\Entreprise;
use App\Models\Domaine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offre>
 */
class OffreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entreprise_id' => Entreprise::inRandomOrder()->first()?->id ?? 1,
            'titre' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(),
            'domaine_id' => Domaine::inRandomOrder()->first()?->id ?? 1,
            'adresse' => $this->faker->unique()->address(),
            'date_expiration' => $this->faker->dateTimeBetween('+7 days','+2 months'),
            'duree_en_semaines' => $this->faker->numberBetween(1,12),
            'date_debut' => now()-> addDays(80), 
            'statut' => $this->faker->randomElement(['active','expiree']),

        ];
    }
}
