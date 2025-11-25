<div class="card mb-4">
    <div class="card-body">
        <div class="mb-3">
            <label for="name" class="form-label">Nome da campanha *</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $campaign->name ?? '') }}" required maxlength="150">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Descrição</label>
            <textarea name="description" id="description" class="form-control" maxlength="500" rows="2">{{ old('description', $campaign->description ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="thank_you_message" class="form-label">Mensagem de agradecimento</label>
            <textarea name="thank_you_message" id="thank_you_message" class="form-control" maxlength="1000" rows="3" placeholder="Obrigado! Em breve entraremos em contato.">{{ old('thank_you_message', $campaign->thank_you_message ?? '') }}</textarea>
        </div>

        <div class="mb-4">
            <label for="status" class="form-label">Status *</label>
            <select name="status" id="status" class="form-select" required>
                <option value="active" {{ old('status', $campaign->status ?? 'active') === 'active' ? 'selected' : '' }}>Ativa</option>
                <option value="inactive" {{ old('status', $campaign->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inativa</option>
            </select>
        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h2 class="h5 mb-0">Campos do formulário</h2>
                <small class="text-muted">Cadastre até {{ \plugins\leads01\Http\Controllers\Leads01Controller::FIELD_LIMIT }} campos personalizados. Use campos do tipo "select" quando precisar de opções.</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-field">Adicionar campo</button>
        </div>

        <div id="fields-wrapper">
            @php
                $oldFields = old('fields', isset($fields) ? $fields->toArray() : []);
                if (count($oldFields) === 0) {
                    $oldFields = [[
                        'label' => 'Nome',
                        'field_name' => 'nome',
                        'field_type' => 'text',
                        'required' => true,
                        'placeholder' => 'Digite seu nome',
                        'options' => [],
                    ]];
                }
            @endphp

            @foreach($oldFields as $index => $field)
                <div class="card mb-3 field-item" data-index="{{ $index }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Campo #{{ $index + 1 }}</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-field">Remover</button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Rótulo *</label>
                                <input type="text" name="fields[{{ $index }}][label]" class="form-control" value="{{ old('fields.' . $index . '.label', $field['label'] ?? '') }}" required maxlength="150">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nome interno</label>
                                <input type="text" name="fields[{{ $index }}][field_name]" class="form-control" value="{{ old('fields.' . $index . '.field_name', $field['field_name'] ?? '') }}" maxlength="150" placeholder="opcional - usamos o rótulo quando vazio">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo *</label>
                                <select name="fields[{{ $index }}][field_type]" class="form-select field-type" required>
                                    @foreach(['text' => 'Texto', 'email' => 'E-mail', 'number' => 'Número', 'tel' => 'Telefone', 'textarea' => 'Texto longo', 'select' => 'Lista (select)'] as $value => $label)
                                        <option value="{{ $value }}" {{ (old('fields.' . $index . '.field_type', $field['field_type'] ?? 'text') === $value) ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-block">Obrigatório?</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="fields[{{ $index }}][required]" value="1" {{ old('fields.' . $index . '.required', $field['required'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">Sim</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Placeholder</label>
                                <input type="text" name="fields[{{ $index }}][placeholder]" class="form-control" value="{{ old('fields.' . $index . '.placeholder', $field['placeholder'] ?? '') }}" maxlength="255">
                            </div>
                            <div class="col-12 select-options" style="display: {{ (old('fields.' . $index . '.field_type', $field['field_type'] ?? 'text') === 'select') ? 'block' : 'none' }};">
                                <label class="form-label">Opções (uma por linha, mínimo 2)</label>
                                @php
                                    $options = $field['options'] ?? [];
                                    if (is_string($options)) {
                                        $options = json_decode($options, true) ?? [];
                                    }
                                @endphp
                                <textarea name="fields[{{ $index }}][options][]" class="form-control" rows="3" placeholder="Opção 1\nOpção 2">{{ implode("\n", array_filter($options)) }}</textarea>
                                <small class="text-muted">Se o tipo for "select", informe pelo menos duas opções.</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('fields-wrapper');
        const addBtn = document.getElementById('add-field');
        const limit = {{ \plugins\leads01\Http\Controllers\Leads01Controller::FIELD_LIMIT }};

        const refreshVisibility = () => {
            wrapper.querySelectorAll('.field-item').forEach(card => {
                const typeSelect = card.querySelector('.field-type');
                const optionsBox = card.querySelector('.select-options');
                if (typeSelect && optionsBox) {
                    optionsBox.style.display = typeSelect.value === 'select' ? 'block' : 'none';
                }
            });
        };

        wrapper.addEventListener('change', (event) => {
            if (event.target.classList.contains('field-type')) {
                refreshVisibility();
            }
        });

        wrapper.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-field')) {
                const card = event.target.closest('.field-item');
                if (card) {
                    card.remove();
                }
            }
        });

        addBtn.addEventListener('click', () => {
            const current = wrapper.querySelectorAll('.field-item').length;
            if (current >= limit) {
                alert('Você atingiu o limite de ' + limit + ' campos.');
                return;
            }

            const index = current;
            const template = `
                <div class="card mb-3 field-item" data-index="${index}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Campo #${index + 1}</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-field">Remover</button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Rótulo *</label>
                                <input type="text" name="fields[${index}][label]" class="form-control" required maxlength="150">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nome interno</label>
                                <input type="text" name="fields[${index}][field_name]" class="form-control" maxlength="150" placeholder="opcional - usamos o rótulo quando vazio">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo *</label>
                                <select name="fields[${index}][field_type]" class="form-select field-type" required>
                                    <option value="text">Texto</option>
                                    <option value="email">E-mail</option>
                                    <option value="number">Número</option>
                                    <option value="tel">Telefone</option>
                                    <option value="textarea">Texto longo</option>
                                    <option value="select">Lista (select)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-block">Obrigatório?</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="fields[${index}][required]" value="1">
                                    <label class="form-check-label">Sim</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Placeholder</label>
                                <input type="text" name="fields[${index}][placeholder]" class="form-control" maxlength="255">
                            </div>
                            <div class="col-12 select-options" style="display:none;">
                                <label class="form-label">Opções (uma por linha, mínimo 2)</label>
                                <textarea name="fields[${index}][options][]" class="form-control" rows="3" placeholder="Opção 1\nOpção 2"></textarea>
                                <small class="text-muted">Se o tipo for "select", informe pelo menos duas opções.</small>
                            </div>
                        </div>
                    </div>
                </div>`;

            wrapper.insertAdjacentHTML('beforeend', template);
            refreshVisibility();
        });

        refreshVisibility();
    });
</script>
@endpush