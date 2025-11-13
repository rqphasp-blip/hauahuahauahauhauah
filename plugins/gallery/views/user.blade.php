@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    Galeria de {{ $user->name }}
                </div>

                <div class="card-body">
                    @if($photos->count() > 0)
                        <div class="row">
                            @foreach($photos as $photo)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <img src="{{ asset($photo->image_path) }}" class="card-img-top" alt="Foto da galeria">
                                        <div class="card-body">
                                            <p class="card-text">{{ $photo->caption ?? 'Sem legenda' }}</p>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($photo->created_at)->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            Este usuário ainda não publicou fotos na galeria.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection