<div>
    @if (session("success"))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session("success") }}
        </div>
    @endif
    @if (session("error"))
        <div class="mb-4 font-medium text-sm text-red-600">
            {{ session("error") }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4">
            <div class="font-medium text-red-600">{{ __("Opa! Algo deu errado.") }}</div>
            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/perfil/banner/upload') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="banner_image" class="block font-medium text-sm text-gray-700">{{ __("Imagem do Banner (JPG, PNG, GIF, WEBP - Máx 2MB)") }}</label>
            <input id="banner_image" name="banner_image" type="file" class="block mt-1 w-full" required autofocus />
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __("Enviar Banner") }}
            </button>
        </div>
    </form>

    @if (Auth::user()->profile_banner_path)
        <hr class="my-6">
        <h3 class="text-lg font-medium text-gray-900">Banner Atual</h3>
        <div class="mt-2">
            <img src="{{ Storage::url(Auth::user()->profile_banner_path) }}" alt="Banner do Perfil" style="max-height: 250px; width: auto; border-radius: 8px; margin-top: 10px;">
        </div>
        <form method="POST" action="{{ url('/perfil/banner/remover') }}" class="mt-4">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __("Remover Banner") }}
            </button>
        </form>
    @endif
</div>
