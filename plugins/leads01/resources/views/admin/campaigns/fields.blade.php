@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">{{ __('Campos do formulário') }}</h2>
            <p class="text-muted mb-0">{{ __('Arraste para reordenar e adicione até :max campos personalizados.', ['max' => \plugins\leads01\Http\Controllers\LeadCaptureController::FIELD_LIMIT]) }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('leads01.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Voltar') }}
            </a>
            <a href="{{ route('leads01.entries', $campaign) }}" class="btn btn-outline-success">
                <i class="bi bi-people"></i> {{ __('Ver leads') }}
            </a>
        </div>
    </div>

    <div class="alert alert-info d-flex align-items-start gap-2">
        <i class="bi bi-info-circle fs-4"></i>
        <div>
            <strong>{{ __('Dica rápida!') }}</strong>
            <p class="mb-0">{{ __('Utilize o modal para criar novos campos e arraste os itens para definir a ordem.') }}</p>
        </div>
    </div>

    <div class="card shadow-sm" id="lead-fields-builder"
         data-campaign="{{ $campaign->id }}"
         data-limit="{{ \plugins\leads01\Http\Controllers\LeadCaptureController::FIELD_LIMIT }}"
         data-fields='@json($fields->map(fn($field) => [
            "id" => $field->id,
            "label" => $field->label,
            "type" => $field->type,
            "required" => (bool) $field->required,
         ]))'>
        <div class="card-body">
            <ul class="list-group list-group-flush" id="lead-fields-list">
                <li class="list-group-item py-4 text-center text-muted d-none" id="lead-fields-empty">
                    {{ __('Nenhum campo cadastrado ainda. Adicione o primeiro para começar!') }}
                </li>
            </ul>
        </div>
        <div class="card-footer d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-primary" type="button" id="lead-field-add">
                    <i class="bi bi-plus-circle"></i> {{ __('Adicionar campo') }}
                </button>
                <small class="text-muted" id="lead-field-limit" hidden>
                    {{ __('Você atingiu o limite máximo de campos.') }}
                </small>
            </div>
            <button class="btn btn-success ms-md-auto" type="button" id="lead-field-save">
                <span class="label-default"><i class="bi bi-save"></i> {{ __('Salvar alterações') }}</span>
                <span class="label-loading d-none"><span class="spinner-border spinner-border-sm"></span></span>
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="leadFieldModal" tabindex="-1" aria-labelledby="leadFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadFieldModalLabel">{{ __('Adicionar campo') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fechar') }}"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="lead-field-label" class="form-label">{{ __('Nome do campo') }}</label>
                    <input type="text" class="form-control" id="lead-field-label" maxlength="120" required>
                </div>
                <div class="mb-3">
                    <label for="lead-field-type" class="form-label">{{ __('Tipo de campo') }}</label>
                    <select class="form-select" id="lead-field-type">
                        <option value="text">{{ __('Texto') }}</option>
                        <option value="email">{{ __('E-mail') }}</option>
                        <option value="phone">{{ __('Telefone') }}</option>
                        <option value="textarea">{{ __('Texto longo') }}</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="lead-field-required">
                    <label class="form-check-label" for="lead-field-required">
                        {{ __('Campo obrigatório') }}
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-primary" id="lead-field-confirm">{{ __('Adicionar') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const builder = document.getElementById('lead-fields-builder');
        if (!builder) return;

        const limit = Number(builder.dataset.limit || 10);
        const fields = JSON.parse(builder.dataset.fields || '[]');
        const list = document.getElementById('lead-fields-list');
        const emptyState = document.getElementById('lead-fields-empty');
        const addButton = document.getElementById('lead-field-add');
        const saveButton = document.getElementById('lead-field-save');
        const limitText = document.getElementById('lead-field-limit');
        const modalElement = document.getElementById('leadFieldModal');
        const modal = new bootstrap.Modal(modalElement);
        const labelInput = document.getElementById('lead-field-label');
        const typeSelect = document.getElementById('lead-field-type');
        const requiredInput = document.getElementById('lead-field-required');
        const confirmButton = document.getElementById('lead-field-confirm');

        let currentFields = Array.isArray(fields) ? fields : [];

        const refreshList = () => {
            list.querySelectorAll('li.list-group-item').forEach(item => {
                if (item !== emptyState) {
                    item.remove();
                }
            });

            if (currentFields.length === 0) {
                emptyState.classList.remove('d-none');
                list.prepend(emptyState);
            } else {
                emptyState.classList.add('d-none');
            }

            currentFields.forEach((field, index) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center justify-content-between gap-3';
                li.dataset.index = index;
                li.innerHTML = `
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <span class="text-muted" title="{{ __('Arrastar para reordenar') }}">
                            <i class="bi bi-grip-vertical fs-4"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <strong>${field.label}</strong>
                                <span class="badge bg-light text-dark text-uppercase">${typeLabel(field.type)}</span>
                                ${field.required ? '<span class="badge bg-warning text-dark">{{ __('Obrigatório') }}</span>' : ''}
                            </div>
                            <small class="text-muted">{{ __('Campo') }} #${index + 1}</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" type="button">
                        <i class="bi bi-trash"></i>
                    </button>
                `;

                li.querySelector('button').addEventListener('click', () => {
                    currentFields.splice(index, 1);
                    refreshList();
                });

                list.appendChild(li);
            });

            limitText.hidden = currentFields.length < limit;
            addButton.disabled = currentFields.length >= limit;
        };

        const typeLabel = (type) => {
            switch (type) {
                case 'email': return 'E-mail';
                case 'phone': return 'Telefone';
                case 'textarea': return 'Texto longo';
                default: return 'Texto';
            }
        };

        refreshList();

        Sortable.create(list, {
            animation: 150,
            handle: 'span',
            draggable: 'li.list-group-item:not(#lead-fields-empty)',
            onEnd: () => {
                const reordered = [];
                list.querySelectorAll('li.list-group-item:not(#lead-fields-empty)').forEach(item => {
                    const index = Number(item.dataset.index);
                    if (!Number.isNaN(index) && currentFields[index]) {
                        reordered.push(currentFields[index]);
                    }
                });
                currentFields = reordered;
                refreshList();
            }
        });

        addButton.addEventListener('click', () => {
            labelInput.value = '';
            typeSelect.value = 'text';
            requiredInput.checked = false;
            modal.show();
        });

        confirmButton.addEventListener('click', () => {
            const label = labelInput.value.trim();
            if (!label) {
                alert('{{ __('Informe um nome para o campo.') }}');
                return;
            }

            if (currentFields.length >= limit) {
                alert('{{ __('Limite máximo de campos atingido.') }}');
                return;
            }

            currentFields.push({
                id: null,
                label,
                type: typeSelect.value,
                required: requiredInput.checked,
            });

            refreshList();
            modal.hide();
        });

        saveButton.addEventListener('click', async () => {
            saveButton.disabled = true;
            saveButton.querySelector('.label-default').classList.add('d-none');
            saveButton.querySelector('.label-loading').classList.remove('d-none');

            try {
                const response = await fetch('{{ route('leads01.fields.save', $campaign) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ fields: currentFields }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erro ao salvar.');
                }

                alert(data.message);
            } catch (error) {
                alert(error.message);
            } finally {
                saveButton.disabled = false;
                saveButton.querySelector('.label-default').classList.remove('d-none');
                saveButton.querySelector('.label-loading').classList.add('d-none');
            }
        });
    });
</script>
@endpush