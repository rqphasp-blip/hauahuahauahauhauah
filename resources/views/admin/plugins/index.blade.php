@extends('layouts.sidebar')


@section('content')
<div class="container-fluid">


    @if (session("success"))
        <div class="alert alert-success">
            {{ session("success") }}
        </div>
    @endif
    @if (session("error"))
        <div class="alert alert-danger">
            {{ session("error") }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plug me-1"></i>
            Plugins Instalados
            <a href="{{ route("admin.plugins.create") }}" class="btn btn-primary btn-sm float-end">Instalar Novo Plugin</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Identificador</th>
                        <th>Versão</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plugins as $plugin)
                        <tr>
                            <td>{{ $plugin->name }}</td>
                            <td>{{ $plugin->identifier }}</td>
                            <td>{{ $plugin->version }}</td>
                            <td>
                                @if ($plugin->is_active)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td>
                                @if ($plugin->is_active)
                                    <form action="{{ route("admin.plugins.deactivate", $plugin->identifier) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm">Desativar</button>
                                    </form>
                                @else
                                    <form action="{{ route("admin.plugins.activate", $plugin->identifier) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Ativar</button>
                                    </form>
                                @endif
                                <form action="{{ route("admin.plugins.delete", $plugin->identifier) }}" method="POST" style="display:inline-block;" onsubmit="return confirm("Tem certeza que deseja excluir este plugin? Esta ação não pode ser desfeita.");">
                                    @csrf
                                    @method("DELETE")
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum plugin instalado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection