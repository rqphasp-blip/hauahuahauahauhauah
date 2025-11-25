<?php

namespace plugins\leads01\Models;

use Illuminate\Database\Eloquent\Model;

class LeadField extends Model
{
    protected $table = 'leads01_fields';

    protected $fillable = [
        'campaign_id',
        'label',
        'field_name',
        'field_type',
        'required',
        'placeholder',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'sort_order' => 'integer',
    ];
}