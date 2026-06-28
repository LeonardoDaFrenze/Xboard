<?php

namespace Database\Factories;

use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InviteCodeFactory extends Factory
{
    protected $model = InviteCode::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => Str::random(10),
            'status' => 0,
            'pv' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
