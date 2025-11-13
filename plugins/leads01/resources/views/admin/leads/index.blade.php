@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">{{ $campaign->name }}</h2>
            <p class="text-muted mb-0">{{ __('Leads capturados para esta campanha.') }}</p>
        </div>
        <a href="{{ route('leads01.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Voltar para campanhas') }}
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
                <div>
                    <span class="badge bg-primary me-2">{{ __('Total: :total', ['total' => $entries->total()]) }}</span>
                    <span class="badge bg-success">{{ __('Último lead: :date', ['date' => optional($entries->first())->created_at?->format('d/m/Y H:i') ?: __('N/A')]) }}</span>
                </div>
                <a href="{{ route('leads01.fields', $campaign) }}" class="btn btn-outline-primary">
                    <i class="bi bi-sliders"></i> {{ __('Editar campos') }}
                </a>
            </div>

            @if($entries->isEmpty())
                <div class="text-center text-muted py-5">
                    <h5 class="fw-semibold">{{ __('Nenhum lead por aqui ainda.') }}</h5>
                    <p class="mb-0">{{ __('Compartilhe o formulário da campanha para começar a receber contatos.') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Recebido em') }}</th>
                                <th>{{ __('Pré-visualização') }}</th>
                                <th class="text-end">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($entries as $entry)
                            @php
                                $data = $entry->decoded_data;
                                $preview = collect($data)->map(fn($value, $key) => $key . ': ' . $value)->take(3)->implode(' • ');
                            @endphp
                            <tr>
                                <td>#{{ $entry->id }}</td>
                                <td>{{ optional($entry->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $preview ?: __('Sem dados') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('leads01.entries.show', [$campaign, $entry]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> {{ __('Detalhes') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection