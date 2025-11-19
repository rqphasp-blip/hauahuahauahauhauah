@extends('linkstack.layout')

@section('content')
    @push('linkstack-head')
        @include('linkstack.modules.meta')
        @include('linkstack.modules.assets')
    @endpush

    @push('linkstack-head-end')
        @foreach($information as $info)
            @include('linkstack.modules.theme')
        @endforeach

 @if($catalogEnabled ?? false)
            <style>
                .ls-tab-card { background: #ffffff; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,.08); padding: 1.5rem; }
                .ls-tab-buttons { gap: .5rem; }
                .ls-tab-buttons .btn { border-radius: 999px; }
                .ls-tab-pane { display: none; }
                .ls-tab-pane.active { display: block; }
            </style>
        @endif

        <style>
            .ls-tab-card { background: #ffffff; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,.08); padding: 1.5rem; }
            .ls-tab-buttons { gap: .5rem; }
            .ls-tab-buttons .btn { border-radius: 999px; }
            .ls-tab-pane { display: none; }
            .ls-tab-pane.active { display: block; }
        </style>
    @endpush

    @push('linkstack-body-start')
        @include('linkstack.modules.admin-bar')
        @include('linkstack.modules.share-button')
        @include('linkstack.modules.report-icon')
    @endpush

    @push('linkstack-content')
        @if($catalogEnabled ?? false)
            <div class="ls-tab-card">
                <div class="d-flex flex-wrap ls-tab-buttons" role="tablist">
                    <button class="btn btn-primary" type="button" data-ls-tab-target="ls-tab-profile" aria-selected="true">Perfil</button>
                    <button class="btn btn-outline-primary" type="button" data-ls-tab-target="ls-tab-catalog" data-catalog-url="{{ $catalogEmbedUrl }}" aria-selected="false">Catálogo</button>
                </div>
                <div class="mt-3">
                    <div id="ls-tab-profile" class="ls-tab-pane active">
                        @foreach($information as $info)
                            @include('linkstack.elements.avatar')
                            @include('linkstack.elements.heading')
                            @include('linkstack.elements.bio')
                        @endforeach
                        @include('linkstack.elements.icons')
                        @include('linkstack.elements.buttons')
                        @yield('content')
                        @include('linkstack.modules.footer')
                    </div>
                    <div id="ls-tab-catalog" class="ls-tab-pane" data-loaded="false">
                        <div class="text-center text-muted py-4" id="ls-catalog-placeholder">Clique na aba Catálogo para carregar os produtos.</div>
                    </div>
                </div>
            </div>
        @else
            @foreach($information as $info)
                @include('linkstack.elements.avatar')
                @include('linkstack.elements.heading')
                @include('linkstack.elements.bio')
            @endforeach
            @include('linkstack.elements.icons')
            @include('linkstack.elements.buttons')
            @yield('content')
            @include('linkstack.modules.footer')
        @endif
    @endpush

    @if($catalogEnabled ?? false)
        @push('linkstack-body-end')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const buttons = document.querySelectorAll('[data-ls-tab-target]');
                    const panes = {
                        profile: document.getElementById('ls-tab-profile'),
                        catalog: document.getElementById('ls-tab-catalog'),
                    };
                    let catalogLoaded = false;

                    function setActive(targetId) {
                        Object.values(panes).forEach(pane => pane.classList.remove('active'));
                        buttons.forEach(btn => btn.classList.remove('btn-primary'));
                        buttons.forEach(btn => btn.classList.add('btn-outline-primary'));

                        const targetPane = document.getElementById(targetId);
                        if (!targetPane) return;

                        targetPane.classList.add('active');
                        const activeButton = Array.from(buttons).find(btn => btn.dataset.lsTabTarget === targetId);
                        if (activeButton) {
                            activeButton.classList.add('btn-primary');
                            activeButton.classList.remove('btn-outline-primary');
                        }
                    }

                    async function loadCatalog(button) {
                        if (catalogLoaded) return;
                        const url = button.dataset.catalogUrl;
                        const placeholder = document.getElementById('ls-catalog-placeholder');
                        if (!url || !placeholder) return;

                        placeholder.textContent = 'Carregando catálogo...';

                        try {
                            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const html = await response.text();
                            placeholder.innerHTML = html;

                            placeholder.querySelectorAll('script').forEach((script) => {
                                const clone = document.createElement('script');
                                if (script.src) {
                                    clone.src = script.src;
                                } else {
                                    clone.textContent = script.textContent;
                                }
                                document.body.appendChild(clone);
                            });

                            catalogLoaded = true;
                            placeholder.dataset.loaded = 'true';
                        } catch (e) {
                            placeholder.textContent = 'Não foi possível carregar o catálogo.';
                        }
                    }

                    buttons.forEach(button => {
                        button.addEventListener('click', async () => {
                            const targetId = button.dataset.lsTabTarget;
                            if (!targetId) return;

                            setActive(targetId);
                            if (targetId === 'ls-tab-catalog') {
                                await loadCatalog(button);
                            }
                        });
                    });
                });
            </script>
        @endpush
    @endif
@endsection