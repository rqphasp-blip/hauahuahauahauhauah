@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Leads Capturados – {{ $campaign->name }}</h3>
        <a href="{{ route('leads.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($entries->isEmpty())
        <div class="alert alert-info text-center">
            Nenhum lead foi capturado ainda nesta campanha.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Prévia</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        @php
                            $data = json_decode($entry->data, true);
                            $preview = collect($data)->map(fn($v, $k) => "$k: $v")->take(3)->join(', ');
                        @endphp
                        <tr>
                            <td>#{{ $entry->id }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($entry->created_at)) }}</td>
                            <td>{{ $preview }}</td>
                            <td>
                                <a href="{{ route('leads.show', [$campaign->id, $entry->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Detalhes
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
