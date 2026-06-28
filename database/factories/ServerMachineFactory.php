<?php

namespace Database\Factories;

use App\Models\ServerMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerMachineFactory extends Factory
{
    protected $model = ServerMachine::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'token' => ServerMachine::generateToken(),
            'notes' => $this->faker->sentence,
            'is_active' => true,
            'last_seen_at' => time(),
            'load_status' => ['cpu' => 10, 'mem' => 50],
        ];
    }
}
