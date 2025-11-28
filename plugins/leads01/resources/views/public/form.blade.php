@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-2">{{ $campaign->name }}</h1>
                    <p class="text-muted">{{ $campaign->description }}</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
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

                    <form method="POST" action="{{ route('leads01.public.submit', $campaign->slug) }}">
                        @csrf

                        @foreach($fields as $field)
                            <div class="mb-3">
                                <label class="form-label">{{ $field->label }} @if($field->required) <span class="text-danger">*</span> @endif</label>

                                @switch($field->field_type)
                                    @case('email')
                                        <input type="email" name="{{ $field->field_name }}" class="form-control" value="{{ old($field->field_name) }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif maxlength="150">
                                        @break
                                    @case('number')
                                        <input type="number" name="{{ $field->field_name }}" class="form-control" value="{{ old($field->field_name) }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif>
                                        @break
                                    @case('tel')
                                        <input type="tel" name="{{ $field->field_name }}" class="form-control" value="{{ old($field->field_name) }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif maxlength="30">
                                        @break
                                    @case('textarea')
                                        <textarea name="{{ $field->field_name }}" class="form-control" rows="3" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif>{{ old($field->field_name) }}</textarea>
                                        @break
                                    @case('select')
                                        @php $options = is_array($field->options) ? $field->options : json_decode($field->options ?? '[]', true); @endphp
                                        <select name="{{ $field->field_name }}" class="form-select" @if($field->required) required @endif>
                                            <option value="">Selecione...</option>
                                            @foreach($options as $option)
                                                <option value="{{ $option }}" {{ old($field->field_name) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @default
                                        <input type="text" name="{{ $field->field_name }}" class="form-control" value="{{ old($field->field_name) }}" placeholder="{{ $field->placeholder }}" @if($field->required) required @endif maxlength="255">
                                @endswitch
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary w-100">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection