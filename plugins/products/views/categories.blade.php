@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Categorias de produtos</h1>
            <p class="text-muted mb-1">Crie e edite as categorias disponíveis para seus produtos.</p>
            <p class="small mb-0">Link público do catálogo: <a href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicUrl }}</a></p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('products.index') }}">Voltar para produtos</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Nova categoria</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('products.categories.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Salvar categoria</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">Categorias cadastradas</div>
                <div class="card-body">
                    @if($categories->isEmpty())
                        <p class="text-muted mb-0">Cadastre a primeira categoria para organizar seus produtos.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td class="w-25">{{ $category->name }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td class="text-end">
                                            <form class="d-inline-flex flex-wrap gap-2 justify-content-end" method="POST" action="{{ route('products.categories.update', $category->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" placeholder="Nome" required>
                                                <input type="text" name="description" value="{{ $category->description }}" class="form-control form-control-sm" placeholder="Descrição">
                                                <button class="btn btn-outline-primary btn-sm" type="submit">Atualizar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection