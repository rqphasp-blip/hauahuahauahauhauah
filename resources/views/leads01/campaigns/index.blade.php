@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Campanhas de Leads</h2>
        <a href="{{ route('leads01.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova campanha
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
            Nenhuma campanha criada ainda. Clique em "Nova campanha" para começar.
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th class="d-none d-md-table-cell">Descrição</th>
                        <th>Slug</th>
                        <th class="d-none d-lg-table-cell">Atualizado em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>
                            <td class="d-none d-md-table-cell">{{ $campaign->description ?: '-' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $campaign->slug }}</span>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                {{ \Carbon\Carbon::parse($campaign->updated_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('leads01.fields', $campaign->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-ui-checks"></i> Campos
                                </a>
                                <a href="{{ route('leads01.leads', $campaign->id) }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-people"></i> Leads
                                </a>
                                <a href="{{ route('leads01.edit', $campaign->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('leads01.destroy', $campaign->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir campanha e todos os dados relacionados?')">
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