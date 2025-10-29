@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Minhas Campanhas de Leads</h3>
        <a href="{{ route('leads.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova Campanha
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($campaigns->isEmpty())
        <div class="alert alert-info text-center">
            Você ainda não criou nenhuma campanha de captura de leads.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Criada em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>
                            <td>{{ $campaign->description ?? '-' }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($campaign->created_at)) }}</td>
                            <td>
                                <a href="{{ route('leads.builder', $campaign->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-ui-checks"></i> Builder
                                </a>
                                <a href="{{ route('leads.entries', $campaign->id) }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-people"></i> Leads
                                </a>
                                <a href="{{ route('leads.edit', $campaign->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('leads.destroy', $campaign->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir esta campanha?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
