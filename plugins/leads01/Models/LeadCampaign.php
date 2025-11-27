<?php

namespace plugins\leads01\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LeadCampaign extends Model
{
    protected $table = 'leads01_campaigns';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'thank_you_message',
        'status',
		'visivel',
    ];

	
	
	protected $casts = [
        'visivel' => 'integer', // ou 'boolean' se preferir
    ];
	
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(LeadField::class, 'campaign_id');
    }

    public function entries()
    {
        return $this->hasMany(LeadEntry::class, 'campaign_id');
    }
}