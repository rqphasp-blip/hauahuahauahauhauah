<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Button;
use App\Support\Gallery\GalleryRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class LinkTypeViewController extends Controller
{
     public function __construct(protected GalleryRepository $galleryRepository)
    {
    }

	 protected function resolveGalleryRepository(): ?GalleryRepository
    {
        static $resolved = false;
        static $repository = null;

        if ($resolved) {
            return $repository;
        }

        $resolved = true;

        if (!class_exists(GalleryRepository::class)) {
            return $repository = null;
        }

        try {
            $repository = app(GalleryRepository::class);
        } catch (\Throwable $exception) {
            Log::warning('Gallery repository could not be resolved.', [
                'exception' => $exception->getMessage(),
            ]);

            $repository = null;
        }

        return $repository;
    }
	
	
	
    public function getParamForm($typename, $linkId = 0)
    {
        $data = [
            'title' => '',
            'link' => '',
            'button_id' => 0,
            'buttons' => [],
        ];
    
        if ($linkId) {
            $link = Link::find($linkId);
            $data['title'] = $link->title;
            $data['link'] = $link->link;
            if (Route::currentRouteName() != 'showButtons') {
                $data['button_id'] = $link->button_id;
            }
    
            if (!empty($link->type_params) && is_string($link->type_params)) {
                $typeParams = json_decode($link->type_params, true);
                if (is_array($typeParams)) {
                    $data = array_merge($data, $typeParams);
                }
            }
        }
        if ($typename === 'predefined') {
            $buttons = Button::select()->orderBy('name', 'asc')->get();
            foreach ($buttons as $btn) {
                $data['buttons'][] = [
                    'name' => $btn->name,
                    'title' => $btn->alt,
                    'exclude' => $btn->exclude,
                    'selected' => ($linkId && isset($link) && $link->button_id == $btn->id),
                ];
            }
            return view('components.pageitems.predefined-form', $data);
        }

        if ($typename === 'gallery') {
            $repository = $this->resolveGalleryRepository();

            if ($repository) {
                try {
                    $data['galleryImages'] = $repository
                        ->getImagesForUser()
                        ->map(function (array $image) {
                            return [
                                'id' => $image['id'],
                                'title' => $image['title'] ?? pathinfo($image['file_path'] ?? '', PATHINFO_FILENAME),
                                'url' => $image['thumbnail_url'] ?? $image['full_url'],
                            ];
                        })
                        ->filter(fn ($image) => !empty($image['id']) && !empty($image['url']))
                        ->values()
                        ->all();
                } catch (\Throwable $exception) {
                    Log::warning('Gallery images could not be loaded from the repository.', [
                        'exception' => $exception->getMessage(),
                    ]);

                    $data['galleryImages'] = [];
                }
            } else {
                $data['galleryImages'] = [];
            }
        }
        // Set the block asset context before returning the view
        setBlockAssetContext($typename);
        return view($typename . '.form', $data);
    }

    /**
     * Retrieve gallery images for the authenticated user, if available.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getGalleryImagesForUser(): array
    {
        try {
            if (!Auth::check() || !Schema::hasTable('gallery_images')) {
                return [];
            }

            return GalleryImage::query()
                ->where('user_id', Auth::id())
                ->orderByDesc('id')
                ->get()
                ->map(function (GalleryImage $image) {
                    $path = $image->thumbnail_path ?: $image->file_path;
                    $url = $this->resolveGalleryUrl($path);

                    if (!$url) {
                        return null;
                    }

                    return [
                        'id' => $image->id,
                        'title' => $image->title ?? pathinfo($path ?? '', PATHINFO_FILENAME),
                        'url' => $url,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            Log::warning('Gallery images could not be loaded for the current user.', [
                'user_id' => Auth::id(),
                'exception' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Resolve a gallery asset path into a publicly accessible URL.
     *
     * @param  string|null  $path
     * @return string|null
     */
    protected function resolveGalleryUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (Storage::disk('public')->exists($normalizedPath)) {
            return Storage::disk('public')->url($normalizedPath);
        }

        $publicPath = public_path($normalizedPath);
        if (file_exists($publicPath)) {
            return asset($normalizedPath);
        }

        return asset('storage/' . $normalizedPath);
    }

    public function blockAsset(Request $request, $type)
    {
        $asset = $request->query('asset');

        // Prevent directory traversal in $type
        if (preg_match('/\.\.|\/|\\\\/', $type)) {
            abort(403, 'Unauthorized action.');
        }

        // Define allowed file extensions
        $allowedExtensions = ['js', 'css', 'img', 'svg', 'gif', 'jpg', 'jpeg', 'png', 'mp4', 'mp3'];

        $extension = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return response('File type not allowed', Response::HTTP_FORBIDDEN);
        }

        $basePath = realpath(base_path("blocks/$type"));

        $fullPath = realpath(base_path("blocks/$type/$asset"));

        if (!$fullPath || !file_exists($fullPath) || strpos($fullPath, $basePath) !== 0) {
            return response('File not found', Response::HTTP_NOT_FOUND);
        }

        // Map file extensions to MIME types
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'img' => 'image/png',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
        ];

        // Determine the MIME type using the mapping
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType
        ]);
    }
}
