@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">{{ isset($campaign) ? 'Editar campanha' : 'Nova campanha' }}</h2>
        <a href="{{ route('leads01.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <form method="POST" action="{{ isset($campaign) ? route('leads01.update', $campaign->id) : route('leads01.store') }}" class="card shadow-sm">
        <div class="card-body">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="name">Nome da campanha</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $campaign->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="description">Descrição</label>
                <textarea id="description" name="description" rows="3" class="form-control" placeholder="Resumo interno da campanha">{{ old('description', $campaign->description ?? '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label" for="thank_you_message">Mensagem de agradecimento</label>
                <textarea id="thank_you_message" name="thank_you_message" rows="3" class="form-control" placeholder="Mensagem exibida após a submissão">{{ old('thank_you_message', $campaign->thank_you_message ?? 'Obrigado! Em breve entraremos em contato.') }}</textarea>
                <small class="text-muted">Essa mensagem aparecerá dentro do modal após o envio do formulário.</small>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> {{ isset($campaign) ? 'Salvar mudanças' : 'Criar campanha' }}
            </button>
        </div>
    </form>
</div>
@endsection