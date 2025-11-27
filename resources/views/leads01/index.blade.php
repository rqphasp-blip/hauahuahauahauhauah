@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Minhas Campanhas de Leads</h1>
            <p class="text-muted mb-0">Gerencie formulários de captação com até {{ \App\Providers\plugins\leads01\Leads01Controller::FIELD_LIMIT }} campos por campanha.</p>
        </div>
        <a href="{{ route('leads01.create') }}" class="btn btn-primary">Nova campanha</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($campaigns->count() === 0)
        <div class="alert alert-info">Nenhuma campanha cadastrada ainda. Crie a primeira para começar a captar leads.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Leads</th>
						 <th class="text-center">Visível</th> 
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>
                            <td><code>{{ $campaign->slug }}</code></td>
                            <td>
                                @if($campaign->status === 'active')
                                    <span class="badge bg-success">Ativa</span>
                                @else
                                    <span class="badge bg-secondary">Inativa</span>
                                @endif
                            </td>
                            <td>{{ $campaign->entries_count }}</td>
							
							 <td class="text-center">
                    <form action="{{ route('leads01.campaign.toggle-visible', $campaign->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm {{ (int) $campaign->visivel === 1 ? 'btn-success' : 'btn-outline-secondary' }}">
                            {{ (int) $campaign->visivel === 1 ? 'Visível' : 'Tornar visível' }}
                        </button>
                    </form>
                </td>
							
							
                            <td class="text-end">
                                <a href="{{ route('leads01.leads', $campaign->id) }}" class="btn btn-sm btn-outline-info">Leads</a>
                                <a href="{{ route('leads01.edit', $campaign->id) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('leads01.destroy', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir campanha e todos os leads?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $campaigns->links() }}
    @endif
</div>
@endsection