<?php

namespace App\Providers\plugins\leads01;

// Carrega os Models manualmente antes de usar
$modelsPath = base_path('plugins/leads01/Models');
if (!class_exists('plugins\\leads01\\Models\\LeadField')) {
    require_once $modelsPath . '/LeadField.php';
}
if (!class_exists('plugins\\leads01\\Models\\LeadEntry')) {
    require_once $modelsPath . '/LeadEntry.php';
}
if (!class_exists('plugins\\leads01\\Models\\LeadCampaign')) {
    require_once $modelsPath . '/LeadCampaign.php';
}

// Cria aliases no namespace global (com \\ no início)
if (!class_exists('\\LeadCampaign', false)) {
    class_alias('plugins\\leads01\\Models\\LeadCampaign', '\\LeadCampaign');
}
if (!class_exists('\\LeadEntry', false)) {
    class_alias('plugins\\leads01\\Models\\LeadEntry', '\\LeadEntry');
}
if (!class_exists('\\LeadField', false)) {
    class_alias('plugins\\leads01\\Models\\LeadField', '\\LeadField');
}

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Models já foram carregados manualmente acima, usar namespace completo no código

class Leads01Controller extends Controller
{
    public const FIELD_LIMIT = 10;

    public function index()
    {
        $user = auth()->user();

        $campaigns = \LeadCampaign::where('user_id', $user->id)
            ->withCount('entries')
            ->latest()
            ->paginate(15);

 return view($this->resolveView('index'), compact('campaigns'));
    }

    public function create()
    {
        return view($this->resolveView('create'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $this->validateCampaign($request);
        $fields = $this->validateFields($request);

        $campaign = \LeadCampaign::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'thank_you_message' => $validated['thank_you_message'] ?? null,
            'status' => $validated['status'],
        ]);

        $this->persistFields($campaign, $fields);

        return redirect()->route('leads01.index')
            ->with('success', 'Campanha criada e campos salvos com sucesso.');
    }

    public function edit(int $id)
    {
        $campaign = $this->findCampaign($id);

        return view($this->resolveView('edit'), [
            'campaign' => $campaign,
            'fields' => $campaign->fields()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $campaign = $this->findCampaign($id);

        $validated = $this->validateCampaign($request, $campaign->id);
        $fields = $this->validateFields($request);

        $campaign->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'thank_you_message' => $validated['thank_you_message'] ?? null,
            'status' => $validated['status'],
        ]);

        $this->persistFields($campaign, $fields);

        return redirect()->route('leads01.index')
            ->with('success', 'Campanha atualizada com sucesso.');
    }

    public function destroy(int $id)
    {
        $campaign = $this->findCampaign($id);
        $campaign->entries()->delete();
        $campaign->fields()->delete();
        $campaign->delete();

        return redirect()->route('leads01.index')
            ->with('success', 'Campanha excluída com sucesso.');
    }

    public function leads(int $id)
    {
        $campaign = $this->findCampaign($id);

        $entries = $campaign->entries()
            ->latest()
            ->paginate(20);

        return view($this->resolveView('leads.index'), compact('campaign', 'entries'));
    }

    public function showLead(int $id, int $entryId)
    {
        $campaign = $this->findCampaign($id);

        $entry = $campaign->entries()
            ->with('fields')
            ->findOrFail($entryId);

        return view($this->resolveView('leads.show'), [
            'campaign' => $campaign,
            'entry' => $entry,
        ]);
    }

    public function publicList(string $username)
    {
        $user = User::where('name', $username)->firstOrFail();

        $campaigns = \LeadCampaign::where('user_id', $user->id)
            ->where('status', 'active')
            ->withCount('entries')
            ->latest()
            ->get();

        return view($this->resolveView('public.list'), compact('user', 'campaigns'));
    }

    public function publicForm(string $slug)
    {
        $campaign = \LeadCampaign::where('slug', $slug)
            ->where('status', 'active')
            ->with('fields')
            ->firstOrFail();

        return view($this->resolveView('public.form'), compact('campaign'));
    }

    public function submit(Request $request, string $slug)
    {
        $campaign = \LeadCampaign::where('slug', $slug)
            ->where('status', 'active')
            ->with('fields')
            ->firstOrFail();

        $rules = [];
        foreach ($campaign->fields as $field) {
            $baseRule = $field->required ? 'required' : 'nullable';
            $rules['field_' . $field->id] = $baseRule . '|' . $this->fieldRule($field->type);
        }

        $validated = $request->validate($rules);

        $entry = \LeadEntry::create([
            'campaign_id' => $campaign->id,
            'user_id' => $campaign->user_id,
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->header('User-Agent'),
        ]);

        foreach ($campaign->fields as $field) {
            $entry->fields()->create([
                'lead_field_id' => $field->id,
                'value' => $validated['field_' . $field->id] ?? null,
            ]);
        }

        return redirect()
            ->route('leads01.form', $campaign->slug)
            ->with('success', $campaign->thank_you_message ?: 'Obrigado pelo envio.');
    }

    protected function validateCampaign(Request $request, ?int $campaignId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thank_you_message' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);
    }

    protected function validateFields(Request $request): array
    {
        $fields = $request->input('fields', []);

        if (count($fields) > self::FIELD_LIMIT) {
            throw ValidationException::withMessages([
                'fields' => 'Você excedeu o limite de ' . self::FIELD_LIMIT . ' campos.',
            ]);
        }

        $validated = [];
        foreach ($fields as $index => $field) {
            $name = trim($field['name'] ?? '');
            $type = $field['type'] ?? '';

            if ($name === '') {
                throw ValidationException::withMessages([
                    "fields.$index.name" => 'O nome do campo é obrigatório.',
                ]);
            }

            if (strlen($name) > 255) {
                throw ValidationException::withMessages([
                    "fields.$index.name" => 'O nome do campo deve ter no máximo 255 caracteres.',
                ]);
            }

            if (! in_array($type, ['text', 'textarea', 'email', 'select'], true)) {
                throw ValidationException::withMessages([
                    "fields.$index.type" => 'Tipo de campo inválido.',
                ]);
            }

            $options = $field['options'] ?? [];
            if ($type === 'select') {
                if (!is_array($options) || empty(array_filter($options))) {
                    throw ValidationException::withMessages([
                        "fields.$index.options" => 'Selecione pelo menos uma opção para o campo select.',
                    ]);
                }

                $options = array_values(array_filter(array_map('trim', $options)));
            } else {
                $options = [];
            }

            $validated[] = [
                'name' => $name,
                'type' => $type,
                'required' => (bool) ($field['required'] ?? false),
                'sort_order' => $index + 1,
                'options' => $options,
            ];
        }

        return $validated;
    }

    protected function fieldRule(string $type): string
    {
        return match ($type) {
            'email' => 'email|max:255',
            'textarea' => 'string|max:2000',
            default => 'string|max:255',
        };
    }

    protected function persistFields(LeadCampaign $campaign, array $fields): void
    {
        $campaign->fields()->delete();

        foreach ($fields as $field) {
            $campaign->fields()->create([
                'name' => $field['name'],
                'type' => $field['type'],
                'required' => $field['required'],
                'sort_order' => $field['sort_order'],
                'options' => $field['options'],
            ]);
        }
    }

    protected function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (\LeadCampaign::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function findCampaign(int $id): \LeadCampaign
    {
        $user = auth()->user();

        return \LeadCampaign::where('user_id', $user->id)
            ->with('fields')
            ->findOrFail($id);
    }
	   protected function resolveView(string $view): string
    {
        return view()->exists("leads01.$view") ? "leads01.$view" : "leads01::$view";
    }
}