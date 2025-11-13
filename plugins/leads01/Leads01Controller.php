<?php

namespace plugins\leads01;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Leads01Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['display', 'submit']);
    }

    public function index()
    {
        $user = Auth::user();

        $campaigns = DB::table('leads01_campaigns')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('leads01::campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('leads01::campaigns.form');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'thank_you_message' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $suffix = 1;

        while (DB::table('leads01_campaigns')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        DB::table('leads01_campaigns')->insert([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'thank_you_message' => $validated['thank_you_message'] ?? 'Obrigado! Em breve entraremos em contato.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('leads01.index')->with('success', 'Campanha criada com sucesso.');
    }

    public function edit(int $id)
    {
        $campaign = $this->findCampaignForUser($id);

        if (!$campaign) {
            return redirect()->route('leads01.index')->with('error', 'Campanha não encontrada.');
        }

        return view('leads01::campaigns.form', compact('campaign'));
    }

    public function update(Request $request, int $id)
    {
        $campaign = $this->findCampaignForUser($id);

        if (!$campaign) {
            return redirect()->route('leads01.index')->with('error', 'Campanha não encontrada.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'thank_you_message' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('leads01_campaigns')
            ->where('id', $campaign->id)
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'thank_you_message' => $validated['thank_you_message'] ?? 'Obrigado! Em breve entraremos em contato.',
                'updated_at' => now(),
            ]);

        return redirect()->route('leads01.index')->with('success', 'Campanha atualizada com sucesso.');
    }

    public function destroy(int $id)
    {
        $campaign = $this->findCampaignForUser($id);

        if (!$campaign) {
            return redirect()->route('leads01.index')->with('error', 'Campanha não encontrada.');
        }

        DB::transaction(function () use ($campaign) {
            DB::table('leads01_fields')->where('campaign_id', $campaign->id)->delete();
            DB::table('leads01_entries')->where('campaign_id', $campaign->id)->delete();
            DB::table('leads01_campaigns')->where('id', $campaign->id)->delete();
        });

        return redirect()->route('leads01.index')->with('success', 'Campanha removida.');
    }

    public function fields(int $id)
    {
        $campaign = $this->findCampaignForUser($id);

        if (!$campaign) {
            return redirect()->route('leads01.index')->with('error', 'Campanha não encontrada.');
        }

        $fields = DB::table('leads01_fields')
            ->where('campaign_id', $campaign->id)
            ->orderBy('sort_order')
            ->get();

        return view('leads01::campaigns.fields', compact('campaign', 'fields'));
    }

    public function saveFields(Request $request, int $id)
    {
        $campaign = $this->findCampaignForUser($id);

        if (!$campaign) {
            return response()->json(['message' => 'Campanha inválida.'], 404);
        }

        $fields = $request->input('fields');

        if (!is_array($fields) || count($fields) === 0) {
            return response()->json(['message' => 'Informe ao menos um campo.'], 422);
        }

        if (count($fields) > 10) {
            return response()->json(['message' => 'Você pode configurar no máximo 10 campos.'], 422);
        }

        $prepared = [];
        $usedNames = [];

        foreach ($fields as $index => $field) {
            $label = trim($field['label'] ?? '');
            $baseName = Str::slug($field['name'] ?? $label, '_');

            if ($baseName === '') {
                $baseName = 'campo_' . $index;
            }

            $name = $baseName;
            $suffix = 1;
            while (in_array($name, $usedNames, true)) {
                $name = $baseName . '_' . $suffix++;
            }

            $usedNames[] = $name;

            $prepared[] = [
                'label' => $label !== '' ? $label : 'Campo ' . ($index + 1),
                'field_name' => $name,
                'field_type' => $field['type'] ?? 'text',
                'required' => !empty($field['required']) ? 1 : 0,
                'placeholder' => $field['placeholder'] ?? null,
                'options' => isset($field['options']) ? json_encode($field['options']) : null,
                'sort_order' => $index,
            ];
        }

        DB::transaction(function () use ($campaign, $prepared) {
            DB::table('leads01_fields')->where('campaign_id', $campaign->id)->delete();

            foreach ($prepared as $field) {
                DB::table('leads01_fields')->insert([
                    'campaign_id' => $campaign->id,
                    'label' => $field['label'],
                    'field_name' => $field['field_name'],
                    'field_type' => $field['field_type'],
                    'required' => $field['required'],
                    'placeholder' => $field['placeholder'],
                    'options' => $field['options'],
                    'sort_order' => $field['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json(['message' => 'Campos salvos com sucesso.']);
    }

    public function leads(int $id)
    {
        $campaign = $this->findCampaignForUser($id);

        if (!$campaign) {
            return redirect()->route('leads01.index')->with('error', 'Campanha não encontrada.');
        }

        $entries = DB::table('leads01_entries')
            ->where('campaign_id', $campaign->id)
            ->orderByDesc('created_at')
            ->get();

        return view('leads01::leads.index', compact('campaign', 'entries'));
    }

    public function lead(int $id, int $entryId)
    {
        $campaign = $this->findCampaignForUser($id);

        if (!$campaign) {
            return redirect()->route('leads01.index')->with('error', 'Campanha não encontrada.');
        }

        $entry = DB::table('leads01_entries')
            ->where('campaign_id', $campaign->id)
            ->where('id', $entryId)
            ->first();

        if (!$entry) {
            return redirect()->route('leads01.leads', $campaign->id)->with('error', 'Lead não encontrado.');
        }

        $data = json_decode($entry->data, true) ?? [];

        return view('leads01::leads.show', compact('campaign', 'entry', 'data'));
    }

    public function display(string $slug)
    {
        $campaign = DB::table('leads01_campaigns')->where('slug', $slug)->first();

        if (!$campaign) {
            abort(404);
        }

        $fields = DB::table('leads01_fields')
            ->where('campaign_id', $campaign->id)
            ->orderBy('sort_order')
            ->get();

        return view('leads01::display.modal', compact('campaign', 'fields'));
    }

    public function submit(Request $request, string $slug)
    {
        $campaign = DB::table('leads01_campaigns')->where('slug', $slug)->first();

        if (!$campaign) {
            return response()->json(['message' => 'Campanha inválida.'], 404);
        }

        $fields = DB::table('leads01_fields')
            ->where('campaign_id', $campaign->id)
            ->orderBy('sort_order')
            ->get();

        if ($fields->isEmpty()) {
            return response()->json(['message' => 'Nenhum campo configurado.'], 422);
        }

        $rules = [];
        $attributes = [];

        foreach ($fields as $field) {
            $rule = [];

            if ($field->required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            switch ($field->field_type) {
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'tel':
                    $rule[] = 'string';
                    $rule[] = 'max:30';
                    break;
                case 'textarea':
                    $rule[] = 'string';
                    $rule[] = 'max:1000';
                    break;
                default:
                    $rule[] = 'string';
                    $rule[] = 'max:255';
                    break;
            }

            $rules['fields.' . $field->field_name] = $rule;
            $attributes['fields.' . $field->field_name] = $field->label;
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = [];
        foreach ($fields as $field) {
            $payload[$field->label] = $request->input('fields.' . $field->field_name);
        }

        DB::table('leads01_entries')->insert([
            'campaign_id' => $campaign->id,
            'data' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => $campaign->thank_you_message ?? 'Obrigado! Em breve entraremos em contato.',
        ]);
    }

    protected function findCampaignForUser(int $id)
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return DB::table('leads01_campaigns')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
    }
}