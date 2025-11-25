<?php

namespace plugins\leads01\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use plugins\leads01\Models\LeadCampaign;
use plugins\leads01\Models\LeadEntry;

class Leads01Controller extends Controller
{
    public const FIELD_LIMIT = 10;

    public function index()
    {
        $user = auth()->user();

        $campaigns = LeadCampaign::where('user_id', $user->id)
            ->withCount('entries')
            ->latest()
            ->paginate(15);

        return view('leads01::index', compact('campaigns'));
    }

    public function create()
    {
        return view('leads01::create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $this->validateCampaign($request);
        $fields = $this->validateFields($request);

        $campaign = LeadCampaign::create([
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

        return view('leads01::edit', [
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
            ->with('success', 'Campanha atualizada e campos salvos com sucesso.');
    }

    public function destroy(int $id)
    {
        $campaign = $this->findCampaign($id);
        $campaign->delete();

        return redirect()->route('leads01.index')
            ->with('success', 'Campanha removida. Todos os leads e campos associados foram apagados.');
    }

    public function leads(int $id)
    {
        $campaign = $this->findCampaign($id);

        $leads = LeadEntry::where('campaign_id', $campaign->id)
            ->latest()
            ->paginate(20);

        return view('leads01::leads.index', compact('campaign', 'leads'));
    }

    public function showLead(int $id, int $entryId)
    {
        $campaign = $this->findCampaign($id);

        $lead = LeadEntry::where('campaign_id', $campaign->id)
            ->where('id', $entryId)
            ->firstOrFail();

        $fields = $campaign->fields()->orderBy('sort_order')->get();

        return view('leads01::leads.show', compact('campaign', 'lead', 'fields'));
    }

    public function publicList(string $username)
    {
        $user = User::where('name', $username)->firstOrFail();

        $campaigns = LeadCampaign::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('leads01::public.list', compact('user', 'campaigns'));
    }

    public function publicForm(string $slug)
    {
        $campaign = LeadCampaign::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $fields = $campaign->fields()->orderBy('sort_order')->get();

        return view('leads01::public.form', compact('campaign', 'fields'));
    }

    public function submit(string $slug, Request $request)
    {
        $campaign = LeadCampaign::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $fields = $campaign->fields()->orderBy('sort_order')->get();

        if ($fields->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum campo configurado para este formulário.');
        }

        $validation = [];
        foreach ($fields as $field) {
            $rules = $field->required ? ['required'] : ['nullable'];

            switch ($field->field_type) {
                case 'email':
                    $rules[] = 'email';
                    $rules[] = 'max:150';
                    break;
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'tel':
                    $rules[] = 'string';
                    $rules[] = 'max:30';
                    break;
                case 'textarea':
                    $rules[] = 'string';
                    $rules[] = 'max:2000';
                    break;
                case 'select':
                    $rules[] = 'string';
                    $rules[] = 'max:150';
                    break;
                default:
                    $rules[] = 'string';
                    $rules[] = 'max:255';
                    break;
            }

            $validation[$field->field_name] = $rules;
        }

        $data = $request->validate($validation);

        LeadEntry::create([
            'campaign_id' => $campaign->id,
            'user_id' => $campaign->user_id,
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $message = trim((string) $campaign->thank_you_message);

        return redirect()->back()
            ->with('success', $message !== '' ? $message : 'Obrigado! Em breve entraremos em contato.');
    }

    protected function validateCampaign(Request $request, ?int $campaignId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'thank_you_message' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    protected function validateFields(Request $request): array
    {
        $fields = $request->input('fields', []);

        if (!is_array($fields) || count($fields) === 0) {
            throw ValidationException::withMessages([
                'fields' => 'Adicione pelo menos um campo para o formulário.',
            ]);
        }

        if (count($fields) > self::FIELD_LIMIT) {
            throw ValidationException::withMessages([
                'fields' => 'Você pode cadastrar no máximo ' . self::FIELD_LIMIT . ' campos.',
            ]);
        }

        $prepared = [];
        $usedNames = [];

        foreach ($fields as $index => $field) {
            $label = trim((string) ($field['label'] ?? ''));
            $type = $field['field_type'] ?? 'text';
            $placeholder = $field['placeholder'] ?? null;
            $options = $field['options'] ?? [];
            if (is_array($options) && count($options) === 1 && is_string($options[0])) {
                $options = preg_split('/\r?\n/', $options[0]);
            }
            if (is_string($options)) {
                $options = preg_split('/\r?\n/', $options);
            }
            $required = !empty($field['required']);

            if ($label === '') {
                throw ValidationException::withMessages([
                    'fields.' . $index . '.label' => 'Informe um rótulo para o campo ' . ($index + 1) . '.',
                ]);
            }

            $nameBase = Str::slug($field['field_name'] ?? $label, '_');
            if ($nameBase === '') {
                $nameBase = 'campo_' . ($index + 1);
            }

            $name = $nameBase;
            $suffix = 1;
            while (in_array($name, $usedNames, true)) {
                $name = $nameBase . '_' . $suffix++;
            }
            $usedNames[] = $name;

            if ($type === 'select') {
                if (!is_array($options) || count(array_filter($options)) < 2) {
                    throw ValidationException::withMessages([
                        'fields.' . $index . '.options' => 'Cadastre ao menos duas opções para campos do tipo select.',
                    ]);
                }
            }

            $prepared[] = [
                'label' => $label,
                'field_name' => $name,
                'field_type' => in_array($type, ['text', 'email', 'number', 'tel', 'textarea', 'select'], true) ? $type : 'text',
                'required' => $required,
                'placeholder' => $placeholder !== null ? trim((string) $placeholder) : null,
                'options' => $type === 'select' ? array_values(array_filter($options, fn ($opt) => trim((string) $opt) !== '')) : null,
                'sort_order' => $index,
            ];
        }

        return $prepared;
    }

    protected function persistFields(LeadCampaign $campaign, array $fields): void
    {
        $campaign->fields()->delete();

        foreach ($fields as $field) {
            $campaign->fields()->create($field);
        }
    }

    protected function findCampaign(int $id): LeadCampaign
    {
        $user = auth()->user();

        return LeadCampaign::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    protected function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $suffix = 1;

        while (LeadCampaign::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}