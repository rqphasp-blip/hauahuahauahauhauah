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
            // Normaliza flags para facilitar o uso no Blade
            $hasCatalog = $catalogEnabled ?? false;
            // Se tiver variável $leadsEnabled vinda do controller, respeita.
            // Caso contrário, considera que existe leads se $leadCampaign não estiver vazio.
            $hasLeads   = isset($leadsEnabled)
                ? ($leadsEnabled && !empty($leadCampaign))
                : (!empty($leadCampaign));
        @endphp

        @if($hasCatalog || $hasLeads)
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
            $hasLeads   = isset($leadsEnabled)
                ? ($leadsEnabled && !empty($leadCampaign))
                : (!empty($leadCampaign));
        @endphp

        @if($hasCatalog || $hasLeads)
            <div class="ls-tab-card">
                <div class="ls-tab-buttons" role="tablist">
                    {{-- Aba Perfil (sempre) --}}
                    <button
                        class="ls-tab-button active"
                        type="button"
                        data-ls-tab-target="ls-tab-profile"
                        aria-selected="true">
                        Perfil
                    </button>

                    {{-- Aba Catálogo (se habilitado) --}}
                    @if($hasCatalog)
                        <button
                            class="ls-tab-button"
                            type="button"
                            data-ls-tab-target="ls-tab-catalog"
                            data-catalog-url="{{ $catalogEmbedUrl }}"
                            aria-selected="false">
                            Catálogo
                        </button>
                    @endif

                    {{-- Aba Formulário (se houver campanha válida) --}}
                    @if($hasLeads)
                        <button
                            class="ls-tab-button"
                            type="button"
                            data-ls-tab-target="ls-tab-leads"
                            aria-selected="false">
                            {{ $leadCampaign->name ?? 'Formulário' }}
                        </button>
                    @endif
                </div>

                <div class="mt-3">
                    {{-- CONTEÚDO: PERFIL --}}
                    <div id="ls-tab-profile" class="ls-tab-pane active">
                        @include('linkstack.elements.buttons')
                        @yield('content')
                        @include('linkstack.modules.footer')
                    </div>

                    {{-- CONTEÚDO: CATÁLOGO (lazy load via fetch) --}}
                    @if($hasCatalog)
                        <div id="ls-tab-catalog" class="ls-tab-pane" data-loaded="false">
                            <div class="text-center text-muted py-4" id="ls-catalog-placeholder">
                                Clique na aba Catálogo para carregar os produtos.
                            </div>
                        </div>
                    @endif

                    {{-- CONTEÚDO: LEADS --}}
                    @if($hasLeads)
                        <div id="ls-tab-leads" class="ls-tab-pane">
                            <div class="py-3">
                                <h2 class="h5 mb-3">{{ $leadCampaign->name }}</h2>

                                @if($leadCampaign->description)
                                    <p class="text-muted">{{ $leadCampaign->description }}</p>
                                @endif

                                @if(session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
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

                                {{-- IMPORTANTE: usa rota nomeada do plugin --}}
                                <form method="POST"
                                      action="{{ route('leads01.submit', $leadCampaign->slug) }}">
                                    @csrf

                                    @foreach($leadCampaign->fields as $field)
                                        @php
                                            $fieldId   = 'field_'.$field->id;
                                            $label     = $field->label ?? $field->name;
                                            $required  = (bool) ($field->required ?? false);
                                            $type      = $field->type ?? 'text';
                                            $oldValue  = old($fieldId);

                                            $optionsRaw = $field->options ?? [];
                                            if (is_string($optionsRaw)) {
                                                $options = json_decode($optionsRaw, true) ?? [];
                                            } elseif (is_array($optionsRaw)) {
                                                $options = $optionsRaw;
                                            } else {
                                                $options = [];
                                            }
                                        @endphp

                                        <div class="mb-3">
                                            <label for="{{ $fieldId }}" class="form-label">
                                                {{ $label }} @if($required) * @endif
                                            </label>

                                            @if($type === 'textarea')
                                                <textarea
                                                    id="{{ $fieldId }}"
                                                    name="{{ $fieldId }}"
                                                    class="form-control"
                                                    rows="3"
                                                    @if($required) required @endif>{{ $oldValue }}</textarea>
                                            @elseif($type === 'select')
                                                <select
                                                    id="{{ $fieldId }}"
                                                    name="{{ $fieldId }}"
                                                    class="form-select"
                                                    @if($required) required @endif>
                                                    <option value="">Selecione...</option>
                                                    @foreach($options as $opt)
                                                        <option value="{{ $opt }}" @if($oldValue === $opt) selected @endif>
                                                            {{ $opt }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                @php
                                                    $htmlType = in_array($type, ['email', 'tel', 'number', 'text'], true)
                                                        ? $type
                                                        : 'text';
                                                @endphp
                                                <input
                                                    type="{{ $htmlType }}"
                                                    id="{{ $fieldId }}"
                                                    name="{{ $fieldId }}"
                                                    class="form-control"
                                                    value="{{ $oldValue }}"
                                                    @if($required) required @endif>
                                            @endif
                                        </div>
                                    @endforeach

                                    <button type="submit" class="btn btn-primary w-100">
                                        Enviar
                                    </button>
                                </form>

                                @if($leadCampaign->thank_you_message)
                                    <p class="text-muted small mt-2">
                                        Após o envio, o usuário verá a mensagem:
                                        “{{ $leadCampaign->thank_you_message }}”
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Comportamento original: sem catálogo/sem leads, só os botões padrão --}}
            @include('linkstack.elements.buttons')
            @yield('content')
            @include('linkstack.modules.footer')
        @endif
    @endpush

    @php
        $hasCatalog = $catalogEnabled ?? false;
        $hasLeads   = isset($leadsEnabled)
            ? ($leadsEnabled && !empty($leadCampaign))
            : (!empty($leadCampaign));
    @endphp

    @if($hasCatalog || $hasLeads)
        @push('linkstack-body-end')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const buttons = document.querySelectorAll('[data-ls-tab-target]');
                    const panes = {};
                    ['ls-tab-profile', 'ls-tab-catalog', 'ls-tab-leads'].forEach(function (id) {
                        const el = document.getElementById(id);
                        if (el) {
                            panes[id] = el;
                        }
                    });

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
                        Object.values(panes).forEach(function (pane) {
                            pane.classList.remove('active');
                        });

                        buttons.forEach(function (btn) {
                            btn.classList.remove('active');
                            btn.setAttribute('aria-selected', 'false');
                        });

                        const targetPane = document.getElementById(targetId);
                        if (!targetPane) return;

                        targetPane.classList.add('active');

                        const activeButton = Array.from(buttons).find(function (btn) {
                            return btn.dataset.lsTabTarget === targetId;
                        });
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

                            placeholder.querySelectorAll('script').forEach(function (script) {
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

                    buttons.forEach(function (button) {
                        button.addEventListener('click', async function () {
                            const targetId = button.dataset.lsTabTarget;
                            if (!targetId) return;

                            setActive(targetId);

                            if (targetId === 'ls-tab-catalog' && button.dataset.catalogUrl) {
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
