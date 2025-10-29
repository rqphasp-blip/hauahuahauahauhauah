@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Lista de Leads') }}</span>
                    <a href="{{ route('leadcapture.export') }}" class="btn btn-sm btn-success">{{ __('Exportar CSV') }}</a>
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

                    <!-- Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total</h5>
                                    <h2>{{ $stats['total'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Novos</h5>
                                    <h2>{{ $stats['novos'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Contatados</h5>
                                    <h2>{{ $stats['contatados'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Convertidos</h5>
                                    <h2>{{ $stats['convertidos'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <form method="GET" action="{{ route('leadcapture.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Buscar</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ $search ?? '' }}" placeholder="Nome, email ou telefone">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Todos</option>
                                        <option value="novo" {{ ($status ?? '') == 'novo' ? 'selected' : '' }}>Novo</option>
                                        <option value="contatado" {{ ($status ?? '') == 'contatado' ? 'selected' : '' }}>Contatado</option>
                                        <option value="convertido" {{ ($status ?? '') == 'convertido' ? 'selected' : '' }}>Convertido</option>
                                        <option value="arquivado" {{ ($status ?? '') == 'arquivado' ? 'selected' : '' }}>Arquivado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_from">Data Inicial</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date_to">Data Final</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Filtrar</button>
                                <a href="{{ route('leadcapture.index') }}" class="btn btn-secondary">Limpar</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Telefone</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leads as $lead)
                                    <tr>
                                        <td>{{ $lead->id }}</td>
                                        <td>{{ $lead->name }}</td>
                                        <td>{{ $lead->email }}</td>
                                        <td>{{ $lead->phone }}</td>
                                        <td>
                                            @if($lead->status == 'novo')
                                                <span class="badge bg-info">Novo</span>
                                            @elseif($lead->status == 'contatado')
                                                <span class="badge bg-warning">Contatado</span>
                                            @elseif($lead->status == 'convertido')
                                                <span class="badge bg-success">Convertido</span>
                                            @elseif($lead->status == 'arquivado')
                                                <span class="badge bg-secondary">Arquivado</span>
                                            @else
                                                <span class="badge bg-info">Novo</span>
                                            @endif
                                        </td>
                                        <td>{{ $lead->created_at }}</td>
                                        <td>
                                            <a href="{{ route('leadcapture.show', $lead->id) }}" class="btn btn-sm btn-info">Ver</a>
                                            <form action="{{ route('leadcapture.destroy', $lead->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este lead?')">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhum lead encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $leads->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
