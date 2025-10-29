@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Minhas campanhas de contato</h1>
            <p class="text-gray-600">Organize e acompanhe seus leads por campanha.</p>
        </div>
        <a href="{{ route('contatos.campaigns.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            Nova campanha
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-100 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-100 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($campaigns->isEmpty())
        <div class="rounded-md border border-dashed border-gray-300 p-8 text-center text-gray-500">
            Você ainda não criou nenhuma campanha. Clique em "Nova campanha" para começar.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($campaigns as $campaign)
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $campaign->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Criada em {{ \Illuminate\Support\Carbon::parse($campaign->created_at)->format('d/m/Y H:i') }}
                    </p>
                    @if($campaign->description)
                        <p class="mt-3 text-gray-700">{{ $campaign->description }}</p>
                    @endif
                    <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
                        <span><strong>{{ $leadTotals[$campaign->id] ?? 0 }}</strong> leads capturados</span>
                        <a href="{{ route('contatos.campaigns.show', $campaign->id) }}" class="text-blue-600 hover:text-blue-800">
                            Gerenciar
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection