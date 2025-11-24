<?php

namespace App\Providers\plugins\instagramstories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InstagramStoriesImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe o formulário para importar stories do Instagram.
     */
    public function create()
    {
        return view('instagramstories.import');
    }

    /**
     * Realiza o download da mídia do Instagram e cria um novo story.
     */
    public function store(Request $request)
    {
        $request->validate([
            'instagram_url' => 'required|url',
            'caption' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $log = [];

        $this->logStep($log, 'Iniciando importação para a URL informada.');

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/x-m4v' => 'm4v',
            'video/webm' => 'webm',
        ];

        $mediaResponse = $this->fetchInstagramMedia($request->instagram_url, $log);

        if ($mediaResponse === null) {
            $this->logStep($log, 'Nenhuma mídia válida foi obtida após todas as tentativas.');

            return back()
                ->with('error', 'Não foi possível acessar o link informado. Verifique a URL do story do Instagram.')
                ->with('import_log', $log);
        }

        $contentType = $mediaResponse['content_type'];

        $this->logStep($log, 'Conteúdo retornado com Content-Type: ' . ($contentType ?? 'desconhecido'));

        if (! $contentType || ! isset($allowedTypes[$contentType])) {
            $this->logStep($log, 'Tipo de mídia não suportado.');

            return back()
                ->with('error', 'O conteúdo retornado não é uma mídia suportada (imagens: jpeg, png, gif, webp ou vídeos: mp4, mov, m4v, webm).')
                ->with('import_log', $log);
        }

        $body = $mediaResponse['body'];

        if (strlen($body) === 0) {
            $this->logStep($log, 'Nenhum conteúdo retornado pelo link informado.');

            return back()
                ->with('error', 'Nenhum conteúdo foi retornado pelo link informado.')
                ->with('import_log', $log);
        }

        $isVideo = str_starts_with($contentType, 'video/');
        $maxSizeBytes = $isVideo ? 50 * 1024 * 1024 : 5 * 1024 * 1024;

        if (strlen($body) > $maxSizeBytes) {
            $limitMb = $isVideo ? '50MB' : '5MB';

            $this->logStep($log, 'Mídia maior que o limite permitido: ' . strlen($body) . ' bytes.');

            return back()
                ->with('error', "O arquivo retornado é maior que {$limitMb}. Selecione um story menor.")
                ->with('import_log', $log);
        }

        $uploadDir = 'uploads/stories';
        $fullPath = public_path($uploadDir);

        if (! file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $extension = $allowedTypes[$contentType];
        $filename = uniqid('story_', true) . '.' . $extension;
        $relativePath = $uploadDir . '/' . $filename;

        file_put_contents($fullPath . '/' . $filename, $body);

        $this->logStep($log, 'Arquivo salvo em: ' . $relativePath);

        DB::table('user_stories')->insert([
            'user_id' => $user->id,
            'image_path' => $relativePath,
            'caption' => $request->caption,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logStep($log, 'Registro criado na tabela user_stories para o usuário ' . $user->id . '.');

        return redirect()
            ->route('stories.index')
            ->with('success', 'Story importado do Instagram com sucesso!')
            ->with('import_log', $log);
    }

    protected function fetchInstagramMedia(string $url, array &$log): ?array
    {
        $mobileHeaders = [
            // A string similar to o que o instaloader usa para emular app mobile.
            'User-Agent' => 'Instagram 253.0.0.0 Android (30/11; 420dpi; 1080x1920; Google/google; Pixel 5; redfin; redfin; en_US; 382025478)',
            'Accept' => '*/*',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.8',
            'X-IG-App-ID' => '936619743392459',
            'Referer' => 'https://www.instagram.com/',
        ];

        $this->logStep($log, 'Preparando cliente HTTP com headers mobile.');

        $baseRequest = Http::withHeaders($mobileHeaders)->withOptions([
            'allow_redirects' => true,
        ])->timeout(25);

        // 1) Se o link for um story típico, extraímos o ID para bater na API mobile, inspirado na abordagem do instaloader.
        $storyId = $this->extractStoryId($url);

        if ($storyId) {
            $this->logStep($log, 'ID do story detectado: ' . $storyId . '. Tentando endpoint mobile.');

            $mobileEndpoint = "https://i.instagram.com/api/v1/media/{$storyId}/info/";
            $mobileResponse = $this->safeGet($baseRequest, $mobileEndpoint, $log, 'GET mobile info');

            $mediaFromMobile = $this->extractFromMobileInfo($mobileResponse);

            if ($mediaFromMobile) {
                $this->logStep($log, 'URL direta encontrada na resposta mobile.');

                $download = $this->safeGet($baseRequest, $mediaFromMobile, $log, 'DOWNLOAD mobile media');

                if ($download && $download->successful()) {
                    return [
                        'content_type' => $download->header('Content-Type'),
                        'body' => $download->body(),
                    ];
                }

                $this->logStep($log, 'Download da mídia pelo endpoint mobile falhou ou retornou status diferente de 200.');
            }
        } else {
            $this->logStep($log, 'Nenhum ID de story encontrado na URL informada.');
        }

        // 2) Tenta API pública ddinstagram que expõe URL direto, reduzindo bloqueios de geolocalização.
        $ddInstagramUrl = 'https://ddinstagram.com/api?url=' . urlencode($url);
        $this->logStep($log, 'Tentando API ddinstagram: ' . $ddInstagramUrl);

        $ddResponse = $this->safeGet($baseRequest, $ddInstagramUrl, $log, 'GET ddinstagram');

        if ($ddResponse && $ddResponse->successful()) {
            $ddJson = json_decode($ddResponse->body(), true);

            if (isset($ddJson['media_urls']) && is_array($ddJson['media_urls'])) {
                foreach ($ddJson['media_urls'] as $ddMedia) {
                    if (! is_string($ddMedia)) {
                        continue;
                    }

                    $this->logStep($log, 'Tentando download via ddinstagram (lista de URLs).');

                    $download = $this->safeGet($baseRequest, $ddMedia, $log, 'DOWNLOAD ddinstagram media_urls');

                    if ($download && $download->successful()) {
                        return [
                            'content_type' => $download->header('Content-Type'),
                            'body' => $download->body(),
                        ];
                    }
                }
            }

            if (isset($ddJson['media_url']) && is_string($ddJson['media_url'])) {
                $this->logStep($log, 'Tentando download via ddinstagram (media_url único).');

                $download = $this->safeGet($baseRequest, $ddJson['media_url'], $log, 'DOWNLOAD ddinstagram media_url');

                if ($download && $download->successful()) {
                    return [
                        'content_type' => $download->header('Content-Type'),
                        'body' => $download->body(),
                    ];
                }
            }
        } else {
            $this->logStep($log, 'API ddinstagram não retornou sucesso.');
        }

        // 3) Fallback para HTML/JSON público (padrão anterior), agora com parsing mais completo inspirado nos campos usados pelo instaloader.
        foreach ($this->buildCandidateUrls($url) as $candidateUrl) {
            $this->logStep($log, 'Tentando obter HTML/JSON a partir de: ' . $candidateUrl);

            $initial = $this->safeGet($baseRequest, $candidateUrl, $log, 'GET candidate');

            if (! $initial || ! $initial->successful()) {
                $this->logStep($log, 'Falha ao acessar candidato: ' . $candidateUrl);
                continue;
            }

            $contentType = $initial->header('Content-Type');
            $body = $initial->body();

            if (is_string($contentType) && ! str_starts_with($contentType, 'text/html')) {
                $this->logStep($log, 'Resposta não-HTML detectada no candidato, retornando mídia direta.');
                return [
                    'content_type' => $contentType,
                    'body' => $body,
                ];
            }

            $mediaUrl = $this->extractMediaUrlFromHtml($body);

            if (! $mediaUrl) {
                $jsonUrl = $this->buildJsonUrl($candidateUrl);
                $this->logStep($log, 'Tentando extrair via JSON: ' . $jsonUrl);

                $jsonResponse = $this->safeGet($baseRequest, $jsonUrl, $log, 'GET json candidate');

                if ($jsonResponse && $jsonResponse->successful()) {
                    $mediaUrl = $this->extractMediaUrlFromJson($jsonResponse->body());
                }
            }

            if (! $mediaUrl) {
                continue;
            }

            $this->logStep($log, 'URL direta identificada. Tentando download: ' . $mediaUrl);

            $mediaResponse = $this->safeGet($baseRequest, $mediaUrl, $log, 'DOWNLOAD candidate media');

            if ($mediaResponse && $mediaResponse->successful()) {
                return [
                    'content_type' => $mediaResponse->header('Content-Type'),
                    'body' => $mediaResponse->body(),
                ];
            }

            $this->logStep($log, 'Download falhou para a URL direta obtida.');
        }

        return null;
    }

    protected function extractStoryId(string $url): ?string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';

        // URLs típicas: /stories/<username>/<storyId>/
        if (preg_match('#/stories/[A-Za-z0-9_.-]+/(\d+)#', $path, $matches)) {
            return $matches[1];
        }

        // Caso venha um link de reels_media: tenta extrair numérico.
        if (preg_match('#/(\d{6,})/#', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function extractFromMobileInfo($response): ?string
    {
        if (! $response || ! $response->successful()) {
            return null;
        }

        $json = json_decode($response->body(), true);

        if (! is_array($json) || ! isset($json['items'][0])) {
            return null;
        }

        $item = $json['items'][0];

        if (isset($item['video_versions'][0]['url'])) {
            return $item['video_versions'][0]['url'];
        }

        if (isset($item['image_versions2']['candidates'][0]['url'])) {
            return $item['image_versions2']['candidates'][0]['url'];
        }

        return null;
    }

    protected function buildCandidateUrls(string $originalUrl): array
    {
        $urls = [];
        $trimmed = trim($originalUrl);

        if ($trimmed !== '') {
            $urls[] = $trimmed;
        }

        if (str_contains($trimmed, 'instagram.com')) {
            $urls[] = $this->buildJsonUrl($trimmed);

            $parsed = parse_url($trimmed);
            $path = $parsed['path'] ?? '';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $ddUrl = 'https://ddinstagram.com' . $path . $query;

            $urls[] = $ddUrl;
            $urls[] = $this->buildJsonUrl($ddUrl);
        }

        return array_values(array_unique(array_filter($urls)));
    }

    protected function buildJsonUrl(string $url): string
    {
        return str_contains($url, '?') ? $url . '&__a=1&__d=dis' : $url . '?__a=1&__d=dis';
    }

    protected function safeGet($client, string $url, array &$log, string $context)
    {
        try {
            $response = $client->get($url);

            if ($response) {
                $this->logStep($log, $context . ' -> status ' . $response->status());
            }

            return $response;
        } catch (\Exception $e) {
            $this->logStep($log, $context . ' falhou: ' . $e->getMessage());
            return null;
        }
    }

    protected function logStep(array &$log, string $message): void
    {
        $log[] = '[' . date('H:i:s') . '] ' . $message;
    }

    protected function extractMediaUrlFromHtml(string $html): ?string
    {
        if (preg_match('/<meta[^>]+property=["\']og:(video|image)["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return $matches[2];
        }

        if (preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            return $matches[1];
        }

        if (preg_match('/"video_url"\s*:\s*"([^"]+)"/i', $html, $matches)) {
            return stripcslashes($matches[1]);
        }

        if (preg_match('/"display_url"\s*:\s*"([^"]+)"/i', $html, $matches)) {
            return stripcslashes($matches[1]);
        }

        return null;
    }

    protected function extractMediaUrlFromJson(string $json): ?string
    {
        $decoded = json_decode($json, true);

        if (! $decoded || ! is_array($decoded)) {
            return null;
        }

        $stack = [$decoded];

        while ($stack) {
            $current = array_pop($stack);

            foreach ($current as $key => $value) {
                if (in_array($key, ['video_url', 'display_url'], true) && is_string($value)) {
                    return $value;
                }

                if ($key === 'video_versions' && is_array($value)) {
                    foreach ($value as $version) {
                        if (is_array($version) && isset($version['url']) && is_string($version['url'])) {
                            return $version['url'];
                        }
                    }
                }

                if ($key === 'items' && is_array($value)) {
                    foreach ($value as $item) {
                        if (is_array($item) && isset($item['video_versions'][0]['url'])) {
                            return $item['video_versions'][0]['url'];
                        }

                        if (is_array($item) && isset($item['image_versions2']['candidates'][0]['url'])) {
                            return $item['image_versions2']['candidates'][0]['url'];
                        }
                    }
                }

                if (is_array($value)) {
                    $stack[] = $value;
                }
            }
        }

        return null;
    }
}