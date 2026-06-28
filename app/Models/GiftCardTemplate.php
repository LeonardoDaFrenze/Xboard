<?php

namespace App\Models;

use Dflydev\DotAccessData\Data;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\GiftCardTemplate
 *
 * @property int $id
 * @property string $name Gift Card Name
 * @property string|null $description Gift Card Name
 * @property int $type Gift Card Description
 * @property boolean $status Card Type
 * @property array|null $conditions Status
 * @property array $rewards Usage Condition Configuration
 * @property array|null $limits Reward Configuration
 * @property array|null $special_config Restrictions
 * @property string|null $icon Special Configuration
 * @property string $theme_color Card Icon
 * @property int $sort Theme Color
 * @property int $admin_id Sorting OrderID
 * @property int $created_at
 * @property int $updated_at
 */
class GiftCardTemplate extends Model
{
    use HasFactory;

    protected $table = 'v2_gift_card_template';
    protected $dateFormat = 'U';

// Card Type Constants
    const TYPE_GENERAL = 1;         // General Gift Card
    const TYPE_PLAN = 2;            // Package Gift Card
    const TYPE_MYSTERY = 3;         // Blind Box Gift Card

    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'conditions',
        'rewards',
        'limits',
        'special_config',
        'icon',
        'background_image',
        'theme_color',
        'sort',
        'admin_id'
    ];

    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'conditions' => 'array',
        'rewards' => 'array',
        'limits' => 'array',
        'special_config' => 'array',
        'status' => 'boolean'
    ];

    /**
     * Get Card Type Mapping
     */
    public static function getTypeMap(): array
    {
        return [
            self::TYPE_GENERAL => 'General Gift Card',
            self::TYPE_PLAN => 'Package Gift Card',
            self::TYPE_MYSTERY => 'Blind Box Gift Card',
        ];
    }

    /**
     * Get Type Name
     */
    public function getTypeNameAttribute(): string
    {
        return self::getTypeMap()[$this->type] ?? 'Unknown Type';
    }

    /**
     * Associate Redeem Code
     */
    public function codes(): HasMany
    {
        return $this->hasMany(GiftCardCode::class, 'template_id');
    }

    /**
     * Associate Usage Record
     */
    public function usages(): HasMany
    {
        return $this->hasMany(GiftCardUsage::class, 'template_id');
    }

    /**
     * Associate Statistics Data
     */
    public function stats(): HasMany
    {
        return $this->hasMany(GiftCardUsage::class, 'template_id');
    }

    /**
     * Check Availability
     */
    public function isAvailable(): bool
    {
        return $this->status;
    }

    /**
     * Check if User Meets Usage Conditions
     */
    public function checkUserConditions(User $user): bool
    {
        switch ($this->type) {
            case self::TYPE_GENERAL:
                $rewards = $this->rewards ?? [];
                if (isset($rewards['transfer_enable']) || isset($rewards['expire_days']) || isset($rewards['reset_package'])) {
                    if (!$user->plan_id) {
                        return false;
                    }
                }
                break;
            case self::TYPE_PLAN:
                if ($user->isActive()) {
                    return false;
                }
                break;
        }

        $conditions = $this->conditions ?? [];

// Check New User Condition
        if (isset($conditions['new_user_only']) && $conditions['new_user_only']) {
            $maxDays = $conditions['new_user_max_days'] ?? 7;
            if ($user->created_at < (time() - ($maxDays * 86400))) {
                return false;
            }
        }

// Check Paid User Condition
        if (isset($conditions['paid_user_only']) && $conditions['paid_user_only']) {
            $paidOrderExists = $user->orders()->where('status', Order::STATUS_COMPLETED)->exists();
            if (!$paidOrderExists) {
                return false;
            }
        }

// Check Allowed Packages
        if (isset($conditions['allowed_plans']) && $user->plan_id) {
            if (!in_array($user->plan_id, $conditions['allowed_plans'])) {
                return false;
            }
        }

// Check if Referral is Required
        if (isset($conditions['require_invite']) && $conditions['require_invite']) {
            if (!$user->invite_user_id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate Actual Reward
     */
    public function calculateActualRewards(User $user): array
    {
        $baseRewards = $this->rewards;
        $actualRewards = $baseRewards;

// Handle Blind Box Random Rewards
        if ($this->type === self::TYPE_MYSTERY && isset($this->rewards['random_rewards'])) {
            $randomRewards = $this->rewards['random_rewards'];
            $totalWeight = array_sum(array_column($randomRewards, 'weight'));
            $random = mt_rand(1, $totalWeight);
            $currentWeight = 0;

            foreach ($randomRewards as $reward) {
                $currentWeight += $reward['weight'];
                if ($random <= $currentWeight) {
                    $actualRewards = array_merge($actualRewards, $reward);
                    unset($actualRewards['weight']);
                    break;
                }
            }
        }

// Handle Special Rewards like Festivals (General Logic)
        if (isset($this->special_config['festival_bonus'])) {
            $now = time();
            $festivalConfig = $this->special_config;

            if (isset($festivalConfig['start_time']) && isset($festivalConfig['end_time'])) {
                if ($now >= $festivalConfig['start_time'] && $now <= $festivalConfig['end_time']) {
                    $bonus = data_get($festivalConfig, 'festival_bonus', 1.0);
                    if ($bonus > 1.0) {
                        foreach ($actualRewards as $key => &$value) {
                            if (is_numeric($value)) {
                                $value = intval($value * $bonus);
                            }
                        }
                        unset($value); // Dereference
                    }
                }
            }
        }

        return $actualRewards;
    }

    /**
     * Check Usage Frequency Limit
     */
    public function checkUsageLimit(User $user): bool
    {
        $limits = $this->limits ?? [];

// Check Maximum Usage Per User
        if (isset($limits['max_use_per_user'])) {
            $usedCount = $this->usages()
                ->where('user_id', $user->id)
                ->count();
            if ($usedCount >= $limits['max_use_per_user']) {
                return false;
            }
        }

// Check Cooldown Time
        if (isset($limits['cooldown_hours'])) {
            $lastUsage = $this->usages()
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastUsage && isset($lastUsage->created_at)) {
                $cooldownTime = $lastUsage->created_at + ($limits['cooldown_hours'] * 3600);
                if (time() < $cooldownTime) {
                    return false;
                }
            }
        }

        return true;
    }
}