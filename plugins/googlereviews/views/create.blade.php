<!-- Arquivo: views/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Adicionar Novo Estabelecimento</span>
                        <a href="{{ route('googlereviews.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('googlereviews.store') }}">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label for="place_id">Place ID do Google</label>
                            <input type="text" class="form-control @error('place_id') is-invalid @enderror" 
                                id="place_id" name="place_id" value="{{ old('place_id') }}" required>
                            
                            @error('place_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            
                            <small class="form-text text-muted mt-2">
                                O Place ID é um identificador único do Google para cada estabelecimento.
                                <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">
                                    Saiba como encontrar o Place ID
                                </a>
                            </small>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="name">Nome do Estabelecimento</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" required>
                            
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info">
                            <h5>Como encontrar o Place ID de um estabelecimento:</h5>
                            <ol>
                                <li>Acesse a <a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder" target="_blank">ferramenta de busca de Place ID</a></li>
                                <li>Digite o nome do estabelecimento no campo de busca</li>
                                <li>Selecione o estabelecimento correto nos resultados</li>
                                <li>Copie o Place ID exibido e cole no campo acima</li>
                            </ol>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Adicionar Estabelecimento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
