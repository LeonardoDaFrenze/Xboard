<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word() . ' Plan',
            'transfer_enable' => 100 * 1024 * 1024 * 1024, // 100 GB
            'show' => 1,
            'renew' => 1,
            'sell' => 1,
            'sort' => $this->faker->numberBetween(1, 100),
            'reset_traffic_method' => Plan::RESET_TRAFFIC_MONTHLY,
            'prices' => [
                Plan::PERIOD_MONTHLY => 1000,
                Plan::PERIOD_QUARTERLY => 2800,
                Plan::PERIOD_YEARLY => 10000,
            ],
            'capacity_limit' => null,
            'tags' => ['Pro', 'Fast'],
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }

    public function hidden()
    {
        return $this->state(function (array $attributes) {
            return [
                'show' => 0,
                'sell' => 0,
            ];
        });
    }
}
