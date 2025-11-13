<?php

namespace plugins\leads01\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'data',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
        'data' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(LeadCampaign::class, 'campaign_id');
    }

    protected function decodedData(): Attribute
    {
        return Attribute::get(function (): array {
            $data = $this->data;

            if (is_array($data)) {
                return $data;
            }

            $decoded = json_decode((string) $data, true);

            return is_array($decoded) ? $decoded : [];
        });
    }
}