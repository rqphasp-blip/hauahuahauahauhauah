@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Detalhes do Lead') }}</span>
                    <a href="{{ route('leadcapture.index') }}" class="btn btn-sm btn-secondary">{{ __('Voltar para Lista') }}</a>
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Informações do Lead</h5>
                            <table class="table">
                                <tr>
                                    <th width="30%">ID:</th>
                                    <td>{{ $lead->id }}</td>
                                </tr>
                                <tr>
                                    <th>Nome:</th>
                                    <td>{{ $lead->name }}</td>
                                </tr>
                                <tr>
                                    <th>E-mail:</th>
                                    <td>{{ $lead->email }}</td>
                                </tr>
                                <tr>
                                    <th>Telefone:</th>
                                    <td>{{ $lead->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
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
                                </tr>
                                <tr>
                                    <th>Data de Criação:</th>
                                    <td>{{ $lead->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>Última Atualização:</th>
                                    <td>{{ $lead->updated_at }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Atualizar Status</h5>
                            <form action="{{ route('leadcapture.update.status', $lead->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="novo" {{ $lead->status == 'novo' ? 'selected' : '' }}>Novo</option>
                                        <option value="contatado" {{ $lead->status == 'contatado' ? 'selected' : '' }}>Contatado</option>
                                        <option value="convertido" {{ $lead->status == 'convertido' ? 'selected' : '' }}>Convertido</option>
                                        <option value="arquivado" {{ $lead->status == 'arquivado' ? 'selected' : '' }}>Arquivado</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="notes">Notas</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4">{{ $lead->notes }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Atualizar Status</button>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h5>Mensagem</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $lead->message }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
