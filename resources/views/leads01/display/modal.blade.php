<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $campaign->name }} - Formulário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-4">
        <h1 class="h3">{{ $campaign->name }}</h1>
        <p class="text-muted">Preencha os campos abaixo para participar.</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leadCaptureModal">Abrir formulário</button>
    </div>
</div>

<div class="modal fade" id="leadCaptureModal" tabindex="-1" aria-labelledby="leadCaptureLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadCaptureLabel">{{ $campaign->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="lead-capture-form">
                    @csrf
                    <div class="vstack gap-3">
                        @foreach ($fields as $field)
                            <div>
                                <label class="form-label">{{ $field->label }} @if($field->required)<span class="text-danger">*</span>@endif</label>
                                @php $name = $field->field_name; @endphp
                                @switch($field->field_type)
                                    @case('textarea')
                                        <textarea class="form-control" name="fields[{{ $name }}]" placeholder="{{ $field->placeholder }}" {{ $field->required ? 'required' : '' }}></textarea>
                                        @break
                                    @case('select')
                                        @php $options = json_decode($field->options ?? '[]', true) ?? []; @endphp
                                        <select class="form-select" name="fields[{{ $name }}]" {{ $field->required ? 'required' : '' }}>
                                            <option value="">Selecione</option>
                                            @foreach ($options as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @default
                                        <input type="{{ $field->field_type === 'tel' ? 'tel' : $field->field_type }}" class="form-control" name="fields[{{ $name }}]" placeholder="{{ $field->placeholder }}" {{ $field->required ? 'required' : '' }}>
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="form-errors"></div>
                    <div class="alert alert-success mt-3 d-none" id="form-success"></div>
                    <div class="d-grid mt-3">
                        <button class="btn btn-primary" type="submit">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('lead-capture-form');
        const errorsBox = document.getElementById('form-errors');
        const successBox = document.getElementById('form-success');

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            errorsBox.classList.add('d-none');
            successBox.classList.add('d-none');

            fetch("{{ route('leads01.submit', $campaign->slug) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: new FormData(form)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.errors) {
                        const messages = Object.values(data.errors).flat();
                        errorsBox.textContent = messages.join('\n');
                        errorsBox.classList.remove('d-none');
                        return;
                    }

                    form.reset();
                    successBox.textContent = data.message || 'Obrigado!';
                    successBox.classList.remove('d-none');
                })
                .catch(() => {
                    errorsBox.textContent = 'Não foi possível enviar o formulário. Tente novamente.';
                    errorsBox.classList.remove('d-none');
                });
        });
    });
</script>
</body>
</html>