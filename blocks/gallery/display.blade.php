@php
    $typeParams = $link->type_params;
    if (is_string($typeParams)) {
        $typeParams = json_decode($typeParams, true) ?? [];
    }

    try {
        $repository = app(\App\Support\Gallery\GalleryRepository::class);
    } catch (\Throwable $exception) {
        $repository = null;
    }

    $selectedIds = collect($typeParams['selected_images'] ?? [])
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->values();

    $images = collect();

    if ($selectedIds->isNotEmpty() && $repository && $repository->available()) {
        $records = $repository->getImagesByIds($selectedIds->all());

        $mapped = $records
            ->map(function (array $image) {
                $full = $image['full_url'] ?? null;

                if (!$full) {
                    return null;
                }

                $thumb = $image['thumbnail_url'] ?? $full;
                $title = $image['title'] ?? __('messages.gallery.display.default_title');
                $description = $image['description'] ?? '';
                $alt = $image['alt'] ?? $title ?? __('messages.gallery.display.default_alt');

                return [
                    'id' => $image['id'],
                    'title' => $title ?: __('messages.gallery.display.default_title'),
                    'description' => $description,
                    'thumb' => $thumb ?: $full,
                    'full' => $full,
                    'alt' => $alt ?: __('messages.gallery.display.default_alt'),
                ];
            })
            ->filter()
            ->keyBy('id');

        $ordered = [];
        foreach ($selectedIds as $id) {
            if (isset($mapped[$id])) {
                $ordered[] = $mapped[$id];
            }
        }

        $images = collect($ordered);
    }
@endphp

