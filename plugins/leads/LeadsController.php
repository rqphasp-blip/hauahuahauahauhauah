<?php

namespace App\Providers\plugins\leads;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LeadsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Página principal: lista de campanhas do usuário
     */
    public function index()
    {
        $user = Auth::user();

        $campaigns = DB::table('user_lead_campaigns')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('leads::index', compact('user', 'campaigns'));
    }

    /**
     * Formulário para criar uma nova campanha
     */
    public function create()
    {
        return view('leads::create');
    }

    /**
     * Armazena uma nova campanha
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        $slug = str_slug($request->name) . '-' . uniqid();

        DB::table('user_lead_campaigns')->insert([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('leads.index')->with('success', 'Campanha criada com sucesso!');
    }

    /**
     * Edita uma campanha existente
     */
    public function edit($id)
    {
        $user = Auth::user();

        $campaign = DB::table('user_lead_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return redirect()->route('leads.index')->with('error', 'Campanha não encontrada.');
        }

        return view('leads::create', compact('campaign'));
    }

    /**
     * Atualiza campanha
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        DB::table('user_lead_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => now(),
            ]);

        return redirect()->route('leads.index')->with('success', 'Campanha atualizada com sucesso!');
    }

    /**
     * Exclui campanha e todos os dados vinculados
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $campaign = DB::table('user_lead_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return redirect()->route('leads.index')->with('error', 'Campanha não encontrada.');
        }

        // Exclui campos e leads relacionados
        DB::table('user_lead_fields')->where('campaign_id', $id)->delete();
        DB::table('user_lead_entries')->where('campaign_id', $id)->delete();
        DB::table('user_lead_campaigns')->where('id', $id)->delete();

        return redirect()->route('leads.index')->with('success', 'Campanha excluída com sucesso!');
    }

    /**
     * Página do builder (Sortable.js)
     */
    public function builder($id)
    {
        $user = Auth::user();

        $campaign = DB::table('user_lead_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return redirect()->route('leads.index')->with('error', 'Campanha não encontrada.');
        }

        $fields = DB::table('user_lead_fields')
            ->where('campaign_id', $campaign->id)
            ->orderBy('order', 'asc')
            ->get();

        return view('leads::builder', compact('campaign', 'fields'));
    }

    /**
     * Salva os campos ordenados do form builder (Sortable.js)
     */
    public function builderSave(Request $request, $id)
    {
        $user = Auth::user();

        $campaign = DB::table('user_lead_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return response()->json(['error' => 'Campanha inválida.'], 403);
        }

        $fields = $request->input('fields');
        if (!is_array($fields)) {
            return response()->json(['error' => 'Formato inválido.'], 400);
        }

        // Limpa campos antigos
        DB::table('user_lead_fields')->where('campaign_id', $campaign->id)->delete();

        // Reinsere os novos campos com ordem
        foreach ($fields as $index => $field) {
            DB::table('user_lead_fields')->insert([
                'campaign_id' => $campaign->id,
                'label' => $field['label'] ?? 'Campo ' . ($index + 1),
                'type' => $field['type'] ?? 'text',
                'required' => isset($field['required']) ? (int)$field['required'] : 0,
                'order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Lista de leads capturados por campanha
     */
    public function entries($id)
    {
        $user = Auth::user();

        $campaign = DB::table('user_lead_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return redirect()->route('leads.index')->with('error', 'Campanha não encontrada.');
        }

        $entries = DB::table('user_lead_entries')
            ->where('campaign_id', $campaign->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('leads::entries', compact('campaign', 'entries'));
    }

    /**
     * Exibe detalhes de um lead específico
     */
    public function show($id, $entryId)
    {
        $user = Auth::user();

        $campaign = DB::table('user_lead_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return redirect()->route('leads.index')->with('error', 'Campanha não encontrada.');
        }

        $entry = DB::table('user_lead_entries')
            ->where('id', $entryId)
            ->where('campaign_id', $campaign->id)
            ->first();

        if (!$entry) {
            return redirect()->route('leads.entries', $campaign->id)->with('error', 'Lead não encontrado.');
        }

        $data = json_decode($entry->data, true);

        return view('leads::show', compact('campaign', 'entry', 'data'));
    }
}
