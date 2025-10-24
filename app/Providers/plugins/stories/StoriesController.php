<?php

namespace App\Providers\plugins\stories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe a página de gerenciamento de stories
     */
    public function index()
    {
        $user = Auth::user();
        $stories = DB::table('user_stories')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Filtrar stories com menos de 24 horas
        $activeStories = $stories->filter(function($story) {
            $createdAt = new \DateTime($story->created_at);
            $now = new \DateTime();
            $diff = $now->diff($createdAt);
            
            // Retorna true se o story foi criado há menos de 24 horas
            return ($diff->days == 0 && $diff->h < 24);
        });
        
        return view('stories.index', compact('user', 'activeStories'));
    }

    /**
     * Exibe o formulário para criar um novo story
     */
    public function create()
    {
        return view('stories.create');
    }

    /**
     * Processa o upload do story
     */
    public function store(Request $request)
    {
        $request->validate([
            'story_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Cria o diretório se não existir
        $uploadDir = 'uploads/stories';
        $fullPath = public_path($uploadDir);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Salva a imagem do story
        $image = $request->file('story_image');
        $filename = uniqid('story_', true) . '.' . $image->getClientOriginalExtension();
        $path = $uploadDir . '/' . $filename;
        
        $image->move(public_path($uploadDir), $filename);

        // Insere o registro no banco de dados
        DB::table('user_stories')->insert([
            'user_id' => $user->id,
            'image_path' => $path,
            'caption' => $request->caption,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('stories.index')->with('success', 'Story publicado com sucesso! Ficará visível por 24 horas.');
    }

    /**
     * Exibe um story específico
     */
    public function show($id)
    {
        $story = DB::table('user_stories')->where('id', $id)->first();
        
        if (!$story) {
            return redirect()->route('stories.index')->with('error', 'Story não encontrado.');
        }
        
        // Verificar se o story ainda está ativo (menos de 24 horas)
        $createdAt = new \DateTime($story->created_at);
        $now = new \DateTime();
        $diff = $now->diff($createdAt);
        
        if ($diff->days > 0 || $diff->h >= 24) {
            return redirect()->route('stories.index')->with('error', 'Este story expirou.');
        }
        
        $user = DB::table('users')->where('id', $story->user_id)->first();
        
        return view('stories.show', compact('story', 'user'));
    }

    /**
     * Remove um story
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $story = DB::table('user_stories')->where('id', $id)->first();
        
        if (!$story || $story->user_id != $user->id) {
            return redirect()->route('stories.index')->with('error', 'Você não tem permissão para excluir este story.');
        }

        // Remove a imagem do story se existir
        if ($story->image_path && file_exists(public_path($story->image_path))) {
            unlink(public_path($story->image_path));
        }
        
        // Remove o registro do banco de dados
        DB::table('user_stories')->where('id', $id)->delete();

        return redirect()->route('stories.index')->with('success', 'Story removido com sucesso!');
    }
    
    /**
     * Exibe os stories de um usuário específico
     */
    public function userStories($username)
    {
        $user = DB::table('users')->where('name', $username)->first();
        
        if (!$user) {
            return redirect()->route('stories.index')->with('error', 'Usuário não encontrado.');
        }
        
        $stories = DB::table('user_stories')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Filtrar stories com menos de 24 horas
        $activeStories = $stories->filter(function($story) {
            $createdAt = new \DateTime($story->created_at);
            $now = new \DateTime();
            $diff = $now->diff($createdAt);
            
            // Retorna true se o story foi criado há menos de 24 horas
            return ($diff->days == 0 && $diff->h < 24);
        });
        
        return view('stories.user', compact('user', 'activeStories'));
    }
}
