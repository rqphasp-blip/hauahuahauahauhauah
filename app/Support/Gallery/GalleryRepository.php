<?php

namespace App\Support\Gallery;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryRepository
{
    /**
     * Cache of the resolved gallery source table and column mappings.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $sourceCache = null;

    /**
     * Candidate table names that might store gallery images.
     *
     * @var array<int, string>
     */
    protected array $candidateTables = [
        'gallery_images',
        'gallery',
        'gallery_items',
        'gallery_photos',
        'plugin_gallery_images',
        'plugin_gallery_items',
        'plugin_gallery',
        'plugin_galleries',
        'galleries',
        'gallery_uploads',
        'gallery_files',
        'user_gallery',
        'user_gallery_images',
    ];

    /**
     * Determine if the gallery data source is available.
     */
    public function available(): bool
    {
        return $this->detectSource() !== null;
    }

    /**
     * Resolve the effective user identifier for the current session.
     */
    public function resolveUserId(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if (!empty($user->auth_as) && is_numeric($user->auth_as)) {
            return (int) $user->auth_as;
        }

        return (int) $user->id;
    }

    /**
     * Fetch all gallery images that belong to the provided (or current) user.
     *
     * @param  int|null  $userId
     * @return Collection<int, array<string, mixed>>
     */
    public function getImagesForUser(?int $userId = null): Collection
    {
        $userId ??= $this->resolveUserId();

        if (!$userId) {
            return collect();
        }

        $source = $this->detectSource();

        if (!$source) {
            return collect();
        }

        $query = DB::table($source['table']);
        $query = $this->applyUserScope($query, $source, $userId);

        $records = $query
            ->orderByDesc($source['columns']['id'])
            ->get();

        return $this->mapRecords($records, $source);
    }

    /**
     * Fetch gallery images by their identifiers.
     *
     * @param  array<int, int>  $ids
     * @param  int|null  $userId
     * @return Collection<int, array<string, mixed>>
     */
    public function getImagesByIds(array $ids, ?int $userId = null): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        $source = $this->detectSource();

        if (!$source) {
            return collect();
        }

        $userId ??= $this->resolveUserId();

        $query = DB::table($source['table'])
            ->whereIn($source['columns']['id'], $ids);

        if ($userId) {
            $query = $this->applyUserScope($query, $source, $userId, allowFallback: true);
        }

        $records = $query->get();

        $mapped = $this->mapRecords($records, $source)->keyBy('id');

