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

        @php
            $hasCatalog = $catalogEnabled ?? false;
            $hasLeads   = isset($leadCampaign) && $leadCampaign;
        @endphp

        @if($hasCatalog)
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
            @include('linkstack.elements.profile-hero')
            @include('linkstack.elements.heading')
            @include('linkstack.elements.bio')
        @endforeach
        @include('linkstack.elements.icons')

        @php
            $hasCatalog = $catalogEnabled ?? false;
            $hasLeads   = isset($leadCampaign) && $leadCampaign;

            // tab vindo do controller: links | catalog | leads
            $requestedTab = $activeTab ?? 'links';

            // decide qual aba fica ativa de fato
            if ($requestedTab === 'catalog' && !$hasCatalog) {
                $requestedTab = 'links';
            }
            if ($requestedTab === 'leads' && !$hasLeads) {
                $requestedTab = 'links';
            }

            $activePaneId = match ($requestedTab) {
                'catalog' => 'ls-tab-catalog',
                'leads'   => 'ls-tab-leads',
                default   => 'ls-tab-profile',
            };

            $leadFormEmbedUrl = null;
            if ($hasLeads) {
                // supõe rota pública leads01.form(slug)
                $leadFormEmbedUrl = route('leads01.form', $leadCampaign->slug) . '?embed=1';
            }
        @endphp

        @if($hasCatalog || $hasLeads)
            <div class="ls-tab-card {{ $activePaneId === 'ls-tab-catalog' ? 'ls-tab-card--catalog' : '' }}">
                <div class="ls-tab-buttons" role="tablist">
                    {{-- Aba Perfil / Links --}}
                    <button
                        class="ls-tab-button {{ $activePaneId === 'ls-tab-profile' ? 'active' : '' }}"
                        type="button"
                        data-ls-tab-target="ls-tab-profile"
                        aria-selected="{{ $activePaneId === 'ls-tab-profile' ? 'true' : 'false' }}"
                    >
                        Links
                    </button>

                    {{-- Aba Catálogo (se habilitado) --}}
                    @if($hasCatalog)
                        <button
                            class="ls-tab-button {{ $activePaneId === 'ls-tab-catalog' ? 'active' : '' }}"
                            type="button"
                            data-ls-tab-target="ls-tab-catalog"
                            data-catalog-url="{{ $catalogEmbedUrl }}"
                            aria-selected="{{ $activePaneId === 'ls-tab-catalog' ? 'true' : 'false' }}"
                        >
                            Catálogo
                        </button>
                    @endif

                    {{-- Aba Leads (se houver campanha ativa) --}}
                    @if($hasLeads && $leadFormEmbedUrl)
                        <button
                            class="ls-tab-button {{ $activePaneId === 'ls-tab-leads' ? 'active' : '' }}"
                            type="button"
                            data-ls-tab-target="ls-tab-leads"
                            data-leads-url="{{ $leadFormEmbedUrl }}"
                            aria-selected="{{ $activePaneId === 'ls-tab-leads' ? 'true' : 'false' }}"
                        >
                            Lead Form
                        </button>
                    @endif
                </div>

                <div class="mt-3">
                    {{-- PANE PERFIL/LINKS --}}
                    <div
                        id="ls-tab-profile"
                        class="ls-tab-pane {{ $activePaneId === 'ls-tab-profile' ? 'active' : '' }}"
                    >
                        @include('linkstack.elements.buttons')
                        @yield('content')
                        @include('linkstack.modules.footer')
                    </div>

                    {{-- PANE CATÁLOGO --}}
                    @if($hasCatalog)
                        <div
                            id="ls-tab-catalog"
                            class="ls-tab-pane {{ $activePaneId === 'ls-tab-catalog' ? 'active' : '' }}"
                            data-loaded="{{ $activePaneId === 'ls-tab-catalog' ? 'true' : 'false' }}"
                        >
                            <div class="text-center text-muted py-4" id="ls-catalog-placeholder">
                                @if($activePaneId === 'ls-tab-catalog')
                                    Carregando catálogo...
                                @else
                                    Clique em "Catálogo" para carregar os produtos.
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- PANE LEADS --}}
                    @if($hasLeads && $leadFormEmbedUrl)
                        <div
                            id="ls-tab-leads"
                            class="ls-tab-pane {{ $activePaneId === 'ls-tab-leads' ? 'active' : '' }}"
                            data-loaded="{{ $activePaneId === 'ls-tab-leads' ? 'true' : 'false' }}"
                        >
                            <div class="text-center text-muted py-4" id="ls-leads-placeholder">
                                @if($activePaneId === 'ls-tab-leads')
                                    Carregando formulário...
                                @else
                                    Clique em "Lead Form" para carregar o formulário.
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Sem catálogo e sem leads: comportamento original --}}
            @include('linkstack.elements.buttons')
            @yield('content')
            @include('linkstack.modules.footer')
        @endif
    @endpush

    @php
        $hasCatalog = $catalogEnabled ?? false;
        $hasLeads   = isset($leadCampaign) && $leadCampaign;
    @endphp

    @if($hasCatalog || $hasLeads)
        @push('linkstack-body-end')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const buttons = document.querySelectorAll('[data-ls-tab-target]');
                    const panes = {
                        profile: document.getElementById('ls-tab-profile'),
                        catalog: document.getElementById('ls-tab-catalog'),
                        leads: document.getElementById('ls-tab-leads'),
                    };
                    const tabCard = document.querySelector('.ls-tab-card');

                    let catalogLoaded = (panes.catalog && panes.catalog.dataset.loaded === 'true');
                    let leadsLoaded   = (panes.leads   && panes.leads.dataset.loaded   === 'true');

                    function updateTabCardBackground(targetId) {
                        if (!tabCard) return;
                        if (targetId === 'ls-tab-catalog') {
                            tabCard.classList.add('ls-tab-card--catalog');
                        } else {
                            tabCard.classList.remove('ls-tab-card--catalog');
                        }
                    }

                    function setActive(targetId) {
                        Object.values(panes).forEach(pane => {
                            if (!pane) return;
                            pane.classList.remove('active');
                        });
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
                        if (!panes.catalog || catalogLoaded) return;
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
                            panes.catalog.dataset.loaded = 'true';
                        } catch (e) {
                            placeholder.textContent = 'Não foi possível carregar o catálogo.';
                        }
                    }

                    async function loadLeads(button) {
                        if (!panes.leads || leadsLoaded) return;
                        const url = button.dataset.leadsUrl;
                        const placeholder = document.getElementById('ls-leads-placeholder');
                        if (!url || !placeholder) return;

                        placeholder.textContent = 'Carregando formulário...';

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

                            leadsLoaded = true;
                            panes.leads.dataset.loaded = 'true';
                        } catch (e) {
                            placeholder.textContent = 'Não foi possível carregar o formulário.';
                        }
                    }

                    buttons.forEach(button => {
                        button.addEventListener('click', async () => {
                            const targetId = button.dataset.lsTabTarget;
                            if (!targetId) return;

                            setActive(targetId);

                            if (targetId === 'ls-tab-catalog') {
                                await loadCatalog(button);
                            } else if (targetId === 'ls-tab-leads') {
                                await loadLeads(button);
                            }
                        });
                    });

                    // Garante que o estado visual do card reflita a aba inicial
                    const initiallyActive = document.querySelector('.ls-tab-pane.active');
                    if (initiallyActive) {
                        updateTabCardBackground(initiallyActive.id);
                    }
                });
            </script>
        @endpush
    @endif
@endsection
