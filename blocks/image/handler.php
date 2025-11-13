<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Handles the logic for "image" link type.
 *
 * @param \Illuminate\Http\Request $request The incoming request.
 * @param mixed $linkType The link type information.
 * @return array The prepared link data.
 */
function handleLinkType($request, $linkType) {

    $rules = [
        'image_file' => [
            'nullable',
            'required_without:existing_image',
            'image',
            'mimes:jpeg,jpg,png,gif,webp',
            'max:5120',
        ],
        'existing_image' => [
            'nullable',
            'string',
            'max:2048',
        ],
        'link' => [
            'required',
            'url',
            'max:2048',
        ],
        'title' => [
            'nullable',
            'string',
            'max:255',
        ],
        'max_width' => [
            'nullable',
            'integer',
            'min:10',
            'max:100',
        ],
    ];

    $maxWidth = $request->max_width ?? 100;
    $maxWidth = is_numeric($maxWidth) ? max(10, min(100, (int) $maxWidth)) : 100;

    $existingImage = $request->input('existing_image');
    $existingImage = is_string($existingImage) ? trim($existingImage) : null;
    if ($existingImage === '') {
        $existingImage = null;
    }
    if ($existingImage && !filter_var($existingImage, FILTER_VALIDATE_URL)) {
        $existingImage = str_replace('\\', '/', $existingImage);
        $existingImage = ltrim($existingImage, '/');
        $existingImage = preg_replace('#/{2,}#', '/', $existingImage);
        if (str_contains($existingImage, '..')) {
            $existingImage = null;
        }
    }

    $title = $request->title ?? '';
    $title = is_string($title) ? trim($title) : '';
    $title = strip_tags($title);

    $link = filter_var($request->link, FILTER_SANITIZE_URL);

    $linkData = [
        'title' => $title !== '' ? $title : null,
        'link' => $link,
        'button_id' => "93",
        'image_url' => $existingImage,
        'max_width' => $maxWidth,
    ];

    $afterValidation = function ($request, array $linkData, array $validated) use ($existingImage) {
        $uploadedFile = $request->file('image_file');

        if ($uploadedFile) {
            $uploadDirectory = public_path('uploads/link-images');

            if (!File::isDirectory($uploadDirectory)) {
                File::makeDirectory($uploadDirectory, 0755, true);

                $gitignorePath = $uploadDirectory . DIRECTORY_SEPARATOR . '.gitignore';
                if (!File::exists($gitignorePath)) {
                    File::put($gitignorePath, "*\n!.gitignore\n");
                }
            }

            $filename = Str::uuid()->toString() . '.' . $uploadedFile->getClientOriginalExtension();
            $uploadedFile->move($uploadDirectory, $filename);

            if ($existingImage && !filter_var($existingImage, FILTER_VALIDATE_URL)) {
                $previousPath = public_path($existingImage);
                if (Str::startsWith($existingImage, 'uploads/link-images/') && File::exists($previousPath)) {
                    File::delete($previousPath);
                }
            }

            $linkData['image_url'] = 'uploads/link-images/' . $filename;
        } elseif (!empty($existingImage)) {
            $linkData['image_url'] = $existingImage;
        }

        return $linkData;
    };

    return ['rules' => $rules, 'linkData' => $linkData, 'after_validation' => $afterValidation];
}