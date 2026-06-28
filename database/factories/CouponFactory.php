<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word() . ' Discount',
            'code' => strtoupper(Str::random(8)),
            'type' => 1, // 1: Amount discount, 2: Percentage discount
            'value' => 1000,
            'show' => 1,
            'limit_use' => null, // Unlimited
            'limit_use_with_user' => 1,
            'limit_plan_ids' => null,
            'limit_period' => null,
            'started_at' => time() - 3600,
            'ended_at' => time() + 86400 * 30, // Valid for 30 days
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }

    public function percentage()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 2,
                'value' => 20, // 20% off
            ];
        });
    }

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'ended_at' => time() - 3600, // Expired 1 hour ago
            ];
        });
    }
}
