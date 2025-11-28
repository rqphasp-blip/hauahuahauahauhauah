@php
    $modalId = 'leadCaptureModal-' . $campaign->id;
@endphp

<div class="lead-capture-widget" data-campaign="{{ $campaign->id }}">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
        {{ __('Quero participar') }}
    </button>
</div>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}-label">{{ $campaign->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Fechar') }}"></button>
            </div>
            <div class="modal-body">
                <div class="lead-capture-feedback alert alert-success d-none" role="alert"></div>
                <form class="lead-capture-form" data-action="{{ route('leads01.public.submit', $campaign) }}">
                    @csrf
                    @foreach($fields as $field)
                        @php
                            $inputName = 'fields[' . $field->id . ']';
                            $fieldId = 'lead-field-' . $campaign->id . '-' . $field->id;
                        @endphp
                        <div class="mb-3">
                            <label for="{{ $fieldId }}" class="form-label">{{ $field->label }}@if($field->required) <span class="text-danger">*</span>@endif</label>
                            @if($field->type === 'textarea')
                                <textarea class="form-control" id="{{ $fieldId }}" name="{{ $inputName }}" rows="3" {{ $field->required ? 'required' : '' }}></textarea>
                            @else
                                <input class="form-control" id="{{ $fieldId }}" name="{{ $inputName }}" type="{{ $field->type === 'phone' ? 'tel' : $field->type }}" {{ $field->required ? 'required' : '' }}>
                            @endif
                            <div class="invalid-feedback"></div>
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary w-100">
                        <span class="label-default">{{ __('Enviar') }}</span>
                        <span class="label-loading d-none"><span class="spinner-border spinner-border-sm"></span></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.lead-capture-form').forEach(form => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const submitButton = form.querySelector('button[type="submit"]');
                const defaultLabel = submitButton.querySelector('.label-default');
                const loadingLabel = submitButton.querySelector('.label-loading');
                const feedback = form.closest('.modal-content').querySelector('.lead-capture-feedback');

                submitButton.disabled = true;
                defaultLabel.classList.add('d-none');
                loadingLabel.classList.remove('d-none');
                feedback.classList.add('d-none');
                feedback.classList.remove('alert-danger');
                feedback.classList.add('alert-success');
                feedback.textContent = '';

                form.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                });

                try {
                    const response = await fetch(form.dataset.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        },
                        body: new FormData(form),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (data.errors) {
                            Object.entries(data.errors).forEach(([key, messages]) => {
                                const input = form.querySelector(`[name="${key}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    const feedbackElement = input.closest('.mb-3').querySelector('.invalid-feedback');
                                    if (feedbackElement) {
                                        feedbackElement.textContent = messages.join(' ');
                                    }
                                }
                            });
                        }
                        throw new Error(data.message || '{{ __('Não foi possível enviar o formulário.') }}');
                    }

                    form.reset();
                    feedback.textContent = data.message || '{{ __('Obrigado pelo contato!') }}';
                    feedback.classList.remove('d-none');
                } catch (error) {
                    feedback.textContent = error.message;
                    feedback.classList.remove('d-none');
                    feedback.classList.remove('alert-success');
                    feedback.classList.add('alert-danger');
                } finally {
                    submitButton.disabled = false;
                    defaultLabel.classList.remove('d-none');
                    loadingLabel.classList.add('d-none');
                }
            });
        });
    });
</script>
@endpush