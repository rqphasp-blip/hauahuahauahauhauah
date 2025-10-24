<!-- Arquivo: views/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Detalhes do Estabelecimento</span>
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

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informações do Estabelecimento</h5>
                            <table class="table">
                                <tr>
                                    <th>Nome:</th>
                                    <td>{{ $establishment->name }}</td>
                                </tr>
                                <tr>
                                    <th>Place ID:</th>
                                    <td>{{ $establishment->place_id }}</td>
                                </tr>
                                <tr>
                                    <th>Data de Cadastro:</th>
                                    <td>{{ date('d/m/Y H:i', strtotime($establishment->created_at)) }}</td>
                                </tr>
                            </table>
                            
                            <h5 class="mt-4">Ações</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editModal">
                                    Editar Estabelecimento
                                </button>
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                                    Excluir Estabelecimento
                                </button>
                            </div>
                            
                            <h5 class="mt-4">Código do Widget</h5>
                            <div class="alert alert-secondary">
                                <p>Copie e cole este código em qualquer página do seu site para exibir o widget de avaliações:</p>
                                <pre><code>&lt;iframe src="{{ route('googlereviews.widget', $establishment->place_id) }}" 
    width="200" height="180" frameborder="0" scrolling="no"&gt;&lt;/iframe&gt;</code></pre>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Dados da API do Google</h5>
                            
                            @if(isset($placeDetails['error']))
                                <div class="alert alert-danger">
                                    <p><strong>Erro ao obter dados da API:</strong> {{ $placeDetails['error'] }}</p>
                                    <p>Verifique se a <a href="{{ route('googlereviews.config') }}">chave da API</a> está configurada corretamente.</p>
                                </div>
                            @else
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $placeDetails['name'] ?? $establishment->name }}</h5>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="mr-2">
                                                <strong>{{ number_format($placeDetails['rating'] ?? 0, 1) }}</strong>
                                            </div>
                                            <div class="stars">
                                                @php
                                                    $rating = $placeDetails['rating'] ?? 0;
                                                @endphp
                                                
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $rating)
                                                        <i class="fas fa-star text-warning"></i>
                                                    @elseif($i - 0.5 <= $rating)
                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-warning"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <div class="ml-2">
                                                <span>{{ $placeDetails['user_ratings_total'] ?? 0 }} avaliações</span>
                                            </div>
                                        </div>
                                        
                                        @if(isset($placeDetails['reviews']) && count($placeDetails['reviews']) > 0)
                                            <h6 class="mt-4">Avaliações Recentes</h6>
                                            @foreach($placeDetails['reviews'] as $review)
                                                <div class="review-item mb-3 p-3 border rounded">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>{{ $review['author_name'] }}</strong>
                                                        </div>
                                                        <div class="stars">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= $review['rating'])
                                                                    <i class="fas fa-star text-warning"></i>
                                                                @else
                                                                    <i class="far fa-star text-warning"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="review-text mt-2">
                                                        {{ $review['text'] }}
                                                    </div>
                                                    <div class="review-time text-muted mt-1">
                                                        <small>{{ date('d/m/Y', $review['time']) }}</small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-info mt-3">
                                                Nenhuma avaliação disponível para este estabelecimento.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Prévia do Widget</h5>
                                        <div class="d-flex justify-content-center">
                                            <iframe src="{{ route('googlereviews.widget', $establishment->place_id) }}" 
                                                width="200" height="180" frameborder="0" scrolling="no"></iframe>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Estabelecimento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('googlereviews.update', $establishment->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Nome do Estabelecimento</label>
                        <input type="text" class="form-control" id="name" name="name" 
                            value="{{ $establishment->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="place_id_display">Place ID (não editável)</label>
                        <input type="text" class="form-control" id="place_id_display" 
                            value="{{ $establishment->place_id }}" disabled>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir o estabelecimento <strong>{{ $establishment->name }}</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('googlereviews.destroy', $establishment->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
