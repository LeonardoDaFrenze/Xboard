<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Plan
 *
 * @property int $id
 * @property string $name Package Name
 * @property int|null $group_id Package NameID
 * @property int $transfer_enable Permission Group(KB)
 * @property int|null $speed_limit TrafficMbps
 * @property bool $show Speed Limit
 * @property bool $renew Whether to Display
 * @property bool $sell Whether to Allow Renewal
 * @property array|null $prices Whether to Allow Purchase
 * @property array|null $tags Price Configuration
 * @property int $sort Tags
 * @property string|null $content Sorting
 * @property int|null $reset_traffic_method Package Description
 * @property int|null $capacity_limit Traffic Reset Method
 * @property int|null $device_limit Subscription Number Limit
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property-read ServerGroup|null $group Device Quantity Limit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $order Associated Permission Groups
 */
class Plan extends Model
{
    use HasFactory;

    protected $table = 'v2_plan';
    protected $dateFormat = 'U';

// Define traffic reset method
    public const RESET_TRAFFIC_FOLLOW_SYSTEM = null;    // Follow System Settings
    public const RESET_TRAFFIC_FIRST_DAY_MONTH = 0;  // Monthly1Day
    public const RESET_TRAFFIC_MONTHLY = 1;          // Reset Monthly
    public const RESET_TRAFFIC_NEVER = 2;            // Do Not Reset
    public const RESET_TRAFFIC_FIRST_DAY_YEAR = 3;   // Annually1Month1Day
    public const RESET_TRAFFIC_YEARLY = 4;           // Reset Annually

// Define price type
    public const PRICE_TYPE_RESET_TRAFFIC = 'reset_traffic';  // Traffic Reset Price

// Define available subscription periods
    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_QUARTERLY = 'quarterly';
    public const PERIOD_HALF_YEARLY = 'half_yearly';
    public const PERIOD_YEARLY = 'yearly';
    public const PERIOD_TWO_YEARLY = 'two_yearly';
    public const PERIOD_THREE_YEARLY = 'three_yearly';
    public const PERIOD_ONETIME = 'onetime';
    public const PERIOD_RESET_TRAFFIC = 'reset_traffic';

// Define old cycle mapping
    public const LEGACY_PERIOD_MAPPING = [
        'month_price' => self::PERIOD_MONTHLY,
        'quarter_price' => self::PERIOD_QUARTERLY,
        'half_year_price' => self::PERIOD_HALF_YEARLY,
        'year_price' => self::PERIOD_YEARLY,
        'two_year_price' => self::PERIOD_TWO_YEARLY,
        'three_year_price' => self::PERIOD_THREE_YEARLY,
        'onetime_price' => self::PERIOD_ONETIME,
        'reset_price' => self::PERIOD_RESET_TRAFFIC
    ];

    protected $fillable = [
        'group_id',
        'transfer_enable',
        'name',
        'speed_limit',
        'show',
        'sort',
        'renew',
        'content',
        'prices',
        'reset_traffic_method',
        'capacity_limit',
        'sell',
        'device_limit',
        'tags'
    ];

    protected $casts = [
        'show' => 'boolean',
        'renew' => 'boolean',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'group_id' => 'integer',
        'prices' => 'array',
        'tags' => 'array',
        'reset_traffic_method' => 'integer',
    ];

    /**
     * Get all available traffic reset methods
     *
     * @return array
     */
    public static function getResetTrafficMethods(): array
    {
        return [
            self::RESET_TRAFFIC_FOLLOW_SYSTEM => 'Follow System Settings',
            self::RESET_TRAFFIC_FIRST_DAY_MONTH => 'January 1st',
            self::RESET_TRAFFIC_MONTHLY => 'Reset Monthly',
            self::RESET_TRAFFIC_NEVER => 'Do Not Reset',
            self::RESET_TRAFFIC_FIRST_DAY_YEAR => 'January 1st of the Year',
            self::RESET_TRAFFIC_YEARLY => 'Reset Annually',
        ];
    }

