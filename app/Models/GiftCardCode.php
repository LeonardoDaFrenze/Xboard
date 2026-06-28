<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\GiftCardCode
 *
 * @property int $id
 * @property int $template_id TemplateID
 * @property GiftCardTemplate $template Associated Template
 * @property string $code Redemption Code
 * @property string|null $batch_id BatchID
 * @property int $status Status
 * @property int|null $user_id User UsedID
 * @property int|null $used_at Usage Time
 * @property int|null $expires_at Expiry Time
 * @property array|null $actual_rewards Actual Reward
 * @property int $usage_count Number of Uses
 * @property int $max_usage Maximum Number of Uses
 * @property array|null $metadata Additional Data
 * @property int $created_at
 * @property int $updated_at
 */
class GiftCardCode extends Model
{
    use HasFactory;

    protected $table = 'v2_gift_card_code';
    protected $dateFormat = 'U';

// Status Constants
    const STATUS_UNUSED = 0;        // Unused
    const STATUS_USED = 1;          // Used
    const STATUS_EXPIRED = 2;       // Expired
    const STATUS_DISABLED = 3;      // Disabled

    protected $fillable = [
        'template_id',
        'code',
        'batch_id',
        'status',
        'user_id',
        'used_at',
        'expires_at',
        'actual_rewards',
        'usage_count',
        'max_usage',
        'metadata'
    ];

    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'used_at' => 'timestamp',
        'expires_at' => 'timestamp',
        'actual_rewards' => 'array',
        'metadata' => 'array'
    ];

    /**
     * Get Status Mapping
     */
    public static function getStatusMap(): array
    {
        return [
            self::STATUS_UNUSED => 'Unused',
            self::STATUS_USED => 'Used',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_DISABLED => 'Disabled',
        ];
    }

    /**
     * Get Status Name
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatusMap()[$this->status] ?? 'Unknown Status';
    }

    /**
     * Associate Gift Card Template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(GiftCardTemplate::class, 'template_id');
    }

    /**
     * Associate User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Associate Usage Record
     */
    public function usages(): HasMany
    {
        return $this->hasMany(GiftCardUsage::class, 'code_id');
    }

    /**
     * Check Availability
     */
    public function isAvailable(): bool
    {
// Check Status
        if (in_array($this->status, [self::STATUS_EXPIRED, self::STATUS_DISABLED])) {
            return false;
        }

// Check if Expired
        if ($this->expires_at && $this->expires_at < time()) {
            return false;
        }

// Check Number of Uses
        if ($this->usage_count >= $this->max_usage) {
            return false;
        }

        return true;
    }

    /**
     * Check if Expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < time();
    }

    /**
     * Mark as Used
     */
    public function markAsUsed(User $user): bool
    {
        $this->status = self::STATUS_USED;
        $this->user_id = $user->id;
        $this->used_at = time();
        $this->usage_count += 1;

        return $this->save();
    }

    /**
     * Mark as Expired
     */
    public function markAsExpired(): bool
    {
        $this->status = self::STATUS_EXPIRED;
        return $this->save();
    }

    /**
     * Mark as Disabled
     */
    public function markAsDisabled(): bool
    {
        $this->status = self::STATUS_DISABLED;
        return $this->save();
    }

    /**
     * Generate Redemption Code
     */
    public static function generateCode(string $prefix = 'GC'): string
    {
        do {
            $safePrefix = (string) $prefix;
            $code = $safePrefix . strtoupper(substr(md5(uniqid($safePrefix . mt_rand(), true)), 0, 12));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Batch Generate Redemption Codes
     */
    public static function batchGenerate(int $templateId, int $count, array $options = []): string
    {
        $batchId = uniqid('batch_');
        $prefix = $options['prefix'] ?? 'GC';
        $expiresAt = $options['expires_at'] ?? null;
        $maxUsage = $options['max_usage'] ?? 1;

        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = [
                'template_id' => $templateId,
                'code' => self::generateCode($prefix),
                'batch_id' => $batchId,
                'status' => self::STATUS_UNUSED,
                'expires_at' => $expiresAt,
                'max_usage' => $maxUsage,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }

        self::insert($codes);

        return $batchId;
    }

    /**
     * Set Actual Reward（For Blind Boxes and Others）
     */
    public function setActualRewards(array $rewards): bool
    {
        $this->actual_rewards = $rewards;
        return $this->save();
    }

    /**
     * Get Actual Reward
     */
    public function getActualRewards(): array
    {
        return $this->actual_rewards ?? $this->template->rewards ?? [];
    }

    /**
     * Check Redemption Code Format
     */
    public static function validateCodeFormat(string $code): bool
    {
// Basic Format Validation: Alphanumeric, Length 8-32
        return preg_match('/^[A-Z0-9]{8,32}$/', $code);
    }

    /**
     * By BatchIDGet Redemption Code
     */
    public static function getByBatchId(string $batchId)
    {
        return self::where('batch_id', $batchId)->get();
    }

    /**
     * Clean Expired Redemption Codes
     */
    public static function cleanupExpired(): int
    {
        $count = self::where('status', self::STATUS_UNUSED)
            ->where('expires_at', '<', time())
            ->count();

        self::where('status', self::STATUS_UNUSED)
            ->where('expires_at', '<', time())
            ->update(['status' => self::STATUS_EXPIRED]);

        return $count;
    }
}