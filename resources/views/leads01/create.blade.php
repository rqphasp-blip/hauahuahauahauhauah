@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-3">Criar campanha de leads</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Corrija os erros abaixo:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leads01.store') }}" method="POST">
        @csrf
       @include('leads01._form')
    </form>
</div>
@endsection