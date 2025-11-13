@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h2 class="mb-1">{{ $campaign->exists ? __('Editar campanha') : __('Nova campanha') }}</h2>
        <p class="text-muted mb-0">{{ __('Defina os detalhes básicos e a mensagem de agradecimento exibida após o envio do formulário.') }}</p>
    </div>

    <form action="{{ $campaign->exists ? route('leads01.update', $campaign) : route('leads01.store') }}" method="POST" class="card shadow-sm">
        <div class="card-body p-4">
            @csrf
            @if($campaign->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">{{ __('Nome da campanha') }}</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $campaign->name) }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">{{ __('Descrição') }}</label>
                <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="{{ __('Ex: Campanha do Instagram') }}">{{ old('description', $campaign->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label for="thank_you_message" class="form-label fw-semibold">{{ __('Mensagem de agradecimento') }}</label>
                <textarea id="thank_you_message" name="thank_you_message" rows="4" class="form-control @error('thank_you_message') is-invalid @enderror" placeholder="{{ __('Ex: Obrigado por se inscrever! Em breve entraremos em contato.') }}">{{ old('thank_you_message', $campaign->thank_you_message) }}</textarea>
                <small class="text-muted">{{ __('Esta mensagem será exibida no modal após o envio do formulário.') }}</small>
                @error('thank_you_message')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
            <a href="{{ route('leads01.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Voltar') }}
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> {{ $campaign->exists ? __('Salvar alterações') : __('Criar campanha') }}
            </button>
        </div>
    </form>
</div>
@endsection