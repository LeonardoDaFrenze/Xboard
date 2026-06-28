<?php

namespace Database\Factories;

use App\Models\TrafficResetLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrafficResetLogFactory extends Factory
{
    protected $model = TrafficResetLog::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'reset_type' => TrafficResetLog::TYPE_MONTHLY,
            'reset_time' => now(),
            'old_upload' => $this->faker->numberBetween(0, 1000000),
            'old_download' => $this->faker->numberBetween(0, 1000000),
            'old_total' => $this->faker->numberBetween(0, 2000000),
            'new_upload' => 0,
            'new_download' => 0,
            'new_total' => 0,
            'trigger_source' => TrafficResetLog::SOURCE_AUTO,
            'metadata' => null,
        ];
    }
}
