@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Criar Novo Story</div>

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

                    <form method="POST" action="{{ route('stories.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="story_image">Selecione uma imagem para o story</label>
                            <input type="file" class="form-control @error('story_image') is-invalid @enderror" id="story_image" name="story_image">
                            
                            @error('story_image')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="caption">Legenda (opcional)</label>
                            <input type="text" class="form-control @error('caption') is-invalid @enderror" id="caption" name="caption" maxlength="255">
                            
                            @error('caption')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            Seu story ficará visível por 24 horas após a publicação.
                        </div>

                        <button type="submit" class="btn btn-primary">Publicar Story</button>
                        <a href="{{ route('stories.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
