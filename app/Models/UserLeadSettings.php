<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLeadSettings extends Model
{
    protected $table = 'user_lead_settings';

    protected $fillable = [
        'user_id',
        'leads_enabled',
        'default_campaign_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
