@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1">{{ __('Campanhas de Leads') }}</h2>
            <p class="text-muted mb-0">{{ __('Gerencie os formulários de captura e acompanhe os leads recebidos.') }}</p>
        </div>
        <a href="{{ route('leads01.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> {{ __('Nova campanha') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($campaigns->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h5 class="fw-semibold">{{ __('Você ainda não possui campanhas criadas.') }}</h5>
                <p class="text-muted">{{ __('Crie uma campanha e personalize até 10 campos para começar a capturar leads.') }}</p>
                <a href="{{ route('leads01.create') }}" class="btn btn-outline-primary">
                    {{ __('Criar minha primeira campanha') }}
                </a>
            </div>
        </div>
    @else
        <div class="table-responsive shadow-sm">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>{{ __('Campanha') }}</th>
                    <th>{{ __('Descrição') }}</th>
                    <th>{{ __('Criada em') }}</th>
                    <th class="text-end">{{ __('Ações') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($campaigns as $campaign)
                    <tr>
                        <td class="fw-semibold">{{ $campaign->name }}</td>
                        <td>{{ $campaign->description ?: '—' }}</td>
                        <td>{{ optional($campaign->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('leads01.fields', $campaign) }}">
                                    <i class="bi bi-sliders"></i> {{ __('Campos') }}
                                </a>
                                <a class="btn btn-sm btn-outline-success" href="{{ route('leads01.entries', $campaign) }}">
                                    <i class="bi bi-people"></i> {{ __('Leads') }}
                                </a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('leads01.edit', $campaign) }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('leads01.destroy', $campaign) }}" method="POST" onsubmit="return confirm('{{ __('Deseja mesmo excluir esta campanha?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>
@endsection