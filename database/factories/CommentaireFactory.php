<?php

namespace Database\Factories;
use App\Models\Commentaire;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Commentaire>
 */
class CommentaireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Commentaire::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()?->id,
            'profil_commus_id' => \App\Models\ProfilCommu::inRandomOrder()->first()?->id,
            'comment' => $this->faker->sentence(8),
        ];
    }
}
