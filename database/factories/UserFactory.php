<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->unique()->numerify('09########'), // شماره موبایل ایرانی
            'grade' => $this->faker->numberBetween(1, 12),
            'is_completed' => $this->faker->boolean(20), // 20% true
            'password' => bcrypt('password'), // یا $this->faker->password(8, 12)
            'favorite_subjects' => $this->faker->randomElement([
                null,
                json_encode(['math', 'physics']),
                json_encode(['literature', 'history']),
                json_encode(['biology', 'chemistry']),
            ]),
            'role' => $this->faker->randomElement(['student', 'admin']),
            'subscription_type' => $this->faker->randomElement(['free', 'pro']),
        ];
    }

    /**
     * Indicate that the user should be an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user should be a pro subscriber.
     */
    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_type' => 'pro',
        ]);
    }

    /**
     * Indicate that the user is a completed student.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
        ]);
    }
}