@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Minha Galeria de Fotos</span>
                    <a href="{{ route('gallery.create') }}" class="btn btn-primary btn-sm">Enviar novas fotos</a>
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

                    @if($photos->count() > 0)
                        <div class="row">
                            @foreach($photos as $photo)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <img src="{{ asset($photo->image_path) }}" class="card-img-top" alt="Foto da galeria">
                                        <div class="card-body d-flex flex-column">
                                            <p class="card-text">{{ $photo->caption ?? 'Sem legenda' }}</p>
                                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($photo->created_at)->diffForHumans() }}</small>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('gallery.show', $photo->id) }}" class="btn btn-sm btn-info">Ver</a>
                                                    <form action="{{ route('gallery.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Deseja remover esta foto?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            Você ainda não enviou nenhuma foto para a galeria. Clique no botão "Enviar novas fotos" para começar.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection