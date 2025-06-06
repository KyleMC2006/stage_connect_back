<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Etablissement>
 */
class EtablissementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\Etablissement::class;
    
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nom_etablissement' => $this->faker->city(),
            'siteweb' => $this->faker->unique()->url(),
            'adresse' => $this->faker->unique()->address(),
            'ville_id' =>  Ville::inRandomOrder()->first()?->id ?? 1,

        ];
    }
}
