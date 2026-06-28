<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\GiftCardUsage
 *
 * @property int $id
 * @property int $code_id Redemption CodeID
 * @property int $template_id TemplateID
 * @property int $user_id User UsingID
 * @property int|null $invite_user_id InviterID
 * @property array $rewards_given Actual Rewards Distributed
 * @property array|null $invite_rewards Rewards Received by Inviter
 * @property int|null $user_level_at_use User Level at Use
 * @property int|null $plan_id_at_use User Package at UseID
 * @property float $multiplier_applied Multiplication Rate Applied
 * @property string|null $ip_address UseIPAddress
 * @property string|null $user_agent User Agent
 * @property string|null $notes Remarks
 * @property int $created_at
 */
class GiftCardUsage extends Model
{
    protected $table = 'v2_gift_card_usage';
    protected $dateFormat = 'U';
    public $timestamps = false;

    protected $fillable = [
        'code_id',
        'template_id',
        'user_id',
        'invite_user_id',
        'rewards_given',
        'invite_rewards',
        'user_level_at_use',
        'plan_id_at_use',
        'multiplier_applied',
        'ip_address',
        'user_agent',
        'notes',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'timestamp',
        'rewards_given' => 'array',
        'invite_rewards' => 'array',
        'multiplier_applied' => 'float'
    ];

    /**
     * Associated Redemption Code
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(GiftCardCode::class, 'code_id');
    }

    /**
     * Associated Template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(GiftCardTemplate::class, 'template_id');
    }

    /**
     * Associated User Using
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Associated Inviter
     */
    public function inviteUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invite_user_id');
    }

    /**
     * Create Usage Record
     */
    public static function createRecord(
        GiftCardCode $code,
        User $user,
        array $rewards,
        array $options = []
    ): self {
        return self::create([
            'code_id' => $code->id,
            'template_id' => $code->template_id,
            'user_id' => $user->id,
            'invite_user_id' => $user->invite_user_id,
            'rewards_given' => $rewards,
            'invite_rewards' => $options['invite_rewards'] ?? null,
            'user_level_at_use' => $user->plan ? $user->plan->sort : null,
            'plan_id_at_use' => $user->plan_id,
            'multiplier_applied' => $options['multiplier'] ?? 1.0,
            // 'ip_address' => $options['ip_address'] ?? null,
            'user_agent' => $options['user_agent'] ?? null,
            'notes' => $options['notes'] ?? null,
            'created_at' => time(),
        ]);
    }
} 