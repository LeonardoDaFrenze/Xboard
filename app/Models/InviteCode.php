<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    use HasFactory;
    protected $table = 'v2_invite_code';
    protected $dateFormat = 'U';
    protected $fillable = ['user_id', 'code', 'status', 'pv'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'status' => 'boolean',
    ];

    const STATUS_UNUSED = 0;
    const STATUS_USED = 1;
}
