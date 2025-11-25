@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Leads - {{ $campaign->name }}</h1>
            <p class="text-muted mb-0">Visualize os envios recebidos para esta campanha.</p>
        </div>
        <a href="{{ route('leads01.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($leads->count() === 0)
        <div class="alert alert-info">Nenhum lead recebido ainda.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                        <tr>
                            <td>#{{ $lead->id }}</td>
                            <td>{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('leads01.leads.show', [$campaign->id, $lead->id]) }}" class="btn btn-sm btn-outline-primary">Ver detalhes</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $leads->links() }}
    @endif
</div>
@endsection