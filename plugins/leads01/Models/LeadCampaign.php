<?php

namespace plugins\leads01\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'thank_you_message',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(LeadField::class, 'campaign_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LeadEntry::class, 'campaign_id');
    }
}