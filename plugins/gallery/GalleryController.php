<?php

namespace App\Providers\plugins\gallery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GalleryController extends Controller
{
    private const THUMB_MAX_DIMENSION = 250;
    private const ORIGINAL_MAX_DIMENSION = 2000; // largura/altura máxima do arquivo principal

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $photos = DB::table('user_gallery_photos')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view($this->resolveView('index'), compact('user', 'photos'));
    }

    public function create()
    {
        return view($this->resolveView('create'));
    }

    public function store(Request $request)
    {
        // Lift common PHP-level limits for heavier uploads before validation kicks in.
        @ini_set('upload_max_filesize', '64M');
        @ini_set('post_max_size', '64M');
        @ini_set('memory_limit', '512M');

        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'file|mimetypes:image/jpeg,image/png,image/gif,image/webp,image/avif,image/tiff,image/bmp,image/x-icon,image/svg+xml',
            'caption' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $originalDir = 'uploads/gallery/photos';
        $thumbnailDir = 'uploads/gallery/thumbs';

        $this->ensureDirectoryExists($originalDir);
        $this->ensureDirectoryExists($thumbnailDir);

        foreach ($request->file('photos') as $photo) {
            $image = $this->createImageResource($photo->getRealPath());

            if (! $image) {
                throw ValidationException::withMessages([
                    'photos' => 'Não foi possível processar uma das imagens enviadas.',
                ]);
            }

            $baseName = $this->buildBaseName($photo);
            $originalPath = $originalDir . '/' . $baseName . '.webp';
            $thumbnailPath = $thumbnailDir . '/' . $baseName . '_thumb.webp';

            $resized = $this->resizeImageToMaxDimension($image, self::ORIGINAL_MAX_DIMENSION);

            if (! $resized) {
                imagedestroy($image);

                throw ValidationException::withMessages([
                    'photos' => 'Não foi possível otimizar as dimensões da imagem enviada.',
                ]);
            }

            if ($resized !== $image) {
                imagedestroy($image);
                $image = $resized;
            }

            $encodedOriginal = $this->encodeWebp($image, 85);

            if ($encodedOriginal === null) {
                imagedestroy($image);

                throw ValidationException::withMessages([
                    'photos' => 'Não foi possível otimizar a imagem enviada.',
                ]);
            }

            file_put_contents(public_path($originalPath), $encodedOriginal);

            $thumbnail = $this->generateThumbnail($image);

            if (! $thumbnail) {
                imagedestroy($image);

                throw ValidationException::withMessages([
                    'photos' => 'Não foi possível gerar a miniatura da imagem enviada.',
                ]);
            }

            $thumbnailData = $this->encodeWebp($thumbnail, 80);

            if ($thumbnailData === null) {
                imagedestroy($image);
                imagedestroy($thumbnail);

                throw ValidationException::withMessages([
                    'photos' => 'Não foi possível gerar a miniatura da imagem enviada.',
                ]);
            }

            file_put_contents(public_path($thumbnailPath), $thumbnailData);

            imagedestroy($image);
            imagedestroy($thumbnail);


            DB::table('user_gallery_photos')->insert([
                'user_id' => $user->id,
                'image_path' => $originalPath,
                'thumbnail_path' => $thumbnailPath,
                'caption' => $request->caption,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('gallery.index')->with('success', 'Fotos enviadas com sucesso!');
    }

    public function show($id)
    {
        $photo = DB::table('user_gallery_photos')->where('id', $id)->first();

        if (! $photo) {
            return redirect()->route('gallery.index')->with('error', 'Foto não encontrada.');
        }

        $user = DB::table('users')->where('id', $photo->user_id)->first();

        return view($this->resolveView('show'), compact('photo', 'user'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $photo = DB::table('user_gallery_photos')->where('id', $id)->first();

        if (! $photo || $photo->user_id !== $user->id) {
            return redirect()->route('gallery.index')->with('error', 'Você não tem permissão para excluir esta foto.');
        }

        $this->deleteIfExists($photo->image_path);
        $this->deleteIfExists($photo->thumbnail_path ?? null);

        DB::table('user_gallery_photos')->where('id', $id)->delete();

        return redirect()->route('gallery.index')->with('success', 'Foto removida com sucesso!');
    }

    public function userGallery($username)
    {
        $user = DB::table('users')->where('name', $username)->first();

        if (! $user) {
            return redirect()->route('gallery.index')->with('error', 'Usuário não encontrado.');
        }

        $photos = DB::table('user_gallery_photos')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view($this->resolveView('user'), compact('user', 'photos'));
    }

    protected function resolveView(string $view): string
    {
        return view()->exists("gallery.$view") ? "gallery.$view" : "gallery::$view";
    }

    protected function ensureDirectoryExists(string $path): void
    {
        $fullPath = public_path($path);

        if (! is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }

    protected function createImageResource(string $path)
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $image = imagecreatefromstring($contents);

        if ($image === false) {
            return null;
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    protected function resizeImageToMaxDimension($image, int $maxDimension)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if (! $width || ! $height) {
            return null;
        }

        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $image;
        }

        $scale = min($maxDimension / $width, $maxDimension / $height);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if (! $resized) {
            return null;
        }

        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        if (! imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
            imagedestroy($resized);

            return null;
        }

        return $resized;
    }

    protected function generateThumbnail($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if (! $width || ! $height) {
            return null;
        }

        $scale = min(self::THUMB_MAX_DIMENSION / $width, self::THUMB_MAX_DIMENSION / $height, 1);
        $thumbWidth = max(1, (int) round($width * $scale));
        $thumbHeight = max(1, (int) round($height * $scale));

        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);

        if (! $thumbnail) {
            return null;
        }

        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);

        if (! imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height)) {
            imagedestroy($thumbnail);

            return null;
        }

        return $thumbnail;
    }

    protected function encodeWebp($image, int $quality = 85): ?string
    {
        ob_start();
        imagewebp($image, null, $quality);
        $data = ob_get_clean();

        if ($data === false) {
            return null;
        }

        return $data;
    }

    protected function deleteIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    protected function buildBaseName(UploadedFile $photo): string
    {
        $originalName = $photo->getClientOriginalName();
        $rawBase = pathinfo($originalName, PATHINFO_FILENAME);

        // Permite letras (com acentos), números, espaços e os caracteres solicitados.
        $sanitized = preg_replace('/[^\p{L}\p{N}_\-\[\]\(\)\s]+/u', '', $rawBase ?? '');
        $sanitized = trim(preg_replace('/\s+/', '_', $sanitized), '_');

        if ($sanitized === '') {
            $sanitized = 'gallery';
        }

        return $sanitized . '_' . uniqid();
    }
}