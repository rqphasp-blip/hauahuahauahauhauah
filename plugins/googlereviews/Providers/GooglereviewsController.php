<?php
namespace App\Providers\plugins\googlereviews;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class GooglereviewsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe a página de gerenciamento de estabelecimentos
     */
    public function index()
    {
        $establishments = DB::table('google_reviews_places')
            ->orderBy('name', 'asc')
            ->get();
            
        return view('googlereviews.index', compact('establishments'));
    }

    /**
     * Exibe o formulário para adicionar um novo estabelecimento
     */
    public function create()
    {
        return view('googlereviews.create');
    }

    /**
     * Processa e armazena um novo estabelecimento
     */
    public function store(Request $request)
    {
        $request->validate([
            'place_id' => 'required|string|unique:google_reviews_places,place_id',
            'name' => 'required|string|max:255',
        ]);

        // Insere o registro no banco de dados
        DB::table('google_reviews_places')->insert([
            'place_id' => $request->place_id,
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('googlereviews.index')->with('success', 'Estabelecimento adicionado com sucesso!');
    }

    /**
     * Exibe detalhes de um estabelecimento específico
     */
    public function show($id)
    {
        $establishment = DB::table('google_reviews_places')->where('id', $id)->first();
        
        if (!$establishment) {
            return redirect()->route('googlereviews.index')->with('error', 'Estabelecimento não encontrado.');
        }
        
        // Buscar dados atualizados da API do Google Places
        $placeDetails = $this->getPlaceDetails($establishment->place_id);
        
        return view('googlereviews.show', compact('establishment', 'placeDetails'));
    }

    /**
     * Atualiza informações de um estabelecimento
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $establishment = DB::table('google_reviews_places')->where('id', $id)->first();
        
        if (!$establishment) {
            return redirect()->route('googlereviews.index')->with('error', 'Estabelecimento não encontrado.');
        }

        // Atualiza o registro no banco de dados
        DB::table('google_reviews_places')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'updated_at' => now(),
            ]);

        return redirect()->route('googlereviews.show', $id)->with('success', 'Estabelecimento atualizado com sucesso!');
    }

    /**
     * Remove um estabelecimento
     */
    public function destroy($id)
    {
        $establishment = DB::table('google_reviews_places')->where('id', $id)->first();
        
        if (!$establishment) {
            return redirect()->route('googlereviews.index')->with('error', 'Estabelecimento não encontrado.');
        }
        
        // Remove o registro do banco de dados
        DB::table('google_reviews_places')->where('id', $id)->delete();

        return redirect()->route('googlereviews.index')->with('success', 'Estabelecimento removido com sucesso!');
    }
    
    /**
     * Retorna o widget de avaliação para um estabelecimento
     */
    public function widget($place_id)
    {
        // Buscar dados da API do Google Places
        $placeDetails = $this->getPlaceDetails($place_id);
        
        if (!$placeDetails || isset($placeDetails['error'])) {
            return response()->json(['error' => 'Não foi possível obter as avaliações deste estabelecimento.'], 404);
        }
        
        return view('googlereviews.widget', compact('placeDetails'));
    }
    
    /**
     * Exibe a página de configuração das credenciais da API
     */
    public function config()
    {
        $config = DB::table('google_reviews_config')->first();
        return view('googlereviews.config', compact('config'));
    }
    
    /**
     * Salva as configurações da API
     */
    public function saveConfig(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
        ]);
        
        // Verifica se já existe uma configuração
        $config = DB::table('google_reviews_config')->first();
        
        if ($config) {
            // Atualiza a configuração existente
            DB::table('google_reviews_config')
                ->update([
                    'api_key' => $request->api_key,
                    'updated_at' => now(),
                ]);
        } else {
            // Cria uma nova configuração
            DB::table('google_reviews_config')
                ->insert([
                    'api_key' => $request->api_key,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }
        
        return redirect()->route('googlereviews.config')->with('success', 'Configurações salvas com sucesso!');
    }
    
    /**
     * Obtém os detalhes de um estabelecimento da API do Google Places
     */
    private function getPlaceDetails($placeId)
    {
        // Obter a chave da API das configurações
        $config = DB::table('google_reviews_config')->first();
        
        if (!$config || !$config->api_key) {
            return ['error' => 'API key não configurada'];
        }
        
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'name,rating,user_ratings_total,reviews',
                'key' => $config->api_key,
            ]);
            
            $data = $response->json();
            
            if ($response->successful() && isset($data['result'])) {
                return $data['result'];
            } else {
                return ['error' => $data['error_message'] ?? 'Erro ao obter dados da API'];
            }
        } catch (\Exception $e) {
            return ['error' => 'Erro ao conectar com a API do Google: ' . $e->getMessage()];
        }
    }
}
