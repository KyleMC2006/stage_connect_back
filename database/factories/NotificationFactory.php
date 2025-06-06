<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Notification::class; 

    public function definition(): array
    {
        return [
            'user_id' => User::where('role','entreprise')->inRandomOrder()->first()->id,
            'type' => fake()->randomElement(['confirmation','desistement']),
            'message' => fake()->paragraph(),
            'donnees_sup' => [
                'offre_id' => fake()->numberBetween(1, 4),
                'candidature_id' => fake()->numberBetween(1, 5),
            ],
        ];
    }
}
