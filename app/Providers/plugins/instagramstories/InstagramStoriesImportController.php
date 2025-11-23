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

        $response = Http::timeout(10)->get($request->instagram_url);

        if (! $response->successful()) {
            return back()->with('error', 'Não foi possível acessar o link informado. Verifique a URL do story do Instagram.');
        }

        $contentType = $response->header('Content-Type');

        if (! $contentType || ! isset($allowedTypes[$contentType])) {
            return back()->with('error', 'O conteúdo retornado não é uma mídia suportada (imagens: jpeg, png, gif, webp ou vídeos: mp4, mov, m4v, webm).');
        }

        $body = $response->body();

        if (strlen($body) === 0) {
            return back()->with('error', 'Nenhum conteúdo foi retornado pelo link informado.');
        }

        $isVideo = str_starts_with($contentType, 'video/');
        $maxSizeBytes = $isVideo ? 50 * 1024 * 1024 : 5 * 1024 * 1024;

        if (strlen($body) > $maxSizeBytes) {
            $limitMb = $isVideo ? '50MB' : '5MB';

            return back()->with('error', "O arquivo retornado é maior que {$limitMb}. Selecione um story menor.");
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

        DB::table('user_stories')->insert([
            'user_id' => $user->id,
            'image_path' => $relativePath,
            'caption' => $request->caption,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('stories.index')->with('success', 'Story importado do Instagram com sucesso!');
    }
}