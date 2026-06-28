<?php

namespace Database\Factories;

use App\Models\GiftCardTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class GiftCardTemplateFactory extends Factory
{
    protected $model = GiftCardTemplate::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'type' => 1,
            'status' => 1,
            'conditions' => null,
            'rewards' => ['balance' => 100],
            'limits' => null,
            'special_config' => null,
            'icon' => null,
            'theme_color' => '#123456',
            'sort' => 0,
            'admin_id' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
