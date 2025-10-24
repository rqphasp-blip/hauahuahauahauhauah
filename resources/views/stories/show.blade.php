@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Visualizar Story</span>
                    <a href="{{ route('stories.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
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

                    <div class="text-center mb-3">
                        <img src="{{ asset($story->image_path) }}" class="img-fluid rounded" alt="Story Image" style="max-height: 500px;">
                    </div>

                    @if($story->caption)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Legenda</h5>
                                <p class="card-text">{{ $story->caption }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0"><strong>Publicado por:</strong> {{ $user->name }}</p>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($story->created_at)->diffForHumans() }}</small>
                        </div>

                        @if(Auth::id() == $story->user_id)
                            <form method="POST" action="{{ route('stories.destroy', $story->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este story?')">Excluir Story</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
