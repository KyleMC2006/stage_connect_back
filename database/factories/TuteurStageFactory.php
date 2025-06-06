<?php

namespace Database\Factories;
use App\Models\TuteurStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TuteurStage>
 */
class TuteurStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = TuteurStage::class;

    public function definition(): array
    {
        return [
            'nom_tuteur' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'contact' => $this->faker->randomElement([
                $this->faker->phoneNumber(),
                $this->faker->email(),
            ]),
            'poste' => $this->faker->randomElement([
                'Directeur RH', 'Chef de Projet', 'Manager', 'Superviseur', 'Coordinateur'
            ]),
            'entreprise_id' => \App\Models\Entreprise::inRandomOrder()->first()?->id,
        ];
    }
}
