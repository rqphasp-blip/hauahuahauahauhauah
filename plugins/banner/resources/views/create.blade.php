@extends("layouts.app")

@section("content")
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __("Criar Banner de Perfil") }}</div>

                <div class="card-body">
                    {{-- O formulário de upload está na página de índice (gerenciamento) --}}
                    <p>{{ __("Para enviar um novo banner, por favor, use a página principal de gerenciamento de banner.") }}</p>
                    <a href="{{ route("banner.index") }}" class="btn btn-primary">{{ __("Voltar para Gerenciamento") }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
