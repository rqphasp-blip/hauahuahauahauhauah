@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $campaign->name }}</h1>
            <p class="text-gray-600">Formulário público: <a href="{{ $formUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $formUrl }}</a></p>
        </div>
        <a href="{{ route('contatos.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Voltar para campanhas</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Adicionar lead manualmente</h2>
            <form action="{{ route('contatos.leads.store', $campaign->id) }}" method="POST" class="space-y-4">
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
                <div class="text-right">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">
                        Salvar lead
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Leads capturados</h2>
            @if($leads->isEmpty())
                <p class="text-gray-500">Nenhum lead foi capturado ainda.</p>
            @else
                <div class="space-y-4">
                    @foreach($leads as $lead)
                        <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $lead->lead_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $lead->lead_email }}</p>
                                    @if($lead->lead_phone)
                                        <p class="text-sm text-gray-600">{{ $lead->lead_phone }}</p>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($lead->created_at)->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($lead->lead_message)
                                <p class="mt-3 text-sm text-gray-700">{{ $lead->lead_message }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection