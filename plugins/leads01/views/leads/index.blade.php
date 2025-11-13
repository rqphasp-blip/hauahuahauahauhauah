@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Leads coletados</h2>
            <small class="text-muted">{{ $campaign->name }}</small>
        </div>
        <a href="{{ route('leads01.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Campanhas
        </a>
    </div>

    @if ($entries->isEmpty())
        <div class="alert alert-info text-center">
            Ainda não há leads cadastrados nesta campanha.
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th class="d-none d-md-table-cell">Resumo</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        @php $data = json_decode($entry->data, true) ?? []; @endphp
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td class="d-none d-md-table-cell">
                                {{ collect($data)->take(2)->map(fn($value, $label) => "$label: $value")->implode(' • ') ?: 'Sem dados' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($entry->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('leads01.leads.show', [$campaign->id, $entry->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
