@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Meus Stories</span>
                    <a href="{{ route('stories.create') }}" class="btn btn-primary btn-sm">Criar Novo Story</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(count($activeStories) > 0)
                        <div class="row">
                            @foreach($activeStories as $story)
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <img src="{{ asset($story->image_path) }}" class="card-img-top" alt="Story Image">
                                        <div class="card-body">
                                            <p class="card-text">
                                                {{ $story->caption ?? 'Sem legenda' }}
                                            </p>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($story->created_at)->diffForHumans() }}
                                                </small>
                                                <a href="{{ route('stories.show', $story->id) }}" class="btn btn-sm btn-info">Ver</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            Você não tem stories ativos no momento. Seus stories ficam visíveis por 24 horas.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