        return collect($ids)
            ->map(fn ($id) => $mapped->get((int) $id))
            ->filter()
            ->values();
    }

    /**
     * Attempt to detect the gallery storage table and the relevant column mappings.
     *
     * @return array<string, mixed>|null
     */
    protected function detectSource(): ?array
    {
        if ($this->sourceCache !== null) {
            return $this->sourceCache;
        }

        $tables = array_unique(array_merge(
            $this->candidateTables,
            $this->discoverLikelyTables()
        ));

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);

            if (empty($columns)) {
                continue;
            }

            $columnMap = $this->buildColumnMap($columns);

            if ($columnMap === null) {
                continue;
            }

            $this->sourceCache = [
                'table' => $table,
                'columns' => $columnMap,
            ];

            return $this->sourceCache;
        }

        return null;
    }

    /**
     * Construct the column mapping for the detected gallery table.
     *
     * @param  array<int, string>  $columns
     * @return array<string, string|null>|null
     */
    protected function buildColumnMap(array $columns): ?array
    {
        $normalized = [];

        foreach ($columns as $column) {
            $normalized[$this->normalizeColumnName($column)] = $column;
        }

        $map = [];

        $map['id'] = $this->matchColumn($normalized, ['id', 'image_id', 'photo_id', 'gallery_image_id']);
        $map['file'] = $this->matchColumn($normalized, [
            'file_path', 'filepath', 'file', 'path', 'image_path', 'imagepath', 'image', 'url', 'source', 'src', 'full_path',
            'fullpath', 'arquivo', 'imagem', 'foto', 'photopath', 'photofile',
        ]);

        if (!$map['id'] || !$map['file']) {
            return null;
        }

        $map['thumbnail'] = $this->matchColumn($normalized, [
            'thumbnail_path', 'thumbnail', 'thumb_path', 'thumb', 'thumb_url', 'preview', 'preview_path', 'preview_url',
            'miniatura', 'miniaturapath',
        ]);
        $map['title'] = $this->matchColumn($normalized, ['title', 'name', 'label', 'titulo']);
        $map['description'] = $this->matchColumn($normalized, [
            'description',
            'details',
            'summary',
            'text',
            'descricao',
            'legenda',
            'caption',
        ]);
        $map['metadata'] = $this->matchColumn($normalized, ['metadata', 'meta', 'data', 'dados']);
        $map['user'] = $this->matchColumn($normalized, [
            'user_id', 'userid', 'user', 'owner_id', 'ownerid', 'account_id', 'accountid', 'profile_id', 'profileid',
            'page_id', 'pageid', 'perfil_id', 'perfilid', 'conta_id', 'contaid',
        ]);

        return $map;
    }

    /**
     * Apply a user-aware scope to the gallery query.
     */
    protected function applyUserScope($query, array $source, int $userId, bool $allowFallback = false)
    {
        $userColumn = $source['columns']['user'] ?? null;

        if (!$userColumn) {
            return $query;
        }

        $candidates = [$userId, (string) $userId];

        $authUser = Auth::user();
        if ($allowFallback && $authUser && !empty($authUser->auth_as) && is_numeric($authUser->auth_as)) {
            $impersonated = (int) $authUser->auth_as;
            $candidates[] = $impersonated;
            $candidates[] = (string) $impersonated;
        }

        $candidates = array_unique($candidates);

        return $query->where(function ($builder) use ($userColumn, $candidates) {
            foreach ($candidates as $candidate) {
                $builder->orWhere($userColumn, $candidate);
            }
        });
    }

    /**
     * Map raw database records into normalized gallery image payloads.
     *
     * @param  \Illuminate\Support\Collection<int, mixed>  $records
     * @param  array<string, mixed>  $source
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapRecords($records, array $source): Collection
    {
        return collect($records)
            ->map(function ($record) use ($source) {
                $id = $this->getAttribute($record, $source['columns']['id']);
                $file = $this->getAttribute($record, $source['columns']['file']);

                if (!$id || !$file) {
                    return null;
                }

                $thumbnail = $source['columns']['thumbnail']
                    ? $this->getAttribute($record, $source['columns']['thumbnail'])
                    : null;

                $title = $source['columns']['title']
                    ? $this->getAttribute($record, $source['columns']['title'])
                    : null;

                $description = $source['columns']['description']
                    ? $this->getAttribute($record, $source['columns']['description'])
                    : null;

                $metadata = $source['columns']['metadata']
                    ? $this->decodeMetadata($this->getAttribute($record, $source['columns']['metadata']))
                    : [];

                if (!$title && isset($metadata['title'])) {
                    $title = $metadata['title'];
                }

                if (!$description) {
                    foreach (['description', 'caption', 'legend', 'legenda'] as $metaKey) {
                        if (isset($metadata[$metaKey])) {
                            $description = $metadata[$metaKey];
                            break;
                        }
                    }
                }

                $fullUrl = $this->resolveUrl($file);
                $thumbUrl = $this->resolveUrl($thumbnail) ?: $fullUrl;

                if (!$fullUrl) {
                    return null;
                }

                $title = $this->normalizeText($title) ?: $this->deriveTitleFromPath($file);
                $description = $this->normalizeText($description) ?: $title;

                $alt = $title ?: ($metadata['alt'] ?? null);
                $alt = $this->normalizeText($alt) ?: $title;

                return [
                    'id' => (int) $id,
                    'title' => $title,
                    'description' => $description,
                    'file_path' => $file,
                    'thumbnail_path' => $thumbnail,
                    'full_url' => $fullUrl,
                    'thumbnail_url' => $thumbUrl,
                    'alt' => $alt,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * Convert potential metadata values into an associative array.
     *
     * @param  mixed  $value
     * @return array<string, mixed>
     */
    protected function decodeMetadata($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Resolve a gallery asset path into a publicly accessible URL.
     */
    protected function resolveUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $candidates = $this->candidatePaths($path);

        foreach ($candidates as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->url($candidate);
            }
        }

        $publicRoots = array_filter([
            function_exists('public_path') ? public_path() : null,
            base_path('public_html'),
            base_path('public'),
        ]);

        foreach ($publicRoots as $root) {
            foreach ($candidates as $candidate) {
                $full = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $candidate;
                if (is_file($full)) {
                    $relative = trim(str_replace(base_path(), '', $full), DIRECTORY_SEPARATOR);
                    return asset(str_replace(DIRECTORY_SEPARATOR, '/', $relative));
                }
            }
        }

        if (!empty($candidates)) {
            return asset(str_replace(DIRECTORY_SEPARATOR, '/', $candidates[0]));
        }

        return null;
    }


    /**
     * Generate potential relative paths for the provided gallery asset.
     *
     * @param  string  $path
     * @return array<int, string>
     */
    protected function candidatePaths(string $path): array
    {
        $normalized = ltrim($path, '/');
        $variants = [$normalized];

        if (str_starts_with($normalized, 'storage/')) {
            $variants[] = substr($normalized, strlen('storage/'));
        }

        $variants[] = 'storage/' . $normalized;
        $variants[] = 'storage/app/public/' . $normalized;
        $variants[] = 'uploads/' . $normalized;
        $variants[] = 'gallery/' . $normalized;
        $variants[] = 'plugins/gallery/' . $normalized;
        $variants[] = 'gallery/uploads/' . $normalized;
        $variants[] = 'plugins/gallery/uploads/' . $normalized;

        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * Normalize column names for matching.
     */
    protected function normalizeColumnName(string $column): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $column));
    }

    /**
     * Attempt to match a candidate column against the normalized column map.
     *
     * @param  array<string, string>  $normalized
     * @param  array<int, string>  $candidates
     */
    protected function matchColumn(array $normalized, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $key = $this->normalizeColumnName($candidate);
            if (isset($normalized[$key])) {
                return $normalized[$key];
            }
        }

        return null;
    }

    /**
     * Safely extract an attribute from the record regardless of its data structure.
     *
     * @param  mixed  $record
     * @param  string|null  $key
     */
    protected function getAttribute($record, ?string $key)
    {
        if (!$key) {
            return null;
        }

        if (is_array($record) && array_key_exists($key, $record)) {
            return $record[$key];
        }

        if (is_object($record) && isset($record->{$key})) {
            return $record->{$key};
        }

        return null;
    }

    /**
     * Normalize textual fields by trimming and ensuring empty strings become null.
     */
    protected function normalizeText($value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? null : $value;
        }

        return null;
    }

    /**
     * Derive a fallback title from a given path.
     */
    protected function deriveTitleFromPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $basename = pathinfo($path, PATHINFO_FILENAME);

        return $basename !== '' ? $basename : null;
    }

    /**
     * Attempt to discover additional gallery-like tables dynamically.
     *
     * @return array<int, string>
     */
    protected function discoverLikelyTables(): array
    {
        try {
            $tables = $this->listDatabaseTables();
        } catch (\Throwable $exception) {
            return [];
        }

        if (empty($tables)) {
            return [];
        }

        $needles = ['gallery', 'photo', 'image'];

        return collect($tables)
            ->filter(fn ($table) => is_string($table) && Str::contains(strtolower($table), $needles))
            ->values()
            ->all();
    }

    /**
     * Retrieve the list of tables available in the current connection.
     *
     * @return array<int, string>
     */
    protected function listDatabaseTables(): array
    {
        $connection = Schema::getConnection();

        try {
            if (method_exists($connection, 'getDoctrineSchemaManager')) {
                $schemaManager = $connection->getDoctrineSchemaManager();
                if ($schemaManager) {
                    return $schemaManager->listTableNames();
                }
            }
        } catch (\Throwable $exception) {
            // Ignore and fall back to SQL-based discovery.
        }

        try {
            $driverName = $connection->getDriverName();

            if ($driverName === 'mysql') {
                $results = DB::select('SHOW TABLES');

                return collect($results)
                    ->map(function ($row) {
                        if (is_object($row)) {
                            return reset($row);
                        }

                        if (is_array($row)) {
                            return reset($row);
                        }

                        return null;
                    })
                    ->filter()
                    ->values()
                    ->all();
            }

            if ($driverName === 'sqlite') {
                $results = DB::select("SELECT name FROM sqlite_master WHERE type='table'");

                return collect($results)
                    ->map(function ($row) {
                        if (is_object($row) && isset($row->name)) {
                            return $row->name;
                        }

                        if (is_array($row) && isset($row['name'])) {
                            return $row['name'];
                        }

                        return null;
                    })
                    ->filter()
                    ->values()
                    ->all();
            }
        } catch (\Throwable $exception) {
            // Ignore any SQL discovery issues.
        }

        return [];
    }
}
