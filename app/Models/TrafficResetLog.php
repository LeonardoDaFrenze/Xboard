<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Traffic Reset Record Model
 * 
 * @property int $id
 * @property int $user_id UserID
 * @property string $reset_type Reset Type
 * @property \Carbon\Carbon $reset_time Reset Time
 * @property int $old_upload Upload Traffic Before Reset
 * @property int $old_download Download Traffic Before Reset
 * @property int $old_total Total Traffic Before Reset
 * @property int $new_upload Upload Traffic After Reset
 * @property int $new_download Download Traffic After Reset
 * @property int $new_total Total Traffic After Reset
 * @property string $trigger_source Trigger Source
 * @property array|null $metadata Additional Metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $user Associated User
 */
class TrafficResetLog extends Model
{
    use HasFactory;
    protected $table = 'v2_traffic_reset_logs';

    protected $fillable = [
        'user_id',
        'reset_type',
        'reset_time',
        'old_upload',
        'old_download',
        'old_total',
        'new_upload',
        'new_download',
        'new_total',
        'trigger_source',
        'metadata',
    ];

    protected $casts = [
        'reset_time' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

// Constants for reset type
    public const TYPE_MONTHLY = 'monthly';
    public const TYPE_FIRST_DAY_MONTH = 'first_day_month';
    public const TYPE_YEARLY = 'yearly';
    public const TYPE_FIRST_DAY_YEAR = 'first_day_year';
    public const TYPE_MANUAL = 'manual';
    public const TYPE_PURCHASE = 'purchase';

// Constants for trigger source
    public const SOURCE_AUTO = 'auto';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_API = 'api';
    public const SOURCE_CRON = 'cron';
    public const SOURCE_USER_ACCESS = 'user_access';
    public const SOURCE_ORDER = 'order';
    public const SOURCE_GIFT_CARD = 'gift_card';

    /**
     * Get the multilingual name of the reset type
     */
    public static function getResetTypeNames(): array
    {
        return [
            self::TYPE_MONTHLY => __('traffic_reset.reset_type.monthly'),
            self::TYPE_FIRST_DAY_MONTH => __('traffic_reset.reset_type.first_day_month'),
            self::TYPE_YEARLY => __('traffic_reset.reset_type.yearly'),
            self::TYPE_FIRST_DAY_YEAR => __('traffic_reset.reset_type.first_day_year'),
            self::TYPE_MANUAL => __('traffic_reset.reset_type.manual'),
            self::TYPE_PURCHASE => __('traffic_reset.reset_type.purchase'),
        ];
    }

    /**
     * Get the multilingual name of the trigger source
     */
    public static function getSourceNames(): array
    {
        return [
            self::SOURCE_AUTO => __('traffic_reset.source.auto'),
            self::SOURCE_MANUAL => __('traffic_reset.source.manual'),
            self::SOURCE_API => __('traffic_reset.source.api'),
            self::SOURCE_CRON => __('traffic_reset.source.cron'),
            self::SOURCE_USER_ACCESS => __('traffic_reset.source.user_access'),
        ];
    }

    /**
     * Associated User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the name of the reset type
     */
    public function getResetTypeName(): string
    {
        return self::getResetTypeNames()[$this->reset_type] ?? $this->reset_type;
    }

    /**
     * Get the name of the trigger source
     */
    public function getSourceName(): string
    {
        return self::getSourceNames()[$this->trigger_source] ?? $this->trigger_source;
    }

    /**
     * Get the difference in traffic before and after the reset
     */
    public function getTrafficDiff(): array
    {
        return [
            'upload_diff' => $this->new_upload - $this->old_upload,
            'download_diff' => $this->new_download - $this->old_download,
            'total_diff' => $this->new_total - $this->old_total,
        ];
    }

    /**
     * Format traffic size
     */
    public function formatTraffic(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}