    /**
     * Get all available subscription periods
     *
     * @return array
     */
    public static function getAvailablePeriods(): array
    {
        return [
            self::PERIOD_MONTHLY => [
                'name' => 'Monthly Payment',
                'days' => 30,
                'value' => 1
            ],
            self::PERIOD_QUARTERLY => [
                'name' => 'Quarterly Payment',
                'days' => 90,
                'value' => 3
            ],
            self::PERIOD_HALF_YEARLY => [
                'name' => 'Semi-annual Payment',
                'days' => 180,
                'value' => 6
            ],
            self::PERIOD_YEARLY => [
                'name' => 'Annual Payment',
                'days' => 365,
                'value' => 12
            ],
            self::PERIOD_TWO_YEARLY => [
                'name' => 'Two-year Payment',
                'days' => 730,
                'value' => 24
            ],
            self::PERIOD_THREE_YEARLY => [
                'name' => 'Three-year Payment',
                'days' => 1095,
                'value' => 36
            ],
            self::PERIOD_ONETIME => [
                'name' => 'One-time Payment',
                'days' => -1,
                'value' => -1
            ],
            self::PERIOD_RESET_TRAFFIC => [
                'name' => 'Reset Traffic',
                'days' => -1,
                'value' => -1
            ],
        ];
    }

    /**
     * Get the price for a specified period
     *
     * @param string $period
     * @return int|null
     */
    public function getPriceByPeriod(string $period): ?int
    {
        return $this->prices[$period] ?? null;
    }

    /**
     * Get all periods with set prices
     *
     * @return array
     */
    public function getActivePeriods(): array
    {
        return array_filter(
            self::getAvailablePeriods(),
            fn($period) => isset($this->prices[$period])
            && $this->prices[$period] > 0,
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Set the price for a specified period
     *
     * @param string $period
     * @param int $price
     * @return void
     * @throws InvalidArgumentException
     */
    public function setPeriodPrice(string $period, int $price): void
    {
        if (!array_key_exists($period, self::getAvailablePeriods())) {
            throw new InvalidArgumentException("Invalid period: {$period}");
        }

        $prices = $this->prices ?? [];
        $prices[$period] = $price;
        $this->prices = $prices;
    }

    /**
     * Remove the price for a specified period
     *
     * @param string $period
     * @return void
     */
    public function removePeriodPrice(string $period): void
    {
        $prices = $this->prices ?? [];
        unset($prices[$period]);
        $this->prices = $prices;
    }

    /**
     * Get all prices and their corresponding period information
     *
     * @return array
     */
    public function getPriceList(): array
    {
        $prices = $this->prices ?? [];
        $periods = self::getAvailablePeriods();

        $priceList = [];
        foreach ($prices as $period => $price) {
            if (isset($periods[$period]) && $price > 0) {
                $priceList[$period] = [
                    'period' => $periods[$period],
                    'price' => $price,
                    'average_price' => $periods[$period]['value'] > 0
                        ? round($price / $periods[$period]['value'], 2)
                        : $price
                ];
            }
        }

        return $priceList;
    }

    /**
     * Check if traffic can be reset
     *
     * @return bool
     */
    public function canResetTraffic(): bool
    {
        return $this->reset_traffic_method !== self::RESET_TRAFFIC_NEVER
            && $this->getResetTrafficPrice() > 0;
    }

    /**
     * Get the price to reset traffic
     *
     * @return int
     */
    public function getResetTrafficPrice(): int
    {
        return $this->prices[self::PRICE_TYPE_RESET_TRAFFIC] ?? 0;
    }

    /**
     * Calculate the number of valid days for a specified period
     *
     * @param string $period
     * @return int -1Indicates perpetual validity
     * @throws InvalidArgumentException
     */
    public static function getPeriodDays(string $period): int
    {
        $periods = self::getAvailablePeriods();
        if (!isset($periods[$period])) {
            throw new InvalidArgumentException("Invalid period: {$period}");
        }

        return $periods[$period]['days'];
    }

    /**
     * Check if the cycle is valid
     *
     * @param string $period
     * @return bool
     */
    public static function isValidPeriod(string $period): bool
    {
        return array_key_exists($period, self::getAvailablePeriods());
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function group(): HasOne
    {
        return $this->hasOne(ServerGroup::class, 'id', 'group_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Set traffic reset method
     *
     * @param int $method
     * @return void
     * @throws InvalidArgumentException
     */
    public function setResetTrafficMethod(int $method): void
    {
        if (!array_key_exists($method, self::getResetTrafficMethods())) {
            throw new InvalidArgumentException("Invalid reset traffic method: {$method}");
        }

        $this->reset_traffic_method = $method;
    }

    /**
     * Set traffic reset price
     *
     * @param int $price
     * @return void
     */
    public function setResetTrafficPrice(int $price): void
    {
        $prices = $this->prices ?? [];
        $prices[self::PRICE_TYPE_RESET_TRAFFIC] = max(0, $price);
        $this->prices = $prices;
    }

    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}