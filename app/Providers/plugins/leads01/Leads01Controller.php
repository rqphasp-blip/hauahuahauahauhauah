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
use Illuminate\Support\Facades\DB;

class Leads01Controller extends Controller
{
    public const FIELD_LIMIT = 10;

    /**
     * Ativa / desativa a visibilidade de uma campanha na página pública.
     * Regra: apenas UMA campanha por usuário pode estar com visivel = 1.
     */
    public function toggleVisible(int $id)
    {
        $user = auth()->user();

        $campaign = \LeadCampaign::where('user_id', $user->id)->findOrFail($id);

        // Se já está visível, apenas oculta (nenhuma visível)
        if ((int) $campaign->visivel === 1) {
            $campaign->visivel = 0;
            $campaign->save();

            return back()->with('success', 'Formulário ocultado da página pública.');
        }

        // Se NÃO está visível:
        // 1) zera todas as campanhas do usuário
        // 2) marca só esta como visível
        DB::transaction(function () use ($user, $campaign) {
            \LeadCampaign::where('user_id', $user->id)->update(['visivel' => 0]);
            $campaign->visivel = 1;
            $campaign->save();
        });

        return back()->with('success', 'Formulário definido como visível na página pública.');
    }

    public function index()
    {
        $user = auth()->user();

        $campaigns = \LeadCampaign::where('user_id', $user->id)
            ->withCount('entries')
            ->latest()
            ->paginate(15);

        return $this->renderView('index', compact('campaigns'));
    }

