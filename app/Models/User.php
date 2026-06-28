<?php

namespace App\Models;

use App\Utils\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\User
 *
 * @property int $id userID
 * @property string $email email
 * @property string $password password
 * @property string|null $password_algo encryption method
 * @property string|null $password_salt encryption salt
 * @property string $token invitation code
 * @property string $uuid
 * @property int|null $invite_user_id referrer
 * @property int|null $plan_id subscriptionID
 * @property int|null $group_id permission groupID
 * @property int|null $transfer_enable traffic(KB)
 * @property int|null $speed_limit speed limitMbps
 * @property int|null $u upload traffic
 * @property int|null $d download traffic
 * @property int|null $banned whether to ban
 * @property int|null $remind_expire expiration reminder
 * @property int|null $remind_traffic traffic reminder
 * @property int|null $expired_at expiration time
 * @property int|null $balance balance
 * @property int|null $commission_balance commission balance
 * @property float $commission_rate return rate
 * @property int|null $commission_type return type
 * @property int|null $device_limit device limit count
 * @property int|null $discount discount
 * @property int|null $last_login_at last login time
 * @property int|null $parent_id parent accountID
 * @property int|null $is_admin whether admin
 * @property int|null $next_reset_at next traffic reset time
 * @property int|null $last_reset_at last traffic reset time
 * @property int|null $telegram_id Telegram ID
 * @property int $reset_count traffic reset count
 * @property int $created_at
 * @property int $updated_at
 * @property bool $commission_auto_check whether to automatically calculate commission
 *
 * @property-read User|null $invite_user referrer information
 * @property-read \App\Models\Plan|null $plan user subscription plan
 * @property-read ServerGroup|null $group permission group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InviteCode> $codes invitation code list
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders order list
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StatUser> $stat statistics
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ticket> $tickets ticket list
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TrafficResetLog> $trafficResetLogs traffic reset record
 * @property-read User|null $parent parent account
 * @property-read string $subscribe_url subscription link（dynamically generated）
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;
    protected $table = 'v2_user';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'banned' => 'boolean',
        'is_admin' => 'boolean',
        'is_staff' => 'boolean',
        'remind_expire' => 'boolean',
        'remind_traffic' => 'boolean',
        'commission_rate' => 'float',
        'next_reset_at' => 'timestamp',
        'last_reset_at' => 'timestamp',
    ];
    protected $hidden = ['password'];

    public const COMMISSION_TYPE_SYSTEM = 0;
    public const COMMISSION_TYPE_PERIOD = 1;
    public const COMMISSION_TYPE_ONETIME = 2;
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    /**
     * case-insensitive email search（compatible with all databases，// Get referrer information）
     */
    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', strtolower(trim($email)));
    }

Get user subscription plan
    public function invite_user(): BelongsTo
    {
        return $this->belongsTo(self::class, 'invite_user_id', 'id');
    }

    /**
     * // Get user invitation code list
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ServerGroup::class, 'group_id', 'id');
    }

// Associate ticket list
    public function codes(): HasMany
    {
        return $this->hasMany(InviteCode::class, 'user_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function stat(): HasMany
    {
        return $this->hasMany(StatUser::class, 'user_id', 'id');
    }

Associate traffic reset record
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * Check if the user is active
     */
    public function trafficResetLogs(): HasMany
    {
        return $this->hasMany(TrafficResetLog::class, 'user_id', 'id');
    }

    /**
     * Check if the user has available node traffic and sufficient amount
     */
    public function isActive(): bool
    {
        return !$this->banned && 
               (!$this->expired_at || $this->expired_at > time()) &&
               $this->plan_id !== null;
    }

    /** 
     * Check if traffic needs to be reset
     */
    public function isAvailable(): bool
    {     
        return $this->isActive() && $this->getRemainingTraffic() > 0;   
    }

    /**
     * Get total used traffic
     */
    public function shouldResetTraffic(): bool
    {
        return $this->isActive() &&
               $this->next_reset_at !== null &&
               $this->next_reset_at <= time();
    }

    /**
     * Get remaining traffic
     */
    public function getTotalUsedTraffic(): int
    {
        return ($this->u ?? 0) + ($this->d ?? 0);
    }

    /**
     * Get traffic usage percentage
     */
    public function getRemainingTraffic(): int
    {
        $used = $this->getTotalUsedTraffic();
        $total = $this->transfer_enable ?? 0;
        return max(0, $total - $used);
    }

    /**
     * Get traffic usage percentage
     */
    public function getTrafficUsagePercentage(): float
    {
        $total = $this->transfer_enable ?? 0;
        if ($total <= 0) {
            return 0;
        }
        
        $used = $this->getTotalUsedTraffic();
        return min(100, ($used / $total) * 100);
    }
}
