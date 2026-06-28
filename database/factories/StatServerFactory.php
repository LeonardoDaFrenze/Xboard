<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatServerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'server_id' => $this->faker->numberBetween(1, 100),
            'server_type' => 'shadowsocks',
            'u' => $this->faker->numberBetween(0, 1024 * 1024 * 100),
            'd' => $this->faker->numberBetween(0, 1024 * 1024 * 100),
            'record_type' => 'd',
            'record_at' => time(),
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
