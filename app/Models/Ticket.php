<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property int $user_id userID
 * @property string $subject ticket subject
 * @property string|null $level ticket level
 * @property int $status ticket status
 * @property int|null $reply_status reply status
 * @property int|null $last_reply_user_id last replier
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property-read User $user associated users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TicketMessage> $messages associated ticket messages
 */
class Ticket extends Model
{
    use HasFactory;

    protected $table = 'v2_ticket';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];

    const STATUS_OPENING = 0;
    const STATUS_CLOSED = 1;
    public static $statusMap = [
        self::STATUS_OPENING => 'open',
        self::STATUS_CLOSED => 'close'
    ];

    const REPLY_STATUS_WAITING = 0;
    const REPLY_STATUS_REPLIED = 1;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * associated ticket messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id', 'id');
    }
    
// about to be deleted
    public function message(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id', 'id');
    }
}
