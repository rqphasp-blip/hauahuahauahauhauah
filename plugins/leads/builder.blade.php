@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Construtor de Formulário – {{ $campaign->name }}</h3>
        <a href="{{ route('leads.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-arrows-move"></i> Arraste os campos para reordenar.  
        Use o botão abaixo para adicionar novos campos personalizados.
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            Campos do Formulário
        </div>
        <div class="card-body" id="sortable-fields">
            @foreach ($fields as $field)
                <div class="sortable-item border rounded p-3 mb-2 bg-light d-flex justify-content-between align-items-center" data-id="{{ $field->id }}">
                    <div>
                        <strong>{{ $field->label }}</strong>
                        <small class="text-muted d-block">{{ ucfirst($field->type) }} @if($field->required) <span class="badge bg-warning text-dark">Obrigatório</span>@endif</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-field">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            @endforeach
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-sm btn-outline-primary" id="add-field">
                <i class="bi bi-plus-circle"></i> Adicionar Campo
            </button>
            <button class="btn btn-sm btn-success" id="save-fields">
                <i class="bi bi-save"></i> Salvar Alterações
            </button>
        </div>
    </div>
</div>

{{-- Modal para adicionar campo --}}
<div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="new-field-form">
        <div class="modal-header">
          <h5 class="modal-title" id="addFieldModalLabel">Novo Campo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="field-label" class="form-label">Label do Campo</label>
            <input type="text" id="field-label" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="field-type" class="form-label">Tipo de Campo</label>
            <select id="field-type" class="form-select">
              <option value="text">Texto</option>
              <option value="email">E-mail</option>
              <option value="phone">Telefone</option>
              <option value="select">Select</option>
              <option value="textarea">Textarea</option>
            </select>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="field-required">
            <label class="form-check-label" for="field-required">
              Obrigatório
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Adicionar</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('sortable-fields');
    const sortable = Sortable.create(container, {
        animation: 150,
        handle: '.sortable-item',
        ghostClass: 'bg-warning'
    });

    // Adicionar novo campo
    document.getElementById('add-field').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('addFieldModal'));
        modal.show();
    });

    // Submeter novo campo
    document.getElementById('new-field-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const label = document.getElementById('field-label').value.trim();
        const type = document.getElementById('field-type').value;
        const required = document.getElementById('field-required').checked;

        if (!label) return alert('O label é obrigatório.');

        const newField = document.createElement('div');
        newField.className = 'sortable-item border rounded p-3 mb-2 bg-light d-flex justify-content-between align-items-center';
        newField.dataset.label = label;
        newField.dataset.type = type;
        newField.dataset.required = required ? 1 : 0;
        newField.innerHTML = `
            <div>
                <strong>${label}</strong>
                <small class="text-muted d-block">${type.charAt(0).toUpperCase() + type.slice(1)} ${required ? '<span class="badge bg-warning text-dark">Obrigatório</span>' : ''}</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-field"><i class="bi bi-trash"></i></button>
        `;

        container.appendChild(newField);
        bootstrap.Modal.getInstance(document.getElementById('addFieldModal')).hide();
        this.reset();
    });

    // Remover campo
    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-field')) {
            e.target.closest('.sortable-item').remove();
        }
    });

    // Salvar ordem e campos
    document.getElementById('save-fields').addEventListener('click', function() {
        const fields = Array.from(container.children).map((item, index) => ({
            label: item.dataset.label || item.querySelector('strong')?.innerText,
            type: item.dataset.type || 'text',
            required: parseInt(item.dataset.required) || 0,
            order: index
        }));

        fetch("{{ route('leads.builder.save', $campaign->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ fields })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Campos salvos com sucesso!');
                location.reload();
            } else {
                alert('Erro ao salvar campos.');
            }
        })
        .catch(() => alert('Erro de comunicação com o servidor.'));
    });
});
</script>
@endsection
