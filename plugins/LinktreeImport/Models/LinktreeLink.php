<?php

namespace App\Models\plugins\linktreeimport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinktreeLink extends Model
{
    protected $table = 'user_linktree_links';

    protected $fillable = [
        'import_id',
        'user_id',
        'title',
        'url',
        'thumbnail_path',
        'custom_icon',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com a importação
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(LinktreeImport::class, 'import_id');
    }

    /**
     * Escopo para obter links de uma importação específica
     */
    public function scopeByImport($query, int $importId)
    {
        return $query->where('import_id', $importId)
            ->orderBy('position');
    }

    /**
     * Escopo para obter links de um usuário
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
