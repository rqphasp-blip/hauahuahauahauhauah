<!-- Arquivo: views/config.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Configuração da API do Google Places</span>
                        <a href="{{ route('googlereviews.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
                    </div>
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

                    <form method="POST" action="{{ route('googlereviews.saveconfig') }}">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label for="api_key">Chave da API do Google Places</label>
                            <input type="text" class="form-control @error('api_key') is-invalid @enderror" 
                                id="api_key" name="api_key" 
                                value="{{ old('api_key', $config->api_key ?? '') }}" required>
                            
                            @error('api_key')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            
                            <small class="form-text text-muted mt-2">
                                Para obter uma chave da API do Google Places, acesse o 
                                <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Console do Google Cloud</a>.
                                Certifique-se de habilitar a API Places para esta chave.
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <h5>Como obter uma chave da API do Google Places:</h5>
                            <ol>
                                <li>Acesse o <a href="https://console.cloud.google.com/" target="_blank">Console do Google Cloud</a></li>
                                <li>Crie um novo projeto ou selecione um existente</li>
                                <li>No menu lateral, navegue até "APIs e Serviços" > "Credenciais"</li>
                                <li>Clique em "Criar Credenciais" > "Chave de API"</li>
                                <li>Restrinja a chave para uso apenas com a API Places</li>
                                <li>Copie a chave gerada e cole no campo acima</li>
                            </ol>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
