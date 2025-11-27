@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Campanhas de Leads</h1>

        <a href="{{ route('leads01.campaigns.create') }}" class="btn btn-primary">
            Criar campanha
        </a>
    </div>

    {{-- MENSAGENS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- CONFIGURAÇÃO: ATIVAR / DESATIVAR ABA --}}
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Exibir aba de formulário na página pública</h5>
                <p class="text-muted small mb-0">
                    Quando ativo, a aba "Lead Form" aparecerá na página do seu perfil.
                </p>
            </div>

            <form method="POST" action="{{ route('leads01.settings.toggle') }}">
                @csrf
                <button class="btn btn-outline-primary">
                    {{ $settings->enabled ? 'Desativar' : 'Ativar' }}
                </button>
            </form>
        </div>
    </div>

    {{-- LISTAGEM DAS CAMPANHAS --}}
    @if($campaigns->isEmpty())
        <div class="alert alert-info">
            Nenhuma campanha criada ainda.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th>Leads</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->name }}</td>

                            <td>
                                @if($campaign->status === 'active')
                                    <span class="badge bg-success">Ativa</span>
                                @else
                                    <span class="badge bg-secondary">Inativa</span>
                                @endif
                            </td>

                            <td>{{ $campaign->leads()->count() }}</td>

                            <td class="text-end">
                                <a href="{{ route('leads01.campaigns.edit', $campaign->id) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>

                                <a href="{{ route('leads01.leads.index', $campaign->id) }}" class="btn btn-sm btn-outline-success">
                                    Ver Leads
                                </a>

                                <form action="{{ route('leads01.campaigns.delete', $campaign->id) }}" method="POST"
                                      class="d-inline-block"
                                      onsubmit="return confirm('Deseja excluir esta campanha?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        Excluir
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
