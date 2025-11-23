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
                                        @php
                                            $extension = strtolower(pathinfo($story->image_path ?? '', PATHINFO_EXTENSION));
                                            $isVideo = in_array($extension, ['mp4', 'mov', 'm4v', 'webm']);
                                        @endphp

                                        @if($isVideo)
                                            <video class="card-img-top" controls muted preload="metadata" style="max-height: 260px; object-fit: cover;">
                                                <source src="{{ asset($story->image_path) }}" type="video/{{ $extension === 'mov' ? 'quicktime' : $extension }}">
                                                Seu navegador não suporta a reprodução de vídeos.
                                            </video>
                                        @else
                                            <img src="{{ asset($story->image_path) }}" class="card-img-top" alt="Story Image">
                                        @endif
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
