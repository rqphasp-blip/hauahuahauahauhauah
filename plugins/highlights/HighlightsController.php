<?php

namespace plugins\highlights;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HighlightsController extends Controller
{
    private const MAX_HIGHLIGHTS = 5;
    private const UPLOAD_DIR = 'uploads/highlights';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $highlights = DB::table('user_highlights')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $remainingSlots = max(0, self::MAX_HIGHLIGHTS - $highlights->count());

        return view($this->resolveView('index'), compact('highlights', 'remainingSlots'));
    }

    public function create()
    {
        $user = Auth::user();
        $highlightCount = DB::table('user_highlights')->where('user_id', $user->id)->count();

        if ($highlightCount >= self::MAX_HIGHLIGHTS) {
            return redirect()->route('highlights.index')->with('error', 'Você já atingiu o limite de 5 destaques.');
        }

        $remainingSlots = self::MAX_HIGHLIGHTS - $highlightCount;

        return view($this->resolveView('create'), compact('remainingSlots'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $highlightCount = DB::table('user_highlights')->where('user_id', $user->id)->count();

        if ($highlightCount >= self::MAX_HIGHLIGHTS) {
            return redirect()->route('highlights.index')->with('error', 'Você já atingiu o limite de 5 destaques.');
        }

        $request->validate([
            'title' => 'required|string|max:50',
            'media' => 'required|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/mpeg,video/webm|max:5120',
        ]);

        $media = $request->file('media');
        $mimeType = $media->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        if ($isVideo) {
            $duration = $this->getVideoDuration($media->getPathname());
            if ($duration !== null && $duration > 30) {
                return redirect()->back()->withInput()->with('error', 'O vídeo deve ter no máximo 30 segundos.');
            }
        }

        $this->ensureUploadDirectoryExists();

        $filename = uniqid('highlight_', true) . '.' . $media->getClientOriginalExtension();
        $media->move(public_path(self::UPLOAD_DIR), $filename);

        DB::table('user_highlights')->insert([
            'user_id' => $user->id,
            'title' => $request->title,
            'media_path' => self::UPLOAD_DIR . '/' . $filename,
            'media_type' => $isVideo ? 'video' : 'image',
            'mime_type' => $mimeType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('highlights.index')->with('success', 'Destaque criado com sucesso!');
    }

    public function show($id)
    {
        $highlight = DB::table('user_highlights')->where('id', $id)->first();

        if (!$highlight || $highlight->user_id !== Auth::id()) {
            return redirect()->route('highlights.index')->with('error', 'Destaque não encontrado.');
        }

        return view($this->resolveView('show'), compact('highlight'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $highlight = DB::table('user_highlights')->where('id', $id)->first();

        if (!$highlight || $highlight->user_id !== $user->id) {
            return redirect()->route('highlights.index')->with('error', 'Você não tem permissão para excluir este destaque.');
        }

        if ($highlight->media_path && file_exists(public_path($highlight->media_path))) {
            unlink(public_path($highlight->media_path));
        }

        DB::table('user_highlights')->where('id', $id)->delete();

        return redirect()->route('highlights.index')->with('success', 'Destaque removido com sucesso!');
    }

    private function resolveView(string $view): string
    {
        return view()->exists("highlights.$view") ? "highlights.$view" : "highlights::$view";
    }

    private function ensureUploadDirectoryExists(): void
    {
        $fullPath = public_path(self::UPLOAD_DIR);

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }

    private function getVideoDuration(string $path): ?float
    {
        $command = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg($path);
        $output = @shell_exec($command);

        if ($output === null || $output === false) {
            return null;
        }

        $duration = (float) trim($output);

        return $duration > 0 ? $duration : null;
    }
}