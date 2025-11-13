<?php

namespace App\Providers\plugins\gallery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GalleryController extends Controller
{
    private const THUMB_MAX_SIZE = 102400; // 100 KB
    private const THUMB_MAX_DIMENSION = 320;

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
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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

            $baseName = uniqid('gallery_', true);
            $originalPath = $originalDir . '/' . $baseName . '.webp';
            $thumbnailPath = $thumbnailDir . '/' . $baseName . '_thumb.webp';

            if (! $this->saveWebp($image, public_path($originalPath))) {
                imagedestroy($image);

                throw ValidationException::withMessages([
                    'photos' => 'Não foi possível salvar a imagem enviada.',
                ]);
            }

            $thumbnail = $this->generateThumbnail($image);

            if (! $thumbnail) {
                imagedestroy($image);

                throw ValidationException::withMessages([
                    'photos' => 'Não foi possível gerar a miniatura da imagem enviada.',
                ]);
            }

            $thumbnailData = $this->encodeThumbnail($thumbnail);

            if ($thumbnailData === null) {
                imagedestroy($image);
                imagedestroy($thumbnail);

                throw ValidationException::withMessages([
                    'photos' => 'A miniatura excede o limite de 100KB. Tente enviar uma imagem menor.',
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

    protected function saveWebp($image, string $destination): bool
    {
        return imagewebp($image, $destination, 80);
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

    protected function encodeThumbnail($thumbnail): ?string
    {
        $quality = 80;

        while ($quality >= 10) {
            ob_start();
            imagewebp($thumbnail, null, $quality);
            $data = ob_get_clean();

            if ($data === false) {
                return null;
            }

            if (strlen($data) <= self::THUMB_MAX_SIZE) {
                return $data;
            }

            $quality -= 5;
        }

        return null;
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
}