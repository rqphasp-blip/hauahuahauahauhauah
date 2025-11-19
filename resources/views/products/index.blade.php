@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Produtos</h1>
            <p class="text-muted mb-1">Cadastre e edite seus produtos. As categorias são gerenciadas em uma página dedicada.</p>
            <p class="small mb-0">Link público do catálogo: <a href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicUrl }}</a></p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="{{ route('products.categories.index') }}">Gerenciar categorias</a>
            @if($editingProduct)
                <a class="btn btn-outline-secondary" href="{{ route('products.index') }}">Nova inserção</a>
            @endif
        </div>
    </div>

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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">{{ $editingProduct ? 'Editar produto' : 'Novo produto' }}</div>
                        <div class="small text-muted">Selecione a categoria e informe os detalhes. A foto é opcional.</div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $editingProduct ? route('products.update', $editingProduct->id) : route('products.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if($editingProduct)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="category_id" class="form-select" required>
                                <option value="" disabled {{ $editingProduct ? '' : 'selected' }}>Selecione</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected($editingProduct && $editingProduct->category_id === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" class="form-control" value="{{ $editingProduct->name ?? '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="3" required>{{ $editingProduct->description ?? '' }}</textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Preço (R$)</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control" value="{{ $editingProduct->price ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" name="weight" step="0.01" min="0" class="form-control" value="{{ $editingProduct->weight ?? '' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Foto do produto</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">Até 5MB.</small>
                            </div>
                        </div>
                        @php($currentImage = $editingProduct->image_path ?? null)
                        @if($currentImage)
                            <div class="mt-3">
                                <p class="small text-muted mb-1">Pré-visualização atual:</p>
                                <img src="{{ asset($currentImage) }}" alt="Imagem do produto" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                            </div>
                        @endif
                        <hr>
                        <p class="fw-semibold mb-2">Itens adicionais</p>
                        <div id="addon-wrapper">
                            @php($presetAddons = $editingAddons->isNotEmpty() ? $editingAddons : collect([null]))
                            @foreach($presetAddons as $addon)
                                <div class="addon-row d-flex flex-column flex-md-row gap-2 mb-2">
                                    <input type="text" name="addon_names[]" class="form-control" placeholder="Nome" value="{{ $addon->name ?? '' }}">
                                    <input type="number" name="addon_prices[]" step="0.01" min="0" class="form-control" placeholder="Preço" value="{{ $addon->price ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-outline-secondary btn-sm mb-3" type="button" id="add-addon">Adicionar adicional</button>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success" type="submit">{{ $editingProduct ? 'Atualizar produto' : 'Salvar produto' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Configurações</div>
                <div class="card-body">
                    <p class="small text-muted">Informe o número de WhatsApp que receberá os pedidos (inclua DDD e, se necessário, código do país).</p>
                    <form method="POST" action="{{ route('products.settings.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp_number" value="{{ $settings->whatsapp_number ?? '' }}" class="form-control" placeholder="Ex.: 5599999999999">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="catalog_enabled" name="catalog_enabled" value="1" {{ ($settings->catalog_enabled ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="catalog_enabled">Exibir aba de catálogo no perfil público</label>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Salvar configurações</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Produtos cadastrados</span>
            <span class="text-muted small">Clique em "Editar" para carregar o produto no formulário.</span>
        </div>
        <div class="card-body">
            @if($products->isEmpty())
                <p class="text-muted mb-0">Cadastre os primeiros produtos para liberar o catálogo.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Peso</th>
                            <th>Adicionais</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    <div class="small text-muted mb-1">{{ $product->description }}</div>
                                    @php($productImage = $product->image_path ?? null)
                                    @if($productImage)
                                        <img src="{{ asset($productImage) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-height: 90px;">
                                    @endif
                                </td>
                                <td>{{ optional($categories->firstWhere('id', $product->category_id))->name }}</td>
                                <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                <td>{{ number_format($product->weight, 2, ',', '.') }} kg</td>
                                <td>
                                    @php($productAddons = $addons->get($product->id) ?? collect())
                                    @if($productAddons->isEmpty())
                                        <span class="text-muted">—</span>
                                    @else
                                        <ul class="mb-0 ps-3">
                                            @foreach($productAddons as $addon)
                                                <li>{{ $addon->name }} (R$ {{ number_format($addon->price, 2, ',', '.') }})</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-outline-primary btn-sm" href="{{ route('products.index', ['edit' => $product->id]) }}">Editar</a>
                                        <form method="POST" action="{{ route('products.destroy', $product->id) }}" onsubmit="return confirm('Remover este produto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-link text-danger">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('sidebar-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.getElementById('add-addon');
        const wrapper = document.getElementById('addon-wrapper');

        if (!button || !wrapper) {
            return;
        }

        button.addEventListener('click', function () {
            const row = document.createElement('div');
            row.classList.add('addon-row', 'd-flex', 'flex-column', 'flex-md-row', 'gap-2', 'mb-2');
            row.innerHTML = `
                <input type="text" name="addon_names[]" class="form-control" placeholder="Nome">
                <input type="number" name="addon_prices[]" step="0.01" min="0" class="form-control" placeholder="Preço">
            `;
            wrapper.appendChild(row);
        });
    });
</script>
@endpush
@endsection