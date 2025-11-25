<?php

namespace plugins\leads01\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LeadEntry extends Model
{
    protected $table = 'leads01_entries';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}