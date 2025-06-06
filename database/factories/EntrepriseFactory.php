<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Entreprise;
use App\Models\Domaine;
use App\Models\Ville;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entreprise>
 */
class EntrepriseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\Entreprise::class;

    public function definition(): array 
    {
        return [
            'user_id' => User::factory(),
            'nom_entreprise' => $this->faker->unique()->company(),
            'email_entreprise' => $this->faker->unique()->safeEmail(),
            'siteweb' => $this->faker->unique()->word(),
            'adresse' => $this->faker->unique()->address(),
            'ville_id' =>  Ville::inRandomOrder()->first()?->id ?? 1,
            'RCCM' => $this->faker->unique()->word(),
            'id_domaine' => Domaine::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}
