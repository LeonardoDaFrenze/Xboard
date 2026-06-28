<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TicketMessage
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $message
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Ticket $ticket Associated Work Order
 * @property-read bool $is_from_user Is the message sent by the work order initiator?
 * @property-read bool $is_from_admin Is the message sent by an administrator?
 */
class TicketMessage extends Model
{
    use HasFactory;

    protected $table = 'v2_ticket_message';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];

    protected $appends = ['is_from_user', 'is_from_admin'];
    protected $hidden = ['ticket'];

    /**
     * Associated Work Order
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    /**
     * Determine if the message is sent by the work order initiator.
     */
    public function getIsFromUserAttribute(): bool
    {
        return $this->ticket && $this->ticket->user_id === $this->user_id;
    }

    /**
     * Determine if the message is sent by an administrator.
     */
    public function getIsFromAdminAttribute(): bool
    {
        return $this->ticket && $this->ticket->user_id !== $this->user_id;
    }
}
