@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Enviar Fotos para a Galeria</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('gallery.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="photos" class="form-label">Selecione as fotos</label>
                            <input type="file" name="photos[]" id="photos" class="form-control" multiple required accept="image/*">
                            <small class="form-text text-muted">Você pode selecionar várias imagens (JPG, PNG, GIF, WEBP) de até 5MB cada.</small>
                        </div>

                        <div class="mb-3">
                            <label for="caption" class="form-label">Legenda (opcional)</label>
                            <input type="text" name="caption" id="caption" class="form-control" maxlength="255" placeholder="Adicione uma legenda para as fotos">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('gallery.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Enviar Fotos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection