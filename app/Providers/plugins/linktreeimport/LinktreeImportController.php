<?php

namespace App\Providers\plugins\linktreeimport;

use App\Http\Controllers\Controller;
use App\Models\Button;
use App\Models\Link;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $avatarPath = $this->downloadAvatar($parsedProfile['avatar_url'] ?? null, $user->id);

        $this->updateUserProfile($user->id, $parsedProfile, $avatarPath);

        $importedLinks = $this->persistLinks($user->id, $parsedProfile['links']);

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

            if (filter_var($href, FILTER_VALIDATE_URL) && $title !== '') {
                if ($this->shouldSkipLink($title, $href)) {
                    continue;
                }

                $links[] = [
                    'title' => $title,
                    'url' => $href,
                ];
            }
        }

        return $links;
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

        return [
             'title' => $title,
            'url' => $url,
            'thumbnail' => $link['thumbnail'] ?? ($link['imageUrl'] ?? ($link['cover'] ?? null)),
            'type' => $socialIcon ? 'custom' : 'link',
            'button_id' => null,
            'custom_icon' => $socialIcon,
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
        $data = UserData::getData($userId, 'profile_import');

        if (empty($data) || $data === 'null') {
            $data = UserData::getData($userId, 'linktree_import');
        }

		
		if (is_string($data) && $data !== 'null') {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            }
        }
		
		
        if (empty($data) || $data === 'null') {
            return null;
        }

		
		  if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }
		
	  if (isset($data['links']) && is_string($data['links'])) {
            $linksDecoded = json_decode($data['links'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['links'] = $linksDecoded;
            }
        }

        if (isset($data['links']) && is_object($data['links'])) {
            $data['links'] = json_decode(json_encode($data['links']), true);
        }

        if (isset($data['headings']) && is_string($data['headings'])) {
            $headingsDecoded = json_decode($data['headings'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['headings'] = $headingsDecoded;
            }
        }

		
        return is_array($data) ? $data : null;
    }

    protected function updateUserProfile(int $userId, array $profile, ?string $avatarPath): void
    {
        $updates = [];

        if (! empty($profile['display_name'])) {
            $updates['name'] = $profile['display_name'];
        }

 
		
		
		 if (! empty($profile['seo_description'])) {
            $updates['seo_desc'] = $profile['seo_description'];
			  $updates['littlelink_description'] = null;
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

    protected function persistLinks(int $userId, array $links): array
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
 $linkButtonId = $this->determineButtonId($linkData, $buttonId);

            $link = new Link();
            $link->link = $linkData['url'];
            $link->title = $linkData['title'];
            $link->button_id = $linkButtonId;
            $link->user_id = $userId;
            $link->up_link = 'yes';
$link->type = $linkData['type'] ?? null;
            if (! empty($linkData['custom_icon'])) {
                $link->custom_icon = $linkData['custom_icon'];
            }

            if ($thumbnailPath) {
                $link->type_params = json_encode(['thumbnail_path' => $thumbnailPath]);
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

    protected function socialButtonId(): int
    {
        return Button::where('name', 'icons')->value('id')
            ?? Button::where('name', 'icon')->value('id')
            ?? 94;
    }

    protected function mapSocialIcon(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        $map = [
            'instagram.com' => 'fa-instagram',
            'tiktok.com' => 'fa-tiktok',
            'spotify.com' => 'fa-spotify',
            'youtube.com' => 'fa-youtube',
            'youtu.be' => 'fa-youtube',
            'twitter.com' => 'fa-twitter',
            'x.com' => 'fa-twitter',
            'facebook.com' => 'fa-facebook',
        ];

        foreach ($map as $domain => $icon) {
            if (str_contains($host, $domain)) {
                return $icon;
            }
        }

        return null;
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

            if (str_contains($classes, 'rounded-full') && str_contains($classes, 'object-contain')) {
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

        return trim(str_ireplace('linktree', '', $normalized));
    }
	
    protected function shouldSkipLink(?string $title, ?string $url): bool
    {
        $title = strtolower($title ?? '');
        $url = strtolower($url ?? '');

        $blockedWords = ['report', 'privacy','linktree', 'hopp', 'biolink', 'bio.link'];

        foreach ($blockedWords as $word) {
            if (str_contains($title, $word) || ($url && str_contains($url, $word))) {
                return true;
            }
        }

        return false;
    }

    protected function removeImportedLinks(array $importData, int $userId): void
    {
        $links = $importData['links'] ?? [];

        foreach ($links as $link) {
            if (! empty($link['id'])) {
                Link::where('id', $link['id'])->where('user_id', $userId)->delete();
                continue;
            }

            if (! empty($link['url'])) {
                Link::where('user_id', $userId)->where('link', $link['url'])->delete();
            }
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