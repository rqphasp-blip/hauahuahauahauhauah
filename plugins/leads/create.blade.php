@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>{{ isset($campaign) ? 'Editar Campanha' : 'Nova Campanha de Leads' }}</h3>

    <form action="{{ isset($campaign) ? route('leads.update', $campaign->id) : route('leads.store') }}" method="POST" class="mt-4">
        @csrf
        @if (isset($campaign))
            @method('POST')
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Campanha</label>
            <input type="text" name="name" id="name" class="form-control" 
                   value="{{ old('name', $campaign->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Descrição</label>
            <textarea name="description" id="description" rows="3" class="form-control"
                      placeholder="Ex: Campanha de captura pelo Instagram">{{ old('description', $campaign->description ?? '') }}</textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('leads.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> {{ isset($campaign) ? 'Salvar Alterações' : 'Criar Campanha' }}
            </button>
        </div>
    </form>
</div>
@endsection
