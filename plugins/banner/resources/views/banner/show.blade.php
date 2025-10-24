@extends("layouts.app")

@section("content")
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __("Visualizar Banner de Perfil") }}</div>

                <div class="card-body">
                    @if ($user->profile_banner_path)
                        <div class="mt-3">
                            <img src="{{ url($user->profile_banner_path) }}" 
                                 alt="{{ __("Banner do Perfil") }}" 
                                 style="max-height: 250px; width: auto; border-radius: 8px; max-width: 100%;">
                        </div>
                    @else
                        <p>{{ __("Você ainda não possui um banner de perfil.") }}</p>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route("banner.index") }}" class="btn btn-primary">{{ __("Voltar para Gerenciamento") }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
