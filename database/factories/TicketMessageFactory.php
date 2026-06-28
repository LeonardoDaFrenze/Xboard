<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'ticket_id' => Ticket::factory(),
            'message' => $this->faker->paragraph(),
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
