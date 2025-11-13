@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Enviar novas fotos</div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('gallery.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="photos" class="form-label">Selecione as fotos</label>
                            <input type="file" name="photos[]" id="photos" class="form-control" multiple required accept="image/*">
                            <small class="form-text text-muted">Formatos permitidos: jpeg, png, jpg, gif, webp. Tamanho máximo: 5MB por foto.</small>
                        </div>

                        <div class="mb-3">
                            <label for="caption" class="form-label">Legenda (opcional)</label>
                            <input type="text" name="caption" id="caption" class="form-control" maxlength="255" placeholder="Adicione uma legenda para suas fotos">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('gallery.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Enviar fotos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection