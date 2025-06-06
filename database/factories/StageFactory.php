<?php

namespace Database\Factories;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stage>
 */
class StageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Stage::class;

    public function definition(): array
    {
        $dateDebut = $this->faker->dateTimeBetween('-2 months', '+1 month');
        $dateFin = $this->faker->dateTimeBetween($dateDebut, '+3 months');

        return [
            'etudiant_id' => \App\Models\Etudiant::inRandomOrder()->first()?->id,
            'offre_id' => \App\Models\Offre::inRandomOrder()->first()?->id,
            'tuteur_stage_id' => \App\Models\TuteurStage::inRandomOrder()->first()?->id,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'statut' => $this->faker->randomElement(['en_cours', 'termine', 'suspendu']),
            'rapport_stage' => $this->faker->optional(0.3)->sentence(),
            'note_stage' => $this->faker->optional(0.4)->numberBetween(10, 20),
            'commentaire_note' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
