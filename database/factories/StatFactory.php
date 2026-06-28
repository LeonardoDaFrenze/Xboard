<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'record_at' => time(),
            'record_type' => 'd',
            'order_count' => $this->faker->numberBetween(0, 100),
            'order_total' => $this->faker->numberBetween(0, 100000),
            'commission_count' => $this->faker->numberBetween(0, 20),
            'commission_total' => $this->faker->numberBetween(0, 10000),
            'paid_count' => $this->faker->numberBetween(0, 50),
            'paid_total' => $this->faker->numberBetween(0, 50000),
            'register_count' => $this->faker->numberBetween(0, 20),
            'invite_count' => $this->faker->numberBetween(0, 10),
            'transfer_used_total' => '0',
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
