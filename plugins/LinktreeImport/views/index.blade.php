@extends('layouts.app')

@section('content')
@once
    <script src="https://kit.fontawesome.com/89540f80d1.js" crossorigin="anonymous"></script>
@endonce
<div class="container py-4">
    @php
        $hasImport = !empty($latestImport);
        $clearImportAction = Route::has('profile-import.clear') ? route('profile-import.clear') : url('/importar-perfil/limpar');
    @endphp
    <div class="d-flex justify-content-between align-items-start mb-3 position-relative">
        <div>
            <h1 class="h4 mb-1">Importar perfil</h1>
            <p class="text-muted mb-0">Busque links, textos, fotos e avatar de um perfil público e adicione tudo de uma vez ao seu perfil.</p>
        </div>
        <div class="ms-3" style="position: sticky; top: 1rem;">
            <form method="POST" action="{{ $clearImportAction }}">
                @csrf
                <button class="btn btn-outline-danger" type="submit" {{ $hasImport ? '' : 'disabled' }}>Limpar importação</button>
            </form>
        </div>
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


	
	
	@php
        $hasImport = !empty($latestImport);
        $clearImportAction = Route::has('profile-import.clear') ? route('profile-import.clear') : url('/importar-perfil');
    @endphp

    <div class="card mb-4">
        <div class="card-header">Importação instantânea</div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile-import.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">URL do perfil</label>
                    <input type="url" name="profile_url" class="form-control" placeholder="https://exemplo.com/seu-perfil" required {{ $hasImport ? 'disabled' : '' }}>
                    <small class="text-muted">Utilize apenas perfis públicos. Em segundos o conteúdo é anexado ao seu perfil.</small>
                </div>
                <button class="btn btn-primary" type="submit" {{ $hasImport ? 'disabled' : '' }}>Importar agora</button>
                @if($hasImport)
                    <p class="text-warning small mb-0 mt-2">Limpe a importação anterior para iniciar uma nova.</p>
                @endif
            </form>
         
        </div>
    </div>
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

    @if($latestImport)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">Última importação</div>
                    <div class="small text-muted">{{ \Carbon\Carbon::parse($latestImport['imported_at'] ?? now())->diffForHumans() }} — origem: {{ $latestImport['source_url'] ?? '' }}</div>
                </div>
                @if(!empty($latestImport['avatar_path']))
                    <img src="{{ asset($latestImport['avatar_path']) }}" alt="Avatar importado" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover;">
                @endif
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Nome:</strong> {{ $latestImport['display_name'] ?? 'Não informado' }}</p>
                <p class="text-muted">{{ $latestImport['bio'] ?? 'Sem bio importada.' }}</p>

                @if($links->isEmpty())
                    <p class="text-muted mb-0">Nenhum link encontrado na importação.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th class="border-0">Link</th>
                                    <th class="border-0">URL</th>
                                    <th class="border-0">Mídia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($links as $link)
                                    <tr>
                                        <td class="fw-semibold">{{ $link['title'] }}</td>
                                        <td><a href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ $link['url'] }}</a></td>
                                        <td>
                                            @if(!empty($link['thumbnail_path']))
                                                <img src="{{ asset($link['thumbnail_path']) }}" alt="Mídia do link" class="img-thumbnail" style="max-height: 64px;">
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection