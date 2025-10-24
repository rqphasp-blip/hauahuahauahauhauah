<!-- Arquivo: views/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Gerenciar Avaliações do Google</span>
                        <div>
                            <a href="{{ route('googlereviews.config') }}" class="btn btn-sm btn-info mr-2">Configurações da API</a>
                            <a href="{{ route('googlereviews.create') }}" class="btn btn-sm btn-primary">Adicionar Estabelecimento</a>
                        </div>
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

                    @if(count($establishments) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Place ID</th>
                                        <th>Data de Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($establishments as $establishment)
                                        <tr>
                                            <td>{{ $establishment->name }}</td>
                                            <td>{{ $establishment->place_id }}</td>
                                            <td>{{ date('d/m/Y H:i', strtotime($establishment->created_at)) }}</td>
                                            <td>
                                                <a href="{{ route('googlereviews.show', $establishment->id) }}" class="btn btn-sm btn-info">Detalhes</a>
                                                <button class="btn btn-sm btn-danger delete-btn" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteModal" 
                                                    data-id="{{ $establishment->id }}"
                                                    data-name="{{ $establishment->name }}">
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p>Nenhum estabelecimento cadastrado. Siga os passos abaixo para começar:</p>
                            <ol>
                                <li>Primeiro, <a href="{{ route('googlereviews.config') }}">configure sua chave da API do Google Places</a></li>
                                <li>Em seguida, <a href="{{ route('googlereviews.create') }}">adicione um estabelecimento</a> usando o Place ID do Google</li>
                            </ol>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
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
                Tem certeza que deseja excluir o estabelecimento <span id="establishment-name"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="delete-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.delete-btn').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#establishment-name').text(name);
            $('#delete-form').attr('action', '/googlereviews/' + id);
        });
    });
</script>
@endsection
