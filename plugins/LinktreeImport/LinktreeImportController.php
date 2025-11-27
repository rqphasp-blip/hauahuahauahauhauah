<?php

namespace App\Providers\plugins\linktreeimport;

use App\Http\Controllers\Controller;
use App\Models\Button;
use App\Models\Link;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LinktreeImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $latestImport = $this->getLastImportMetadata($user->id);
        $links = collect($latestImport['links'] ?? []);

        return view($this->resolveView('index'), compact('user', 'latestImport', 'links'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'profile_url' => 'required|url',
        ]);

        $user = Auth::user();

        if ($this->getLastImportMetadata($user->id)) {
            return redirect()->route('profile-import.index')->with(
                'error',
                'Limpe a importação anterior antes de iniciar uma nova.'
            );
        }

        $response = Http::get($request->profile_url);

        if ($response->failed() || trim($response->body()) === '') {
            return redirect()->back()->with('error', 'Não foi possível acessar o perfil informado.');
        }

        $parsedProfile = $this->parseLinktreeProfile($response->body());

        if (empty($parsedProfile['links'])) {
            return redirect()->back()->with('error', 'Não encontramos links para importar neste perfil.');
        }

        $importBatchId = (string) Str::uuid();

        $avatarPath = $this->downloadAvatar($parsedProfile['avatar_url'] ?? null, $user->id);

        $this->updateUserProfile($user->id, $parsedProfile, $avatarPath);

        $importedLinks = $this->persistLinks($user->id, $parsedProfile['links'], $importBatchId, $request->profile_url);

        if (empty($importedLinks)) {
            return redirect()->back()->with('error', 'Não foi possível adicionar os links importados ao seu perfil.');
        }

        $this->rememberLastImport($user->id, [
            'source_url' => $request->profile_url,
            'display_name' => $parsedProfile['display_name'] ?? null,
            'bio' => $parsedProfile['bio'] ?? null,
            'seo_description' => $parsedProfile['seo_description'] ?? null,
            'headings' => $parsedProfile['headings'] ?? [],
            'avatar_path' => $avatarPath,
            'links' => $importedLinks,
            'import_batch_id' => $importBatchId,
            'imported_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('profile-import.index')->with('success', 'Perfil importado e anexado ao seu perfil com sucesso!');
    }

    public function clear()
    {
        $user = Auth::user();
        $latestImport = $this->getLastImportMetadata($user->id);

        if (! $latestImport) {
            return redirect()->route('profile-import.index')->with('error', 'Nenhuma importação recente para limpar.');
        }

        $this->removeImportedLinks($latestImport, $user->id);
        $this->removeImportedAssets($latestImport);

        UserData::saveData($user->id, 'profile_import', null);
        UserData::saveData($user->id, 'linktree_import', null);

        return redirect()->route('profile-import.index')->with('success', 'Importação anterior limpa. Agora você pode importar novamente.');
    }

    protected function resolveView(string $view): string
    {
        return view()->exists("linktreeimport.$view") ? "linktreeimport.$view" : "linktreeimport::$view";
    }

    protected function parseLinktreeProfile(string $html): array
    {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $profileData = null;
        $profile = [
            'display_name' => null,
            'bio' => null,
            'seo_description' => null,
            'avatar_url' => null,
            'links' => [],
            'headings' => [],
        ];

        $initialData = $this->extractInitialData($html);

        if ($initialData) {
            $profileData = $initialData['page'] ?? ($initialData['profile'] ?? ($initialData['props']['pageProps']['profile'] ?? null));
            $linksData = $initialData['links'] ?? ($initialData['page']['links'] ?? ($initialData['props']['pageProps']['links'] ?? []));

            if (isset($profileData['title'])) {
                $profile['display_name'] = $this->stripBranding($profileData['title']);
            }

            if (isset($profileData['description'])) {
                $description = $this->stripBranding($profileData['description']);
                if (! isset($profileData['dynamicMetaDescription']) || $description !== $this->stripBranding($profileData['dynamicMetaDescription'])) {
                    $profile['bio'] = $description;
                }
            }

            if (isset($profileData['profilePicture'])) {
                $profile['avatar_url'] = $profileData['profilePicture'];
            }

            if (isset($profileData['profilePictureUrl'])) {
                $profile['avatar_url'] = $profile['avatar_url'] ?? $profileData['profilePictureUrl'];
            }

            foreach ($linksData as $link) {
                $preparedLink = $this->prepareLinkFromArray($link);

                if (! $preparedLink) {
                    continue;
                }

                if ($preparedLink['type'] === 'heading') {
                    $profile['headings'][] = $preparedLink['title'];
                    continue;
                }

                if (! $this->shouldSkipLink($preparedLink['title'], $preparedLink['url'])) {
                    $profile['links'][] = $preparedLink;
                }
            }
        }

        $domAvatar = $this->extractAvatarFromDom($html);
        if ($domAvatar) {
            $profile['avatar_url'] = $domAvatar;
        }

        if (! $profile['display_name']) {
            $profile['display_name'] = $this->stripBranding(
                $this->extractMetaContent($html, 'og:title')
                ?? $this->extractMetaContent($html, 'twitter:title')
            );
        }

        if (! $profile['seo_description'] && isset($profileData['dynamicMetaDescription'])) {
            $profile['seo_description'] = $this->stripBranding($profileData['dynamicMetaDescription']);
        }

        if (! $profile['bio']) {
            $metaDescription = $this->extractMetaContent($html, 'og:description')
                ?? $this->extractMetaContent($html, 'description');

            if ($profile['seo_description'] && $metaDescription === $profile['seo_description']) {
                $metaDescription = null;
            }

            $profile['bio'] = $this->stripBranding($metaDescription);
        }

        if ($profile['bio'] && $profile['seo_description'] && $profile['bio'] === $profile['seo_description']) {
            $profile['bio'] = null;
        }

        if (! $profile['avatar_url']) {
            $profile['avatar_url'] = $this->extractMetaContent($html, 'og:image')
                ?? $this->extractMetaContent($html, 'twitter:image');
        }

        if (empty($profile['links'])) {
            $profile['links'] = $this->extractLinksFromMarkup($html);
        }

        $profile['links'] = $this->deduplicateSocialIcons($profile['links']);

        return $profile;
    }

    protected function extractInitialData(string $html): ?array
    {
        $patterns = [
            '/window\.__INITIAL_DATA__\s*=\s*({.*?})\s*;<\\/script>/s',
            '/<script id="__NEXT_DATA__" type="application\/json">(.+?)<\\/script>/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $json = $matches[1];
                $decoded = json_decode(html_entity_decode($json), true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    protected function extractMetaContent(string $html, string $property): ?string
    {
        $metaPattern = '/<meta[^>]+(?:property|name)="' . preg_quote($property, '/') . '"[^>]+content="([^"]+)"[^>]*>/i';

        if (preg_match($metaPattern, $html, $matches)) {
            return html_entity_decode($matches[1]);
        }

        return null;
    }

    protected function extractLinksFromMarkup(string $html): array
    {
        $links = [];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('a') as $anchor) {
            /** @var \DOMElement $anchor */
            $href = $anchor->getAttribute('href');
            $title = $this->stripBranding(trim($anchor->textContent));
            $isSocialIcon = strtolower($anchor->getAttribute('data-testid')) === 'socialicon';

            if ($title === '' && $isSocialIcon) {
                $title = $this->stripBranding($anchor->getAttribute('title') ?: $anchor->getAttribute('aria-label'));

                if ($title === '') {
                    foreach ($anchor->getElementsByTagName('title') as $titleNode) {
                        $title = $this->stripBranding($titleNode->textContent);

                        if ($title !== '') {
                            break;
                        }
                    }
                }
            }

            if (! filter_var($href, FILTER_VALIDATE_URL) || ($title === '' && ! $isSocialIcon)) {
                continue;
            }

            if ($this->shouldSkipLink($title, $href)) {
                continue;
            }

            $icon = $this->mapSocialIcon($href);

            if ($icon) {
                $links[] = $this->buildIconLink($icon, $href);
                continue;
            }

            $links[] = [
                'title' => $title,
                'url' => $href,
            ];
        }

        return $links;
    }

    protected function buildIconLink(string $icon, string $href): array
    {
        // Remove o prefixo 'fa-' apenas se existir no início
        $iconName = (strpos($icon, 'fa-') === 0) ? substr($icon, 3) : $icon;
        
        // Converte o código do ícone para nome amigável
        $friendlyName = $this->getIconFriendlyName($iconName);
        
        return [
            'title' => $friendlyName,
            'url' => $href,
            'thumbnail' => null,
            'type' => 'custom',
            'button_id' => $this->socialIconButtonId(),
            'custom_icon' => 'fa-brands fa-' . $iconName,
        ];
    }

    protected function getIconFriendlyName(string $iconCode): string
    {
        $friendlyNames = [
            'facebook' => 'Facebook',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'twitter' => 'X',
            'youtube' => 'YouTube',
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'spotify' => 'Spotify',
            'threads' => 'Threads',
            'linkedin' => 'LinkedIn',
            'github' => 'GitHub',
            'twitch' => 'Twitch',
            'discord' => 'Discord',
            'amazon' => 'Amazon',
            'patreon' => 'Patreon',
            'store' => 'Loja',
            'bag-shopping' => 'Loja',
            'star' => 'OnlyFans',
            'user-lock' => 'Privacy',
        ];

        return $friendlyNames[$iconCode] ?? ucfirst($iconCode);
    }

    protected function prepareLinkFromArray(array $link): ?array
    {
        $title = $this->stripBranding($link['title'] ?? $link['name'] ?? null);
        $url = $link['url'] ?? $link['link'] ?? null;
        $type = strtolower($link['type'] ?? ($link['linkType'] ?? ($link['__typename'] ?? 'link')));

        if ($type === 'heading' || $type === 'header') {
            return $title ? [
                'title' => $title,
                'url' => null,
                'thumbnail' => null,
                'type' => 'heading',
            ] : null;
        }

        if (! $title || ! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $socialIcon = $this->mapSocialIcon($url);

        if ($socialIcon) {
            return $this->buildIconLink($socialIcon, $url);
        }

        return [
            'title' => $title,
            'url' => $url,
            'thumbnail' => $link['thumbnail'] ?? ($link['imageUrl'] ?? ($link['cover'] ?? null)),
            'type' => 'link',
            'button_id' => null,
            'custom_icon' => null,
        ];
    }

    protected function downloadAsset(?string $url, int $userId, string $prefix): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $response = Http::get($url);

        if ($response->failed()) {
            return null;
        }

        $directory = public_path("uploads/profile-imports/{$userId}");

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
        $filename = $prefix . '_' . Str::random(10) . '.' . $extension;
        $fullPath = $directory . '/' . $filename;

        file_put_contents($fullPath, $response->body());

        return 'uploads/profile-imports/' . $userId . '/' . $filename;
    }

    protected function getLastImportMetadata(int $userId): ?array
    {
        // UserData::getData() já retorna array decodificado
        $data = UserData::getData($userId, 'profile_import');

        if (empty($data) || $data === 'null') {
            $data = UserData::getData($userId, 'linktree_import');
        }

        if (empty($data) || $data === 'null' || !is_array($data)) {
            // Tenta reconstruir a partir dos links no banco
            $rebuilt = $this->reconstructLastImportFromLinks($userId);
            
            if ($rebuilt) {
                $this->rememberLastImport($userId, $rebuilt);
                return $rebuilt;
            }
            
            return null;
        }

        // Verifica se os dados estão completos
        if (!empty($data['links']) && !empty($data['import_batch_id'])) {
            return $data;
        }

        // Se estiver incompleto, tenta reconstruir e mesclar
        $rebuilt = $this->reconstructLastImportFromLinks($userId);

        if ($rebuilt) {
            $merged = array_merge($rebuilt, array_filter($data));
            $this->rememberLastImport($userId, $merged);
            return $merged;
        }

        return $data;
    }

    protected function reconstructLastImportFromLinks(int $userId): ?array
    {
        $linksQuery = DB::table('links')
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        if ($this->linksTableHasColumn('import_batch_id')) {
            $linksQuery->whereNotNull('import_batch_id');
        } else {
            $linksQuery->whereNotNull('type_params')->where('type_params', 'like', '%"import_batch_id"%');
        }

        $select = ['id', 'title', 'link', 'type_params', 'custom_icon', 'created_at'];

        if ($this->linksTableHasColumn('import_batch_id')) {
            $select[] = 'import_batch_id';
        }

        if ($this->linksTableHasColumn('import_source')) {
            $select[] = 'import_source';
        }

        $links = $linksQuery->get($select);

        if ($links->isEmpty()) {
            return null;
        }

        $batches = [];

        foreach ($links as $link) {
            $params = json_decode($link->type_params ?? '', true) ?: [];
            $batchId = $link->import_batch_id ?? ($params['import_batch_id'] ?? null);
            $source = $link->import_source ?? ($params['import_source'] ?? null);

            if (! $batchId) {
                continue;
            }

            if (! isset($batches[$batchId])) {
                $batches[$batchId] = [
                    'links' => [],
                    'import_batch_id' => $batchId,
                    'import_source' => $source,
                    'imported_at' => $link->created_at ? (string) $link->created_at : now()->toDateTimeString(),
                ];
            }

            $batches[$batchId]['links'][] = [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->link,
                'thumbnail_path' => $params['thumbnail_path'] ?? null,
                'custom_icon' => $link->custom_icon,
            ];
        }

        if (empty($batches)) {
            return null;
        }

        $latest = reset($batches);
        $avatarPath = UserData::getData($userId, 'avatar_path');

        return [
            'source_url' => $latest['import_source'] ?? null,
            'display_name' => null,
            'bio' => null,
            'seo_description' => null,
            'headings' => [],
            'avatar_path' => is_string($avatarPath) ? $avatarPath : null,
            'links' => $latest['links'],
            'import_batch_id' => $latest['import_batch_id'],
            'imported_at' => $latest['imported_at'],
        ];
    }

    protected function updateUserProfile(int $userId, array $profile, ?string $avatarPath): void
    {
        $updates = [];

        if (! empty($profile['display_name'])) {
            $updates['name'] = $profile['display_name'];
        }

        $updates['littlelink_description'] = null;

        if (! empty($profile['seo_description'])) {
            $updates['seo_desc'] = $profile['seo_description'];
        }

        if (! empty($profile['headings'])) {
            $updates['subtitulo'] = $profile['headings'][0];
        }

        if ($updates) {
            DB::table('users')->where('id', $userId)->update($updates);
        }

        if ($avatarPath) {
            UserData::saveData($userId, 'avatar_path', $avatarPath);
        }
    }

    protected function persistLinks(int $userId, array $links, ?string $importBatchId = null, ?string $sourceUrl = null): array
    {
        $buttonId = Button::where('name', 'custom')->value('id') ?? Button::min('id');
        if (! $buttonId) {
            return [];
        }
        $currentOrder = (int) DB::table('links')->where('user_id', $userId)->max('order');
        $nextOrder = $currentOrder >= 0 ? $currentOrder + 1 : 0;

        $imported = [];

        foreach ($links as $index => $linkData) {
            $thumbnailPath = $this->downloadAsset($linkData['thumbnail'] ?? null, $userId, 'thumb_' . ($index + 1));

            $typeParams = [];

            if ($importBatchId) {
                $typeParams['import_batch_id'] = $importBatchId;
            }

            if ($sourceUrl) {
                $typeParams['import_source'] = $sourceUrl;
            }

            $linkButtonId = $this->determineButtonId($linkData, $buttonId);

            $link = new Link();
            $link->link = $linkData['url'];
            $link->title = $linkData['title'];
            $link->button_id = $linkButtonId;
            $link->user_id = $userId;
            $link->up_link = 'yes';
            $link->type = $linkData['type'] ?? null;

            if ($importBatchId && $this->linksTableHasColumn('import_batch_id')) {
                $link->import_batch_id = $importBatchId;
            }

            if ($sourceUrl && $this->linksTableHasColumn('import_source')) {
                $link->import_source = $sourceUrl;
            }
            if (! empty($linkData['custom_icon'])) {
                $link->custom_icon = $linkData['custom_icon'];
            }

            if ($thumbnailPath) {
                $typeParams['thumbnail_path'] = $thumbnailPath;
            }

            if ($typeParams) {
                $link->type_params = json_encode($typeParams);
            }

            $link->order = $nextOrder++;
            $link->save();

            $imported[] = [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->link,
                'thumbnail_path' => $thumbnailPath,
                'custom_icon' => $link->custom_icon,
            ];
        }

        return $imported;
    }

    protected function determineButtonId(array $linkData, int $defaultButtonId): int
    {
        if (! empty($linkData['button_id'])) {
            return $linkData['button_id'];
        }

        return $defaultButtonId;
    }

    protected function mapSocialIcon(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        $map = [
            'instagram.com' => 'instagram',
            'tiktok.com' => 'tiktok',
            'spotify.com' => 'spotify',
            'youtube.com' => 'youtube',
            'youtu.be' => 'youtube',
            'twitter.com' => 'twitter',
            'x.com' => 'twitter',
            'facebook.com' => 'facebook',
            'wa.me' => 'whatsapp',
            'whatsapp.com' => 'whatsapp',
            't.me' => 'telegram',
            'telegram.me' => 'telegram',
            'mercadolivre.com.br' => 'store',
            'mercadolibre.com' => 'store',
            'shopee.com' => 'bag-shopping',
            'amazon.' => 'amazon',
            'threads.net' => 'threads',
            'onlyfans.com' => 'star',
            'patreon.com' => 'patreon',
            'privacy.com' => 'user-lock',
        ];

        foreach ($map as $domain => $icon) {
            if (strpos($host, $domain) !== false) {
                return $icon;
            }
        }

        return null;
    }

    protected function deduplicateSocialIcons(array $links): array
    {
        $seenIcons = [];
        $unique = [];

        foreach ($links as $link) {
            $isSocialIcon = ($link['button_id'] ?? null) === $this->socialIconButtonId() || (! empty($link['custom_icon']) && strpos($link['custom_icon'], 'fa-') === 0);

            if ($isSocialIcon) {
                $iconKey = $link['custom_icon'] ?? ($link['title'] ?? '');

                if (! empty($link['custom_icon'])) {
                    $classes = preg_split('/\s+/', trim($link['custom_icon']));

                    foreach (array_reverse($classes) as $class) {
                        if (strpos($class, 'fa-') === 0) {
                            $iconKey = substr($class, 3);
                            break;
                        }
                    }
                }

                $iconKey = strtolower($iconKey);

                if (isset($seenIcons[$iconKey])) {
                    continue;
                }

                $seenIcons[$iconKey] = true;
                $link['title'] = $iconKey;
            }

            $unique[] = $link;
        }

        return $unique;
    }

    protected function socialIconButtonId(): ?int
    {
        static $socialButtonId = null;

        if ($socialButtonId !== null) {
            return $socialButtonId;
        }

        $socialButtonId = Button::find(94)->id ?? null;

        if (! $socialButtonId) {
            $socialButtonId = Button::where('name', 'like', '%icon%')->value('id');
        }

        return $socialButtonId;
    }

    protected function linksTableHasColumn(string $column): bool
    {
        static $columns = [];

        if (array_key_exists($column, $columns)) {
            return $columns[$column];
        }

        $columns[$column] = Schema::hasColumn((new Link())->getTable(), $column);

        return $columns[$column];
    }

    protected function rememberLastImport(int $userId, array $payload): void
    {
        UserData::saveData($userId, 'profile_import', $payload);
    }

    protected function downloadAvatar(?string $url, int $userId): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $response = Http::get($url);

        if ($response->failed()) {
            return null;
        }

        $currentAvatar = function_exists('findAvatar') ? findAvatar($userId) : 'error.error';

        if ($currentAvatar && $currentAvatar !== 'error.error' && file_exists(base_path($currentAvatar))) {
            @unlink(base_path($currentAvatar));
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
        $filename = $userId . '_' . time() . '.' . $extension;

        $directory = public_path('assets/img');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($directory . '/' . $filename, $response->body());

        return 'assets/img/' . $filename;
    }

    protected function extractAvatarFromDom(string $html): ?string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $profilePictureContainer = $dom->getElementById('profile-picture');

        if (! $profilePictureContainer) {
            return null;
        }

        foreach ($profilePictureContainer->getElementsByTagName('img') as $img) {
            $classes = $img->getAttribute('class');

            if (strpos($classes, 'rounded-full') !== false && strpos($classes, 'object-contain') !== false) {
                $src = $img->getAttribute('src');

                if ($src && filter_var($src, FILTER_VALIDATE_URL)) {
                    return $src;
                }
            }
        }

        return null;
    }

    protected function stripBranding(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = mb_convert_encoding($decoded, 'UTF-8', 'UTF-8, ISO-8859-1');

        return trim(preg_replace('/\blinktree\b/i', '', $normalized));
    }

    protected function shouldSkipLink(?string $title, ?string $url): bool
    {
        $title = strtolower($title ?? '');
        $url = strtolower($url ?? '');

        $blockedWords = ['report', 'privacy', 'linktree'];

        foreach ($blockedWords as $word) {
            if (strpos($title, $word) !== false || ($url && strpos($url, $word) !== false)) {
                return true;
            }
        }

        return false;
    }

    protected function removeImportedLinks(array $importData, int $userId): void
    {
        $links = $importData['links'] ?? [];
        $batchId = $importData['import_batch_id'] ?? null;

        if ($batchId && $this->linksTableHasColumn('import_batch_id')) {
            Link::where('user_id', $userId)->where('import_batch_id', $batchId)->delete();

            return;
        }

        if ($batchId) {
            Link::where('user_id', $userId)
                ->whereNotNull('type_params')
                ->where('type_params', 'like', '%"import_batch_id":"' . $batchId . '"%')
                ->delete();

            return;
        }

        $ids = [];

        foreach ($links as $link) {
            if (! empty($link['id'])) {
                $ids[] = $link['id'];
            }
        }

        if (! empty($ids)) {
            Link::where('user_id', $userId)->whereIn('id', $ids)->delete();
        }
    }

    protected function removeImportedAssets(array $importData): void
    {
        $paths = [];

        if (! empty($importData['avatar_path'])) {
            $paths[] = $importData['avatar_path'];
        }

        foreach ($importData['links'] ?? [] as $link) {
            if (! empty($link['thumbnail_path'])) {
                $paths[] = $link['thumbnail_path'];
            }
        }

        foreach ($paths as $path) {
            $fullPath = public_path($path);

            if ($path && file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }
}