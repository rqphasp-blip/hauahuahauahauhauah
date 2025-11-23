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
                .ls-tab-card {
                    background: transparent;
                    border-radius: 16px;
                    box-shadow: 0 15px 40px rgba(0,0,0,.08);
                    padding: 1rem;
                    transition: background-color 0.2s ease;
                }

                .ls-tab-card--catalog {
                    background: #ffffff;
                    color: #1a1a1a;
                }

                .ls-tab-card--catalog .ls-tab-buttons {
                    border-bottom-color: rgba(0, 0, 0, 0.1);
                }

                .ls-tab-buttons {
                    display: flex;
                    gap: 1.25rem;
                    overflow-x: auto;
                    padding-bottom: 0.35rem;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.25);
                    margin-bottom: 1rem;
                    scrollbar-width: none;
                }

                .ls-tab-buttons::-webkit-scrollbar {
                    display: none;
                }

                .ls-tab-button {
                    background: transparent;
                    border: none;
                    color: inherit;
                    font-size: 0.95rem;
                    font-weight: 500;
                    line-height: 1.2;
                    padding: 0.25rem 0;
                    position: relative;
                    white-space: nowrap;
                    opacity: 0.6;
                    transition: opacity 0.2s ease;
                }

                .ls-tab-button::after {
                    content: '';
                    position: absolute;
                    left: 0;
                    bottom: 0;
                    width: 100%;
                    height: 2px;
                    background-color: transparent;
                    transition: background-color 0.2s ease;
                }

                .ls-tab-button.active {
                    opacity: 1;
                }

                .ls-tab-button.active::after {
                    background-color: currentColor;
                }

                .ls-tab-pane { display: none; }
                .ls-tab-pane.active { display: block; }
            </style>
        @endif
    @endpush

    @push('linkstack-body-start')
        @include('linkstack.modules.admin-bar')
        @include('linkstack.modules.share-button')
        @include('linkstack.modules.report-icon')
    @endpush

    @push('linkstack-content')
        @foreach($information as $info)
            @include('linkstack.elements.avatar')
            @include('linkstack.elements.heading')
            @include('linkstack.elements.bio')
        @endforeach
        @include('linkstack.elements.icons')

        @if($catalogEnabled ?? false)
            <div class="ls-tab-card">
                <div class="ls-tab-buttons" role="tablist">
                    <button class="ls-tab-button active" type="button" data-ls-tab-target="ls-tab-profile" aria-selected="true">Perfil</button>
                    <button class="ls-tab-button" type="button" data-ls-tab-target="ls-tab-catalog" data-catalog-url="{{ $catalogEmbedUrl }}" aria-selected="false">Catálogo</button>
                </div>
                <div class="mt-3">
                    <div id="ls-tab-profile" class="ls-tab-pane active">
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
                    const tabCard = document.querySelector('.ls-tab-card');
                    let catalogLoaded = false;

                    function updateTabCardBackground(targetId) {
                        if (!tabCard) return;
                        if (targetId === 'ls-tab-catalog') {
                            tabCard.classList.add('ls-tab-card--catalog');
                        } else {
                            tabCard.classList.remove('ls-tab-card--catalog');
                        }
                    }

                    function setActive(targetId) {
                        Object.values(panes).forEach(pane => pane.classList.remove('active'));
                        buttons.forEach(btn => {
                            btn.classList.remove('active');
                            btn.setAttribute('aria-selected', 'false');
                        });

                        const targetPane = document.getElementById(targetId);
                        if (!targetPane) return;

                        targetPane.classList.add('active');
                        const activeButton = Array.from(buttons).find(btn => btn.dataset.lsTabTarget === targetId);
                        if (activeButton) {
                            activeButton.classList.add('active');
                            activeButton.setAttribute('aria-selected', 'true');
                        }

                        updateTabCardBackground(targetId);
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

                    updateTabCardBackground('ls-tab-profile');
                });
            </script>
        @endpush
    @endif
@endsection
