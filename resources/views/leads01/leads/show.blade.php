@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Lead #{{ $lead->id }} - {{ $campaign->name }}</h1>
            <p class="text-muted mb-0">Recebido em {{ $lead->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div>
            <a href="{{ route('leads01.leads', $campaign->id) }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Dados enviados</h5>
            <dl class="row mb-0">
                @foreach($fields as $field)
                    <dt class="col-sm-3">{{ $field->label }}</dt>
                    <dd class="col-sm-9">{{ $lead->data[$field->field_name] ?? '-' }}</dd>
                @endforeach
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Metadados</h5>
            <p class="mb-1"><strong>IP:</strong> {{ $lead->ip_address ?? '-' }}</p>
            <p class="mb-0"><strong>User Agent:</strong> {{ $lead->user_agent ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection