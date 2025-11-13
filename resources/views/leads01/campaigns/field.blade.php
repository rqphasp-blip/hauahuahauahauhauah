@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Campos do formulário</h2>
            <small class="text-muted">{{ $campaign->name }}</small>
        </div>
        <div class="btn-group">
            <a href="{{ route('leads01.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Campanhas
            </a>
            <a href="{{ route('leads01.leads', $campaign->id) }}" class="btn btn-outline-success">
                <i class="bi bi-people"></i> Leads
            </a>
        </div>
    </div>

    <div class="alert alert-info">
        Configure até <strong>10 campos</strong>. Use o botão "Adicionar campo" para criar novos campos e arraste-os para reordenar.
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div id="fields-list" class="vstack gap-2">
                @forelse ($fields as $field)
                    <div class="card border border-dashed" data-id="{{ $field->id }}" data-name="{{ $field->field_name }}" data-type="{{ $field->field_type }}" data-required="{{ $field->required }}" data-placeholder="{{ $field->placeholder }}" data-options='{{ $field->options }}'>
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $field->label }}</h6>
                                <small class="text-muted">{{ strtoupper($field->field_type) }} @if($field->required) · obrigatório @endif</small>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-field"><i class="bi bi-pencil"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-field"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5" id="empty-placeholder">
                        Nenhum campo configurado ainda.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-primary" id="add-field"><i class="bi bi-plus-circle"></i> Adicionar campo</button>
            <button type="button" class="btn btn-success" id="save-fields"><i class="bi bi-save"></i> Salvar campos</button>
        </div>
    </div>
</div>

@include('leads01::campaigns.partials.field-modal')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('fields-list');
        let sortable = Sortable.create(container, {
            animation: 150,
            handle: '.card',
            filter: '#empty-placeholder',
            onStart: () => {
                document.getElementById('empty-placeholder')?.classList.add('d-none');
            }
        });

        const modalElement = document.getElementById('fieldModal');
        const fieldModal = new bootstrap.Modal(modalElement);
        const fieldForm = document.getElementById('field-form');
        const modalTitle = document.getElementById('fieldModalLabel');
        const removeEmptyPlaceholder = () => document.getElementById('empty-placeholder')?.remove();

        let editingCard = null;

        document.getElementById('add-field').addEventListener('click', () => {
            if (container.querySelectorAll('.card').length >= 10) {
                alert('Você pode adicionar no máximo 10 campos.');
                return;
            }
            editingCard = null;
            modalTitle.textContent = 'Adicionar campo';
            fieldForm.reset();
            document.getElementById('field-options-wrapper').classList.add('d-none');
            fieldModal.show();
        });

        container.addEventListener('click', (event) => {
            const target = event.target.closest('button');
            if (!target) return;

            const card = event.target.closest('.card');

            if (target.classList.contains('remove-field')) {
                card.remove();
                if (!container.querySelector('.card')) {
                    container.insertAdjacentHTML('beforeend', `<div class="text-center text-muted py-5" id="empty-placeholder">Nenhum campo configurado ainda.</div>`);
                }
                return;
            }

            if (target.classList.contains('edit-field')) {
                editingCard = card;
                modalTitle.textContent = 'Editar campo';
                fieldForm.reset();

                document.getElementById('field-label').value = card.querySelector('h6').textContent.trim();
                document.getElementById('field-name').value = card.dataset.name;
                document.getElementById('field-type').value = card.dataset.type;
                document.getElementById('field-required').checked = card.dataset.required === '1';
                document.getElementById('field-placeholder').value = card.dataset.placeholder || '';

                const optionsWrapper = document.getElementById('field-options-wrapper');
                if (card.dataset.type === 'select') {
                    optionsWrapper.classList.remove('d-none');
                    const options = JSON.parse(card.dataset.options || '[]');
                    document.getElementById('field-options').value = options.join('\n');
                } else {
                    optionsWrapper.classList.add('d-none');
                    document.getElementById('field-options').value = '';
                }

                fieldModal.show();
            }
        });

        document.getElementById('field-type').addEventListener('change', (event) => {
            const wrapper = document.getElementById('field-options-wrapper');
            if (event.target.value === 'select') {
                wrapper.classList.remove('d-none');
            } else {
                wrapper.classList.add('d-none');
            }
        });

        fieldForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const label = document.getElementById('field-label').value.trim();
            const name = document.getElementById('field-name').value.trim() || label.toLowerCase().replace(/[^a-z0-9]+/g, '_');
            const type = document.getElementById('field-type').value;
            const required = document.getElementById('field-required').checked ? 1 : 0;
            const placeholder = document.getElementById('field-placeholder').value.trim();
            const optionsText = document.getElementById('field-options').value.trim();
            const options = optionsText ? optionsText.split('\n').map(item => item.trim()).filter(Boolean) : [];

            if (!label) {
                alert('Informe o rótulo do campo.');
                return;
            }

            removeEmptyPlaceholder();

            const card = editingCard ?? document.createElement('div');
            card.className = 'card border border-dashed';
            card.dataset.name = name;
            card.dataset.type = type;
            card.dataset.required = required;
            card.dataset.placeholder = placeholder;
            card.dataset.options = JSON.stringify(options);

            card.innerHTML = `
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">${label}</h6>
                        <small class="text-muted">${type.toUpperCase()} ${required ? '· obrigatório' : ''}</small>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary edit-field"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-field"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;

            if (!editingCard) {
                container.appendChild(card);
            }

            fieldModal.hide();
        });

        document.getElementById('save-fields').addEventListener('click', () => {
            const cards = Array.from(container.querySelectorAll('.card'));

            if (!cards.length) {
                alert('Adicione ao menos um campo antes de salvar.');
                return;
            }

            const fields = cards.map((card, index) => ({
                label: card.querySelector('h6').textContent.trim(),
                name: card.dataset.name,
                type: card.dataset.type,
                required: card.dataset.required,
                placeholder: card.dataset.placeholder,
                options: JSON.parse(card.dataset.options || '[]'),
                order: index
            }));

            fetch("{{ route('leads01.fields.save', $campaign->id) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ fields })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.errors) {
                        alert(Object.values(data.errors).join('\n'));
                        return;
                    }

                    alert(data.message || 'Campos salvos com sucesso.');
                })
                .catch(() => alert('Erro ao salvar campos.'));
        });
    });
</script>
@endpush