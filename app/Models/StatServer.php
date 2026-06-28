<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StatServer
 *
 * @property int $id
 * @property int $server_id ServerID
 * @property int $u Upload Traffic
 * @property int $d Download Traffic
 * @property int $record_at Record Time
 * @property int $created_at
 * @property int $updated_at
 * @property-read int $value PassSUM(u + d)Calculated Total Traffic Value，Available Only When Querying a Specific Time
 */
class StatServer extends Model
{
    use HasFactory;
    protected $table = 'v2_stat_server';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];

    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }
}
