<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'server_rate' => 1.0,
            'u' => $this->faker->numberBetween(0, 1024 * 1024 * 100),
            'd' => $this->faker->numberBetween(0, 1024 * 1024 * 100),
            'record_type' => 'd',
            'record_at' => time(),
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
