@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <h1 class="mb-6 text-2xl font-semibold text-gray-900">Criar nova campanha</h1>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('contatos.campaigns.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Nome da campanha</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Descrição (opcional)</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('contatos.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-gray-700">
                    Cancelar
                </a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                    Criar campanha
                </button>
            </div>
        </form>
    </div>
</div>
@endsection