    public function create()
    {
        return $this->renderView('create');
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
            'visivel'           => 0, // nova campanha começa não visível
        ]);

        $this->persistFields($campaign, $fields);

        return redirect()->route('leads01.index')
            ->with('success', 'Campanha criada e campos salvos com sucesso.');
    }

    public function edit(int $id)
    {
        $campaign = $this->findCampaign($id);

        return $this->renderView('edit', [
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

        $entries = $campaign->entries()
            ->latest()
            ->paginate(20);

        return $this->renderView('leads.index', compact('campaign', 'entries'));
    }

    public function showLead(int $id, int $entryId)
    {
        $campaign = $this->findCampaign($id);

        $entry = $campaign->entries()
            ->with('fields')
            ->findOrFail($entryId);

        return $this->renderView('leads.show', [
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

        return $this->renderView('public.list', compact('user', 'campaigns'));
    }

    public function publicForm(string $slug)
    {
        $campaign = \LeadCampaign::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $fields = $campaign->fields()
            ->orderBy('sort_order')
            ->get();

        return $this->renderView('public.form', compact('campaign', 'fields'));
    }

    public function submit(Request $request, string $slug)
    {
        $campaign = \LeadCampaign::where('slug', $slug)
            ->where('status', 'active')
            ->with('fields')
            ->firstOrFail();

        $rules = [];
        foreach ($campaign->fields as $field) {
            $baseRule   = $field->required ? 'required' : 'nullable';
            $fieldType  = $field->field_type ?? $field->type ?? 'text';
            $rules['field_' . $field->id] = $baseRule . '|' . $this->fieldRule($fieldType);
        }

        $validated = $request->validate($rules);

        $entryData = [];
        foreach ($campaign->fields as $field) {
            $key = $field->field_name
                ?? ($field->name ?? ('field_' . $field->id));

            $entryData[$key] = $validated['field_' . $field->id] ?? null;
        }

        \LeadEntry::create([
            'campaign_id' => $campaign->id,
            'user_id'     => $campaign->user_id,
            'data'        => $entryData,
            'ip_address'  => $request->ip(),
            'user_agent'  => (string) $request->header('User-Agent'),
        ]);

        return redirect()
            ->route('leads01.public.form', $campaign->slug)
            ->with('success', $campaign->thank_you_message ?: 'Obrigado pelo envio.');
    }

    public function saveFields(Request $request, int $id)
    {
        $campaign = $this->findCampaign($id);

        $incoming = $request->input('fields', []);

        if (!is_array($incoming) || count($incoming) === 0) {
            return response()->json([
                'errors' => ['fields' => 'Envie pelo menos um campo para salvar.'],
            ], 422);
        }

        $normalized = [];
        foreach ($incoming as $field) {
            $fieldType = strtolower((string) ($field['field_type'] ?? ($field['type'] ?? 'text')));

            $normalized[] = [
                'label'       => trim((string) ($field['label'] ?? '')),
                'field_name'  => trim((string) ($field['field_name'] ?? ($field['name'] ?? ''))),
                'field_type'  => $fieldType,
                'required'    => (bool) ($field['required'] ?? false),
                'placeholder' => trim((string) ($field['placeholder'] ?? '')),
                'options'     => $this->normalizeOptions($field['options'] ?? [], $fieldType),
                'sort_order'  => $field['sort_order'] ?? $field['order'] ?? null,
            ];
        }

        $request->replace(['fields' => $normalized]);

        try {
            $fields = $this->validateFields($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $this->persistFields($campaign, $fields);

        return response()->json(['message' => 'Campos salvos com sucesso.']);
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

    /**
     * Validação dos campos do formulário, compatível com _form.blade.php:
     * - fields[*][label]
     * - fields[*][field_name] (opcional)
     * - fields[*][field_type] (text, email, number, tel, textarea, select)
     * - fields[*][required]
     * - fields[*][placeholder]
     * - fields[*][options][] (textarea, linhas separadas por \n)
     */
    protected function validateFields(Request $request): array
    {
        $fields = $request->input('fields', []);

        if (!is_array($fields)) {
            $fields = [];
        }

        // Ordena pelo sort_order/order caso venha do formulário em JSON
        $fields = array_values($fields);
        usort($fields, function ($a, $b) {
            $aOrder = (int) ($a['sort_order'] ?? $a['order'] ?? 0);
            $bOrder = (int) ($b['sort_order'] ?? $b['order'] ?? 0);

            return $aOrder <=> $bOrder;
        });

        if (count($fields) > self::FIELD_LIMIT) {
            throw ValidationException::withMessages([
                'fields' => 'Você excedeu o limite de ' . self::FIELD_LIMIT . ' campos.',
            ]);
        }

        $validated = [];

        foreach ($fields as $index => $field) {
            $label       = trim($field['label'] ?? '');
            $fieldName   = trim($field['field_name'] ?? '');
            $type        = strtolower((string) ($field['field_type'] ?? ($field['type'] ?? 'text')));
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

            // tipos permitidos de acordo com o select do formulário
            $allowedTypes = ['text', 'email', 'number', 'tel', 'textarea', 'select'];
            if (!in_array($type, $allowedTypes, true)) {
                throw ValidationException::withMessages([
                    "fields.$index.field_type" => 'Tipo de campo inválido.',
                ]);
            }

            // Trata opções do select
            $options = $this->normalizeOptions($field['options'] ?? [], $type);

            if ($type === 'select' && count($options) < 1) {
                throw ValidationException::withMessages([
                    "fields.$index.options" => 'Selecione pelo menos uma opção para o campo select.',
                ]);
            }

            // Nome interno: se não veio, gera slug a partir do label
            $name = $fieldName !== '' ? $fieldName : Str::slug($label, '_');

            $sortOrder = (int) ($field['sort_order'] ?? $field['order'] ?? ($index + 1));

            $validated[] = [
                'label'       => $label,
                'name'        => $name,
                'type'        => $type,
                'required'    => (bool) ($field['required'] ?? false),
                'sort_order'  => $sortOrder,
                'placeholder' => $placeholder,
                'options'     => $options,
            ];
        }

        return $validated;
    }

    protected function normalizeOptions($rawOptions, ?string $type = null): array
    {
        if (($type ?? '') !== 'select') {
            return [];
        }

        if (is_array($rawOptions)) {
            $rawString = implode("\n", $rawOptions);
        } else {
            $rawString = (string) $rawOptions;
        }

        $lines   = preg_split("/[\r\n]+/", $rawString);
        $options = array_values(array_filter(array_map('trim', $lines)));

        return $options;
    }

    protected function fieldRule(?string $type): string
    {
        $normalized = strtolower($type ?: 'text');

        return match ($normalized) {
            'email'    => 'email|max:255',
            'number'   => 'numeric',
            'tel'      => 'string|max:30',
            'textarea' => 'string|max:2000',
            default    => 'string|max:255',
        };
    }

    /**
     * Usa o alias global \LeadCampaign para evitar problema de namespace.
     */
    protected function persistFields(\LeadCampaign $campaign, array $fields): void
    {
        $campaign->fields()->delete();

        foreach ($fields as $field) {
            $campaign->fields()->create([
                'label'       => $field['label'],
                'field_name'  => $field['name'],
                'field_type'  => $field['type'],
                'required'    => $field['required'],
                'sort_order'  => $field['sort_order'],
                'options'     => $field['options'],
                'placeholder' => $field['placeholder'] ?: null,
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

    protected function renderView(string $view, array $data = [])
    {
        foreach ($this->viewCandidates($view) as $candidate) {
            if (view()->exists($candidate)) {
                return view($candidate, $data);
            }
        }

        $paths = [
            resource_path('views/' . str_replace('.', '/', $view) . '.blade.php'),
            resource_path('views/leads01/' . str_replace('.', '/', $view) . '.blade.php'),
            base_path('plugins/leads01/resources/views/' . str_replace('.', '/', $view) . '.blade.php'),
            base_path('plugins/leads01/views/' . str_replace('.', '/', $view) . '.blade.php'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return view()->file($path, $data);
            }
        }

        abort(404, "View [{$view}] not found for leads01.");
    }

    protected function viewCandidates(string $view): array
    {
        return [
            "leads01.$view",
            "leads01::{$view}",
            "leads01::$view",
            $view,
        ];
    }
}
