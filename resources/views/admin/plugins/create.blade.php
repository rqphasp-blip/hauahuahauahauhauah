@extends('layouts.sidebar')


@section('content')
<div class="container-fluid">


    @if (session("error"))
        <div class="alert alert-danger">
            {{ session("error") }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-upload me-1"></i>
            Upload de Plugin (.zip)
        </div>
        <div class="card-body">
            <form action="{{ route("admin.plugins.store") }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="plugin_zip" class="form-label">Arquivo do Plugin (ZIP)</label>
                    <input class="form-control" type="file" id="plugin_zip" name="plugin_zip" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="overwrite" name="overwrite" value="1">
                    <label class="form-check-label" for="overwrite">Sobrescrever se o plugin já existir</label>
                </div>
                <button type="submit" class="btn btn-primary">Instalar Plugin</button>
                <a href="{{ route("admin.plugins.index") }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

@endsection