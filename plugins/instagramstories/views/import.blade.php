@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Importar Story do Instagram</div>

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

                    <form method="POST" action="{{ route('stories.instagram.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="instagram_url">Link do story no Instagram</label>
                            <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/stories/..." required>

                            @error('instagram_url')
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
                            O story importado será salvo no mesmo gerenciador e ficará visível por 24 horas, assim como os stories enviados manualmente.
                        </div>

                        <button type="submit" class="btn btn-primary">Importar story</button>
                        <a href="{{ route('stories.index') }}" class="btn btn-secondary">Voltar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection