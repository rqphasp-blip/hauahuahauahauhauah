@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-3">Formulários públicos de {{ $user->name }}</h1>

    @if($campaigns->isEmpty())
        <div class="alert alert-info">Nenhuma campanha pública encontrada.</div>
    @else
        <div class="row g-3">
            @foreach($campaigns as $campaign)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $campaign->name }}</h5>
                            <p class="card-text text-muted">{{ $campaign->description }}</p>
                            <div class="mt-auto">
                                <a href="{{ route('leads01.form', $campaign->slug) }}" class="btn btn-primary">Abrir formulário</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection