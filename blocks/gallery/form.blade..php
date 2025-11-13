@php
    $selectedImages = collect($selected_images ?? [])
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->values();

    $availableImages = collect($galleryImages ?? [])
        ->filter(function ($image) {
            return is_array($image) && !empty($image['id']) && !empty($image['url']);
        })
        ->values();
@endphp

@once('gallery-block-form-styles')
    <style>
        .gallery-picker {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .gallery-picker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
        }

        .gallery-picker-item {
            position: relative;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 2px solid transparent;
            background: #f9fafb;
            color: #1f2937;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            display: flex;
            flex-direction: column;
        }

        .gallery-picker-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .gallery-picker-thumb {
            position: relative;
            padding-top: 70%;
            overflow: hidden;
        }

        .gallery-picker-thumb img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s ease;
        }

        .gallery-picker-label {
            padding: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            line-height: 1.25;
        }

        .gallery-picker-item:hover img,
        .gallery-picker-item:focus-within img {
            transform: scale(1.03);
        }

        .gallery-picker-item:hover,
        .gallery-picker-item:focus-within {
            border-color: rgba(37, 99, 235, 0.4);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .gallery-picker-item.is-selected {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
        }

        .gallery-picker-empty {
            background: #fef3c7;
            color: #92400e;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }

        .gallery-picker-empty p {
            margin-bottom: 0;
        }

        .gallery-picker-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .gallery-picker-button {
            border-radius: 9999px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #1f2937;
            padding: 0.35rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .gallery-picker-button:hover,
        .gallery-picker-button:focus-visible {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
            outline: none;
        }
    </style>
@endonce

<div class="gallery-picker" data-gallery-picker>
    <p class="text-muted mb-0">{{ __('messages.gallery.form.instructions') }}</p>

    @if($availableImages->isNotEmpty())
        <div class="gallery-picker-actions">
            <button type="button" class="gallery-picker-button" data-gallery-select-all>
                {{ __('messages.gallery.form.select_all') }}
            </button>
            <button type="button" class="gallery-picker-button" data-gallery-clear-selection>
                {{ __('messages.gallery.form.clear') }}
            </button>
        </div>

        <div class="gallery-picker-grid">
            @foreach($availableImages as $image)
                @php
                    $isSelected = $selectedImages->contains($image['id']);
                @endphp
                <label class="gallery-picker-item @if($isSelected) is-selected @endif">
                    <input type="checkbox" name="selected_images[]" value="{{ $image['id'] }}" @if($isSelected) checked @endif>
                    <span class="gallery-picker-thumb">
                        <img src="{{ $image['url'] }}" alt="{{ $image['title'] }}">
                    </span>
                    <span class="gallery-picker-label">{{ $image['title'] }}</span>
                </label>
            @endforeach
        </div>
    @else
        <div class="gallery-picker-empty">
            <strong>{{ __('messages.gallery.form.empty') }}</strong>
            <p>{{ __('messages.gallery.form.empty_hint') }}</p>
        </div>
    @endif
</div>

@once('gallery-block-form-scripts')
    <script>
        (function () {
            function updateItemState(checkbox) {
                if (!checkbox) {
                    return;
                }

                var item = checkbox.closest('.gallery-picker-item');
                if (!item) {
                    return;
                }

                if (checkbox.checked) {
                    item.classList.add('is-selected');
                } else {
                    item.classList.remove('is-selected');
                }
            }

            function initGalleryPicker(root) {
                if (!root || root.dataset.galleryPickerInitialized === 'true') {
                    return;
                }

                root.dataset.galleryPickerInitialized = 'true';

                var checkboxes = root.querySelectorAll('input[type="checkbox"][name="selected_images[]"]');
                checkboxes.forEach(function (checkbox) {
                    updateItemState(checkbox);
                    checkbox.addEventListener('change', function () {
                        updateItemState(checkbox);
                    });
                });

                var selectAll = root.querySelector('[data-gallery-select-all]');
                if (selectAll) {
                    selectAll.addEventListener('click', function (event) {
                        event.preventDefault();
                        checkboxes.forEach(function (checkbox) {
                            if (!checkbox.checked) {
                                checkbox.checked = true;
                                checkbox.dispatchEvent(new Event('change'));
                            }
                        });
                    });
                }

                var clearSelection = root.querySelector('[data-gallery-clear-selection]');
                if (clearSelection) {
                    clearSelection.addEventListener('click', function (event) {
                        event.preventDefault();
                        checkboxes.forEach(function (checkbox) {
                            if (checkbox.checked) {
                                checkbox.checked = false;
                                checkbox.dispatchEvent(new Event('change'));
                            }
                        });
                    });
                }
            }

            function bootstrapPickers() {
                document.querySelectorAll('[data-gallery-picker]').forEach(function (root) {
                    initGalleryPicker(root);
                });
            }

            document.addEventListener('DOMContentLoaded', bootstrapPickers);
            document.addEventListener('contentLoaded', bootstrapPickers);
        })();
    </script>
@endonce