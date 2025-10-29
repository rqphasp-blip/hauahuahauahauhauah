@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-gray-100 py-10">
    <div class="mx-auto max-w-lg rounded-lg border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $campaign->name }}</h1>
        <p class="mt-2 text-gray-600">Preencha o formulário abaixo para enviar suas informações.</p>

        @if(session('success'))
            <div class="mt-4 rounded-md bg-green-100 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('contatos.form.submit', $campaign->slug) }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="lead_name" class="mb-1 block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" name="lead_name" id="lead_name" value="{{ old('lead_name') }}" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('lead_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="lead_email" class="mb-1 block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" name="lead_email" id="lead_email" value="{{ old('lead_email') }}" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('lead_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="lead_phone" class="mb-1 block text-sm font-medium text-gray-700">Telefone (opcional)</label>
                <input type="text" name="lead_phone" id="lead_phone" value="{{ old('lead_phone') }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('lead_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="lead_message" class="mb-1 block text-sm font-medium text-gray-700">Mensagem (opcional)</label>
                <textarea name="lead_message" id="lead_message" rows="4"
                          class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('lead_message') }}</textarea>
                @error('lead_message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                Enviar informações
            </button>
        </form>
    </div>
</div>
@endsection