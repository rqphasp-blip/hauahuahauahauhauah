@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Detalhes do lead</h2>
            <small class="text-muted">Campanha: {{ $campaign->name }}</small>
        </div>
        <a href="{{ route('leads01.leads', $campaign->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>#{{ $entry->id }}</span>
            <span class="text-muted">{{ \Carbon\Carbon::parse($entry->created_at)->format('d/m/Y H:i') }}</span>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                @foreach ($data as $label => $value)
                    <dt class="col-sm-4">{{ $label }}</dt>
                    <dd class="col-sm-8">{{ $value ?: '-' }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
</div>
@endsection