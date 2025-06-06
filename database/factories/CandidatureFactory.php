<?php

namespace Database\Factories;
use App\Models\Candidature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidature>
 */
class CandidatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
     protected $model = Candidature::class;

    public function definition(): array
    {
        return [
            'etudiant_id' => \App\Models\Etudiant::inRandomOrder()->first()?->id,
            'offre_id' => \App\Models\Offre::inRandomOrder()->first()?->id,
            'statut' => $this->faker->randomElement(['en_attente', 'acceptee', 'refusee']),
            'date_postulat' => $this->faker->dateTimeBetween('-1 months', 'now'),
            'lettre_motivation' => $this->faker->paragraphs(2, true),
        ];
    }
}
