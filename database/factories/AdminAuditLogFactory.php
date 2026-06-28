<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminAuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->create(['is_admin' => 1])->id,
            'action' => 'user.update',
            'method' => 'POST',
            'uri' => '/api/v2/admin/user/update',
            'request_data' => null,
            'ip' => $this->faker->ipv4(),
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
