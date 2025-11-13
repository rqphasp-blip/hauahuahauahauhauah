<div class="mb-3">
    <label for="image_url" class="form-label">{{ __('messages.Image URL') }}</label>
    <input type="url" name="image_url" id="image_url" value="{{ $image_url ?? '' }}" class="form-control" required />
</div>

<div class="mb-3">
    <label for="link" class="form-label">{{ __('messages.URL') }}</label>
    <input type="url" name="link" id="link" value="{{ $link ?? '' }}" class="form-control" required />
</div>

<div class="mb-3">
    <label for="title" class="form-label">{{ __('messages.Alt text') }}</label>
    <input type="text" name="title" id="title" value="{{ $title ?? '' }}" class="form-control" maxlength="255" />
</div>

<div class="mb-3">
    <label for="max_width" class="form-label">{{ __('messages.Maximum width (%)') }}</label>
    <input type="number" name="max_width" id="max_width" value="{{ $max_width ?? 100 }}" class="form-control" min="10" max="100" />
    <small class="form-text text-muted">{{ __('messages.Maximum width help') }}</small>
</div>