@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Lead #{{ $entry->id }} – {{ $campaign->name }}</h3>
        <a href="{{ route('leads.entries', $campaign->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Detalhes do Lead
        </div>
        <div class="card-body">
            @if (!empty($data))
                <table class="table table-borderless align-middle">
                    <tbody>
                        @foreach ($data as $key => $value)
                            <tr>
                                <th style="width: 200px; text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</th>
                                <td>{{ $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning text-center">
                    Nenhum dado disponível para este lead.
                </div>
            @endif
        </div>
        <div class="card-footer text-muted text-end">
            Capturado em {{ date('d/m/Y H:i', strtotime($entry->created_at)) }}
        </div>
    </div>
</div>
@endsection
