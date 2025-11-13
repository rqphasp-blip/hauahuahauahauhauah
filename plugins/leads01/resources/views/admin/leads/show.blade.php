@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">{{ __('Lead #') . $entry->id }}</h2>
            <p class="text-muted mb-0">{{ $campaign->name }}</p>
        </div>
        <a href="{{ route('leads01.entries', $campaign) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Voltar para leads') }}
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3 text-muted">{{ __('Recebido em') }}</dt>
                <dd class="col-sm-9">{{ optional($entry->created_at)->format('d/m/Y H:i') }}</dd>

                @foreach($fields as $field)
                    <dt class="col-sm-3 text-muted">{{ $field->label }}</dt>
                    <dd class="col-sm-9">{{ $data[$field->label] ?? '—' }}</dd>
                @endforeach
            </dl>
        </div>
    </div>
</div>
@endsection