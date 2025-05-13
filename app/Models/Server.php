<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Server extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'ip_address',
        'environment',
        'monitoring_type',
        'cpu_usage',
        'ram_usage',
        'disk_usage',
        'response_time',
        'location',
        'created_by',
        'status',
        'last_checked_at',
        'ssh_user',
        'ssh_password',
        'ssh_key',
        'ssh_port'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cpu_usage' => 'float',
        'ram_usage' => 'float',
        'disk_usage' => 'float',
        'response_time' => 'float',
        'last_checked_at' => 'datetime'
    ];

    /**
     * Get the user that created the server.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected $hidden = [
        'ssh_password',
        'ssh_key'
    ];
}
