<?php

namespace Database\Factories;

use App\Models\TrackingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrackingSession>
 */
class TrackingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone_number' => $this->faker->e164PhoneNumber(),
            'token_hash' => Hash::make(Str::random(64)),
            'label' => $this->faker->optional()->name(),
            'status' => 'pending',
            'expires_at' => now()->addHour(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate the session is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }

    /**
     * Indicate the session has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subHour(),
        ]);
    }
}
