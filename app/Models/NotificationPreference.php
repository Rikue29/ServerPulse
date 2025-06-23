<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'alert_type',
        'severity_min',
        'via_email',
        'via_slack',
        'via_sms',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
