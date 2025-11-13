<?php

function handleLinkType($request, $linkType)
{
    try {
        $repository = app(\App\Support\Gallery\GalleryRepository::class);
    } catch (\Throwable $exception) {
        $repository = null;
    }

    $selected = collect($request->input('selected_images', []))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->values();

    $rules = [
        'selected_images' => [
            'required',
            'array',
            'min:1',
            function ($attribute, $value, $fail) use ($selected, $repository) {
                if ($selected->isEmpty() || !$repository) {
                    return;
                }

                if (!$repository->available()) {
                    $fail(__('messages.gallery.validation.unavailable'));
                    return;
                }

                if ($repository->getImagesByIds($selected->all())->count() !== $selected->count()) {
                    $fail(__('messages.gallery.validation.invalid_selection'));
                }
            },
        ],
        'selected_images.*' => ['integer'],
    ];

    $linkData = [
        'title' => null,
        'selected_images' => $selected->values()->all(),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}