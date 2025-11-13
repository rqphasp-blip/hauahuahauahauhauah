<?php

namespace plugins\leads01\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadField extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'label',
        'type',
        'required',
        'order',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
        'required' => 'boolean',
        'order' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(LeadCampaign::class, 'campaign_id');
    }
}