@if($images->isNotEmpty())
    @once('gallery-block-styles')
        <style>
            .ls-gallery-block {
                width: min(100%, 80vw);
                margin: 0 auto 2rem auto;
            }

            .ls-gallery-grid {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }

            .ls-gallery-item {
                flex: 1 1 min(100%, calc(50% - 1rem));
                max-width: min(100%, calc(50% - 1rem));
                min-width: 140px;
            }

            @media (max-width: 575.98px) {
                .ls-gallery-item {
                    flex-basis: calc(50% - 1rem);
                    max-width: calc(50% - 1rem);
                }
            }

            @media (min-width: 1200px) {
                .ls-gallery-item {
                    flex: 1 1 calc(20% - 1rem);
                    max-width: calc(20% - 1rem);
                }
            }

            .ls-gallery-thumb {
                display: block;
                width: 100%;
                padding: 0;
                border: none;
                background: transparent;
                cursor: pointer;
                position: relative;
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 15px 35px -20px rgba(15, 23, 42, 0.45);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .ls-gallery-thumb::before {
                content: "";
                display: block;
                padding-top: 72%;
            }

            .ls-gallery-thumb img {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }

            .ls-gallery-thumb:hover,
            .ls-gallery-thumb:focus-visible {
                transform: translateY(-4px) scale(1.02);
                box-shadow: 0 25px 45px -25px rgba(37, 99, 235, 0.35);
            }

            .ls-gallery-thumb:hover img,
            .ls-gallery-thumb:focus-visible img {
                transform: scale(1.05);
            }

            .ls-gallery-thumb:focus-visible {
                outline: 3px solid rgba(37, 99, 235, 0.8);
                outline-offset: 4px;
            }

            .ls-gallery-modal[hidden] {
                display: none !important;
            }

            .ls-gallery-modal {
                position: fixed;
                inset: 0;
                z-index: 1050;
                background: rgba(15, 23, 42, 0.75);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }

            .ls-gallery-modal-dialog {
                position: relative;
                width: min(80vw, 960px);
                max-height: 90vh;
                background: #ffffff;
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 25px 45px -20px rgba(15, 23, 42, 0.55);
                display: flex;
                flex-direction: column;
            }

            .ls-gallery-modal-image {
                width: 100%;
                max-height: calc(90vh - 140px);
                object-fit: contain;
                background: #000;
            }

            .ls-gallery-modal-caption {
                padding: 1rem 1.5rem 1.5rem;
                text-align: center;
            }

            .ls-gallery-modal-caption h3 {
                font-size: 1.1rem;
                color: #111827;
                margin-bottom: 0.5rem;
            }

            .ls-gallery-modal-caption p {
                margin: 0;
                color: #4b5563;
                font-size: 0.95rem;
                line-height: 1.4;
            }

            .ls-gallery-modal-close {
                position: absolute;
                top: 0.75rem;
                right: 0.75rem;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 9999px;
                border: none;
                background: rgba(17, 24, 39, 0.75);
                color: #ffffff;
                font-size: 1.35rem;
                line-height: 1;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s ease, transform 0.2s ease;
            }

            .ls-gallery-modal-close:hover,
            .ls-gallery-modal-close:focus-visible {
                background: rgba(37, 99, 235, 0.9);
                transform: scale(1.05);
                outline: none;
            }

            body.ls-gallery-modal-open {
                overflow: hidden;
            }
        </style>
    @endonce

    @once('gallery-block-scripts')
        <script>
            (function () {
                const registeredBlocks = new WeakSet();

                function openModal(modal, data) {
                    if (!modal) {
                        return;
                    }

                    const image = modal.querySelector('[data-gallery-modal-image]');
                    const title = modal.querySelector('[data-gallery-modal-title]');
                    const description = modal.querySelector('[data-gallery-modal-description]');

                    if (image) {
                        image.src = data.full;
                        image.alt = data.alt;
                    }

                    if (title) {
                        title.textContent = data.title || '';
                        title.hidden = !data.title;
                    }

                    if (description) {
                        description.textContent = data.description || '';
                        description.hidden = !data.description;
                    }

                    modal.removeAttribute('hidden');
                    document.body.classList.add('ls-gallery-modal-open');
                }

                function closeModal(modal) {
                    if (!modal) {
                        return;
                    }

                    const image = modal.querySelector('[data-gallery-modal-image]');
                    if (image) {
                        image.src = '';
                    }

                    modal.setAttribute('hidden', 'hidden');
                    document.body.classList.remove('ls-gallery-modal-open');
                }

                function registerBlock(block) {
                    if (!block || registeredBlocks.has(block)) {
                        return;
                    }

                    const modalId = block.getAttribute('data-gallery-modal-id');
                    const modal = document.getElementById(modalId);

                    if (!modal) {
                        return;
                    }

                    registeredBlocks.add(block);

                    block.querySelectorAll('[data-gallery-open]').forEach(function (trigger) {
                        trigger.addEventListener('click', function () {
                            openModal(modal, {
                                full: trigger.getAttribute('data-gallery-src'),
                                alt: trigger.getAttribute('data-gallery-alt') || '',
                                title: trigger.getAttribute('data-gallery-title') || '',
                                description: trigger.getAttribute('data-gallery-description') || '',
                            });
                        });
                    });

                    modal.querySelectorAll('[data-gallery-close]').forEach(function (closer) {
                        closer.addEventListener('click', function () {
                            closeModal(modal);
                        });
                    });

                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) {
                            closeModal(modal);
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && !modal.hasAttribute('hidden')) {
                            closeModal(modal);
                        }
                    });
                }

                function registerById(id) {
                    const block = document.getElementById(id);
                    if (block) {
                        registerBlock(block);
                    }
                }

                function registerAll() {
                    document.querySelectorAll('[data-gallery-block]').forEach(registerBlock);
                }

                window.LinkstackGalleryBlock = window.LinkstackGalleryBlock || {};
                window.LinkstackGalleryBlock.registerById = registerById;
                window.LinkstackGalleryBlock.registerAll = registerAll;

                document.addEventListener('DOMContentLoaded', registerAll);

                var readyEvent;
                try {
                    readyEvent = new CustomEvent('LinkstackGalleryBlockReady');
                } catch (error) {
                    readyEvent = document.createEvent('CustomEvent');
                    readyEvent.initCustomEvent('LinkstackGalleryBlockReady', false, false, {});
                }

                document.dispatchEvent(readyEvent);
            })();
        </script>
    @endonce

    @php
        $blockId = 'gallery-block-' . $link->id;
        $modalId = 'gallery-modal-' . $link->id;
    @endphp

    <div class="ls-gallery-block" id="{{ $blockId }}" data-gallery-block data-gallery-modal-id="{{ $modalId }}">
        <div class="ls-gallery-grid">
            @foreach($images as $image)
                <div class="ls-gallery-item">
                    <button type="button"
                        class="ls-gallery-thumb"
                        data-gallery-open
                        data-gallery-src="{{ $image['full'] }}"
                        data-gallery-title="{{ $image['title'] }}"
                        data-gallery-description="{{ $image['description'] }}"
                        data-gallery-alt="{{ $image['alt'] }}">
                        <img src="{{ $image['thumb'] }}" alt="{{ $image['alt'] }}" loading="lazy">
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="ls-gallery-modal" id="{{ $modalId }}" hidden>
        <div class="ls-gallery-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
            <button type="button" class="ls-gallery-modal-close" aria-label="{{ __('messages.gallery.modal.close') }}" data-gallery-close>&times;</button>
            <img src="" alt="" class="ls-gallery-modal-image" data-gallery-modal-image>
            <div class="ls-gallery-modal-caption">
                
                <p data-gallery-modal-description hidden></p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            function initGalleryBlock() {
                if (window.LinkstackGalleryBlock && typeof window.LinkstackGalleryBlock.registerById === 'function') {
                    window.LinkstackGalleryBlock.registerById('{{ $blockId }}');
                }
            }

            if (window.LinkstackGalleryBlock && typeof window.LinkstackGalleryBlock.registerById === 'function') {
                initGalleryBlock();
            } else {
                document.addEventListener('LinkstackGalleryBlockReady', initGalleryBlock, { once: true });
            }
        })();
    </script>
@endif