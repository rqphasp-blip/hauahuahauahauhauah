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
        $fields    = $this->validateFields($request);

        $campaign = \LeadCampaign::create([
            'user_id'           => $user->id,
            'name'              => $validated['name'],
            'slug'              => $this->uniqueSlug($validated['name']),
            'description'       => $validated['description'] ?? null,
            'thank_you_message' => $validated['thank_you_message'] ?? null,
            'status'            => $validated['status'],
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
            'fields'   => $campaign->fields()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $campaign = $this->findCampaign($id);

        $validated = $this->validateCampaign($request, $campaign->id);
        $fields    = $this->validateFields($request);

        $campaign->update([
            'name'              => $validated['name'],
            'description'       => $validated['description'] ?? null,
            'thank_you_message' => $validated['thank_you_message'] ?? null,
            'status'            => $validated['status'],
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

        // compatível com a view resources/views/leads01/leads/index.blade.php
        $leads = $campaign->entries()
            ->latest()
            ->paginate(20);

        return view($this->resolveView('leads.index'), compact('campaign', 'leads'));
    }

    public function showLead(int $id, int $entryId)
    {
        $campaign = $this->findCampaign($id);

        $entry = $campaign->entries()
            ->with('fields')
            ->findOrFail($entryId);

        return view($this->resolveView('leads.show'), [
            'campaign' => $campaign,
            'entry'    => $entry,
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
            // usa o mesmo field_type armazenado no campo
            $rules['field_' . $field->id] = $baseRule . '|' . $this->fieldRule($field->field_type);
        }

        $validated = $request->validate($rules);

        $entry = \LeadEntry::create([
            'campaign_id' => $campaign->id,
            'user_id'     => $campaign->user_id,
            'submitted_at'=> now(),
            'ip_address'  => $request->ip(),
            'user_agent'  => (string) $request->header('User-Agent'),
        ]);

        foreach ($campaign->fields as $field) {
            $entry->fields()->create([
                'lead_field_id' => $field->id,
                'value'         => $validated['field_' . $field->id] ?? null,
            ]);
        }

        return redirect()
            ->route('leads01.form', $campaign->slug)
            ->with('success', $campaign->thank_you_message ?: 'Obrigado pelo envio.');
    }

    protected function validateCampaign(Request $request, ?int $campaignId = null): array
    {
        return $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'thank_you_message' => 'nullable|string|max:500',
            'status'            => 'required|in:active,inactive',
        ]);
    }

    protected function validateFields(Request $request): array
    {
        $fields = $request->input('fields', []);

        if (!is_array($fields)) {
            return [];
        }

        if (count($fields) > self::FIELD_LIMIT) {
            throw ValidationException::withMessages([
                'fields' => 'Você excedeu o limite de ' . self::FIELD_LIMIT . ' campos.',
            ]);
        }

        // Tipos permitidos devem bater com o select do _form.blade.php
        $allowedTypes = ['text', 'textarea', 'email', 'select', 'number', 'tel'];

        $validated = [];

        foreach ($fields as $index => $field) {
            if (!is_array($field)) {
                continue;
            }

            $label       = trim($field['label'] ?? '');
            $fieldName   = trim($field['field_name'] ?? '');
            $type        = $field['field_type'] ?? '';
            $required    = (bool) ($field['required'] ?? false);
            $placeholder = trim($field['placeholder'] ?? '');

            if ($label === '') {
                throw ValidationException::withMessages([
                    "fields.$index.label" => 'O rótulo do campo é obrigatório.',
                ]);
            }

            if (strlen($label) > 255) {
                throw ValidationException::withMessages([
                    "fields.$index.label" => 'O rótulo do campo deve ter no máximo 255 caracteres.',
                ]);
            }

            if (!in_array($type, $allowedTypes, true)) {
                throw ValidationException::withMessages([
                    "fields.$index.field_type" => 'Tipo de campo inválido.',
                ]);
            }

            // Se não foi definido um nome interno, geramos a partir do rótulo
            if ($fieldName === '') {
                $fieldName = Str::slug($label, '_');
            }

            // Tratamento das opções do select
            $options    = [];
            $rawOptions = $field['options'] ?? [];

            if ($type === 'select') {
                $lines = [];

                if (is_array($rawOptions)) {
                    // Pode vir como um array com uma string contendo várias linhas
                    foreach ($rawOptions as $opt) {
                        if (is_string($opt)) {
                            $parts = preg_split("/\r\n|\r|\n/", $opt);
                            foreach ($parts as $p) {
                                $lines[] = trim($p);
                            }
                        }
                    }
                } elseif (is_string($rawOptions)) {
                    $parts = preg_split("/\r\n|\r|\n/", $rawOptions);
                    foreach ($parts as $p) {
                        $lines[] = trim($p);
                    }
                }

                $options = array_values(array_filter($lines, fn ($v) => $v !== ''));

                if (count($options) < 2) {
                    throw ValidationException::withMessages([
                        "fields.$index.options" => 'Selecione pelo menos duas opções para o campo select.',
                    ]);
                }
            }

            $validated[] = [
                'label'       => $label,
                'field_name'  => $fieldName,
                'field_type'  => $type,
                'required'    => $required,
                'placeholder' => $placeholder,
                'sort_order'  => $index + 1,
                'options'     => $options,
            ];
        }

        return $validated;
    }

    protected function fieldRule(string $type): string
    {
        return match ($type) {
            'email'    => 'email|max:255',
            'textarea' => 'string|max:2000',
            'number'   => 'numeric',
            'tel'      => 'string|max:20',
            default    => 'string|max:255',
        };
    }

    protected function persistFields(\LeadCampaign $campaign, array $fields): void
    {
        // Remove todos os campos antigos e recria
        $campaign->fields()->delete();

        foreach ($fields as $field) {
            $campaign->fields()->create([
                'label'       => $field['label'],
                'field_name'  => $field['field_name'],
                'field_type'  => $field['field_type'],
                'required'    => $field['required'],
                'placeholder' => $field['placeholder'] ?? null,
                'sort_order'  => $field['sort_order'],
                'options'     => $field['options'], // assumindo cast json no Model
            ]);
        }
    }

    protected function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug     = $baseSlug;
        $counter  = 1;

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
