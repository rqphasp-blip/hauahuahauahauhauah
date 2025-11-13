@php
    $params = json_decode($link->type_params ?? '{}', true) ?: [];
    $imageUrl = $params['image_url'] ?? null;
    $maxWidth = $params['max_width'] ?? 100;
    $maxWidth = is_numeric($maxWidth) ? max(10, min(100, (int) $maxWidth)) : 100;
    $altText = $link->title ?? '';
@endphp

@if(!empty($imageUrl))
    <div class="fadein block-image-link text-center" style="max-width: {{ $maxWidth }}%; margin: 0 auto;">
        @if(!empty($link->link))
            <a href="{{ $link->link }}" rel="noopener noreferrer nofollow">
        @endif
                <img src="{{ $imageUrl }}" alt="{{ $altText }}" style="width: 100%; height: auto;" />
        @if(!empty($link->link))
            </a>
        @endif
    </div>
@endif