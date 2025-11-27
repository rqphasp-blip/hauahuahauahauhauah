<?php

namespace App\Models\plugins\linktreeimport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinktreeImport extends Model
{
    protected $table = 'user_linktree_imports';

    protected $fillable = [
        'user_id',
        'source_url',
        'display_name',
        'bio',
        'avatar_path',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com os links importados
     */
    public function links(): HasMany
    {
        return $this->hasMany(LinktreeLink::class, 'import_id');
    }

    /**
     * Escopo para obter a importação mais recente de um usuário
     */
    public function scopeLatestForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Converte a importação para array com todos os dados necessários
     */
    public function toImportArray(): array
    {
        return [
            'id' => $this->id,
            'source_url' => $this->source_url,
            'display_name' => $this->display_name,
            'bio' => $this->bio,
            'avatar_path' => $this->avatar_path,
            'links' => $this->links->map(fn($link) => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'thumbnail_path' => $link->thumbnail_path,
                'custom_icon' => $link->custom_icon,
            ])->toArray(),
            'import_batch_id' => $this->id,
            'imported_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
