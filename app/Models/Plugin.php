<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'name',
        'version',
        'description',
        'author',
        'provider_class',
        'is_active',
        'path',
        'permissions_requested',
        'permissions_granted',
        'installed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions_requested' => 'array',
        'permissions_granted' => 'array',
        'installed_at' => 'datetime',
    ];
}

