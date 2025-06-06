<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Filiere;
use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Etudiant>
 */
class EtudiantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Models\Etudiant::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'id_filiere' => Filiere::inRandomOrder()->first()?->id,
            'id_etablissement' => Etablissement::factory(),
            'matricule' => $this->faker->unique()->bothify('ETU####??'),
            'projets' => $this->faker->optional()->paragraphs(2, true),
            'competences' => $this->faker->optional()->words(5, true),
            'CV' => $this->faker->optional()->url(),
            'parcours' => $this->faker->optional()->paragraph(),
        ];
    }
}
