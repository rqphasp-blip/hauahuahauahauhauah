<div class="catalog-container" data-order-url="{{ route('products.orders.store', ['username' => $user->name]) }}" data-customer-url="{{ route('products.customer.lookup', ['username' => $user->name]) }}">
   <style>
    /* CONTAINER GERAL */
    .catalog-container {
        max-width: 960px;
        margin: 0 auto;
        padding: 1.5rem 1rem 3rem;
        background: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Roboto", "Helvetica Neue", sans-serif;
    }

    .catalog-header {
        display: none; /* somei o cabeçalho “Catálogo de Produtos” para ficar mais iFood */
    }

    /* TÍTULO DA CATEGORIA (ex: Lanches Artesanais) */
    .category-section {
        margin-bottom: 1.5rem;
    }

    .category-heading {
        margin-bottom: 0.75rem;
    }

    .category-heading .badge-category {
        background: transparent;
        padding: 0;
        font-size: 20px;
        font-weight: 700;
        color: #111111;
    }

    .category-heading .text-muted {
        font-size: 13px;
        color: #8b8b8b;
    }

    /* LINHA DO PRODUTO */
    .product-card {
        padding: 18px 0;
        border-bottom: 1px solid #ececec;
        font-weight: 400;
    }

    .product-card:last-child {
        border-bottom: none;
    }

    .product-card-body {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
    }

    .product-card-info {
        flex: 1;
        min-width: 0;
    }

    /* FORÇAR que strong/b negrito não estraguem o visual */
    .product-card b,
    .product-card strong {
        font-weight: 400;
    }

    /* NOME DO PRODUTO */
    .product-title {
        font-size: 17px;
        font-weight: 600;
        color: #111111;
        margin: 0 0 4px;
    }

    /* DESCRIÇÃO */
    .product-description {
        font-size: 14px;
        line-height: 1.25;
        color: #6b6b6b;
        margin: 0 0 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-description.expanded {
        -webkit-line-clamp: unset;
        max-height: none;
    }

    .description-toggle {
        display: none !important; /* iFood não mostra "ver mais" */
    }

    /* PESO / SERVE ATÉ… (se quiser usar) */
    .product-weight {
        display: none; /* escondido para ficar igual ao print enviado */
    }

    /* PREÇO */
    .product-price-row {
        margin-top: 8px;
        font-size: 16px;
        font-weight: 600;
        color: #111111;
        gap: 0;
        align-items: center;
    }

    /* THUMB DO PRODUTO (mesma proporção do iFood) */
    .product-thumb-wrapper {
        width: 96px;
        height: 96px;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
        background: #f3f3f3;
    }

    .product-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* REMOVER CONTROLES DENTRO DO CARD (inputs/botão) DO VISUAL PRINCIPAL */
    .product-actions,
    .addon-list {
        display: none !important;
    }

    /* TEXTO PADRÃO */
    .text-muted {
        color: #7c7c7c !important;
    }

    /* CARRINHO / PRÉ-CHECKOUT */
    .cart-panel {
        background: #ffffff;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        margin-top: 1.5rem;
    }

    .cart-panel h2 {
        font-size: 18px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 0.5rem;
    }

    /* PRÉ-CHECKOUT escondido por padrão
       (só aparece depois do clique no botão PEDIR AGORA) */
    .precheckout-panel.mobile-collapsed {
        display: none;
    }

    /* BOTÃO "PEDIR AGORA" sempre visível em todas as larguras */
    .mobile-precheckout-toggle {
        display: block !important;
        margin-top: 1rem;
    }

    @media (max-width: 767.98px) {
        .catalog-container {
            padding: 1rem 0.75rem 2.5rem;
        }

        .product-thumb-wrapper {
            width: 82px;
            height: 82px;
            border-radius: 12px;
        }
    }
</style>


    <div class="catalog-grid">
        <div>
            @forelse($categories as $category)
                @php($categoryProducts = $products->where('category_id', $category->id))
                @if($categoryProducts->isEmpty())
                    @continue
                @endif
                <section class="category-section">
                    <div class="category-heading">
                        <span class="badge-category">{{ $category->name }}</span>
                        @if($category->description)
                            <span class="text-muted">{{ $category->description }}</span>
                        @endif
                    </div>
                    @foreach($categoryProducts as $product)
                        @php($productAddons = $addons->get($product->id) ?? collect())
                        <article class="product-card" data-product="{{ json_encode(['id' => $product->id, 'name' => $product->name, 'price' => $product->price, 'weight' => $product->weight], JSON_UNESCAPED_UNICODE) }}">
                            <div class="product-card-body">
                                <div class="product-card-info">
                                    <div class="product-title">{{ $product->name }}</div>
                                    <p class="product-description">{{ $product->description }}</p>
                                    <button class="description-toggle" type="button" hidden>... ver mais</button>
                                    <div class="product-price-row">
                                        <span><small>aR$ {{ number_format($product->price, 2, ',', '.') }}</small></span>
                                        <span class="product-weight">{{ number_format($product->weight, 2, ',', '.') }} kg</span>
                                    </div>
                                    @if($productAddons->isNotEmpty())
                                        <p class="fw-semibold mb-2">Adicionais</p>
                                        <ul class="addon-list">
                                            @foreach($productAddons as $addon)
                                                <li>
                                                    <label class="form-check">
                                                        <input type="checkbox" class="form-check-input addon-checkbox" data-name="{{ $addon->name }}" data-price="{{ $addon->price }}">
                                                        <span class="form-check-label">{{ $addon->name }} (R$ {{ number_format($addon->price, 2, ',', '.') }})</span>
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="d-flex gap-2 flex-wrap product-actions">
                                        <input type="number" min="1" value="1" class="form-control quantity-input" style="max-width: 110px;">
                                        <button class="btn btn-primary add-to-cart" type="button">+</button>
                                    </div>
                                </div>
                                @if($product->image_path)
                                    <div class="product-thumb-wrapper">
                                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="product-thumb">
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </section>
            @empty
                <div class="alert alert-info">Nenhuma categoria disponível no momento.</div>
            @endforelse
        </div>

        <div>
            <div class="cart-panel mb-3">
                <h2>Carrinho</h2>
                <div id="cart-list" class="mb-3 text-muted">Seu carrinho está vazio.</div>
                <div class="d-flex justify-content-between fw-semibold">
                    <span>Total estimado:</span>
                    <span id="cart-total">R$ 0,00</span>
                </div>
            </div>

            <button class="btn btn-success w-100 mb-3 mobile-precheckout-toggle" type="button" id="open-precheckout">
    PEDIR AGORA
</button>


            <div class="cart-panel precheckout-panel mobile-collapsed">
                <h2>Pré-checkout</h2>
                <p class="small text-muted">Preencha seus dados. O telefone deve ser informado primeiro para carregarmos suas informações salvas.</p>
                <form id="checkout-form" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="field-label">Telefone</label>
                        <input type="tel" class="form-control" id="checkout-phone" placeholder="(00) 00000-0000" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Nome completo</label>
                        <input type="text" class="form-control" id="checkout-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Endereço completo</label>
                        <input type="text" class="form-control" id="checkout-address" list="address-suggestions" placeholder="Rua, número e bairro" required>
                        <small class="address-helper">Digite o nome da rua para receber sugestões automáticas.</small>
                        <datalist id="address-suggestions"></datalist>
                    </div>
                    <div class="mb-3">
                        <label class="field-label">Observações</label>
                        <textarea class="form-control" id="checkout-note" rows="2" placeholder="Ex.: Sem cebola"></textarea>
                    </div>
                    <div class="precheckout-box">
                        <p class="fw-semibold mb-1">Resumo do pré-checkout</p>
                        <ul class="mb-2" id="precheckout-summary">
                            <li class="text-muted">Adicione itens para visualizar o resumo.</li>
                        </ul>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Peso total</span>
                            <span id="precheckout-weight">0 kg</span>
                        </div>
                    </div>
                    <button class="btn btn-success w-100 mt-3" id="finalize-order" type="button" disabled>Finalizar pelo WhatsApp</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const cart = [];
    const currencyFormatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const weightFormatter = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const container = document.querySelector('.catalog-container');
    const orderUrl = container.dataset.orderUrl;
    const customerUrl = container.dataset.customerUrl;
    const cartList = document.getElementById('cart-list');
    const cartTotal = document.getElementById('cart-total');
    const precheckoutSummary = document.getElementById('precheckout-summary');
    const precheckoutWeight = document.getElementById('precheckout-weight');
    const finalizeButton = document.getElementById('finalize-order');
    const phoneInput = document.getElementById('checkout-phone');
    const nameInput = document.getElementById('checkout-name');
    const addressInput = document.getElementById('checkout-address');
    const noteInput = document.getElementById('checkout-note');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const addressList = document.getElementById('address-suggestions');
    const precheckoutPanel = document.querySelector('.precheckout-panel');
    const precheckoutToggle = document.getElementById('open-precheckout');
    let precheckoutExpanded = false;

    function initDescriptionToggles() {
        document.querySelectorAll('.product-description').forEach(description => {
            if (description.dataset.toggleBound === '1') {
                return;
            }

            const toggle = description.nextElementSibling;
            if (!toggle || !toggle.classList.contains('description-toggle')) {
                return;
            }

            description.dataset.toggleBound = '1';
            toggle.textContent = '... ver mais';

            const measure = () => {
                if (description.classList.contains('expanded')) {
                    toggle.hidden = false;
                    return;
                }

                toggle.hidden = description.scrollHeight <= description.clientHeight + 1;
            };

            requestAnimationFrame(measure);

            toggle.addEventListener('click', () => {
                description.classList.toggle('expanded');
                const expanded = description.classList.contains('expanded');
                toggle.textContent = expanded ? 'ver menos' : '... ver mais';
                measure();
            });

            window.addEventListener('resize', () => {
                if (!description.classList.contains('expanded')) {
                    requestAnimationFrame(measure);
                }
            });
        });
    }

    function renderCart() {
        if (!cart.length) {
            cartList.innerHTML = '<p class="text-muted mb-0">Seu carrinho está vazio.</p>';
            cartTotal.textContent = currencyFormatter.format(0);
            precheckoutSummary.innerHTML = '<li class="text-muted">Adicione itens para visualizar o resumo.</li>';
            precheckoutWeight.textContent = '0 kg';
            finalizeButton.disabled = true;
            return;
        }

        let html = '';
        let total = 0;
        let totalWeight = 0;

        cart.forEach((item, index) => {
            const addons = item.addons.map(addon => `<div class="small text-muted">+ ${addon.name} (${currencyFormatter.format(addon.price)})</div>`).join('');
            const itemTotal = calculateItemTotal(item);
            const itemWeight = item.weight * item.quantity;
            total += itemTotal;
            totalWeight += itemWeight;

            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${item.quantity}x ${item.name}</strong>
                            ${addons}
                        </div>
                        <div class="text-end">
                            ${currencyFormatter.format(itemTotal)}
                            <div class="small text-muted">${weightFormatter.format(itemWeight)} kg</div>
                        </div>
                    </div>
                    <button class="btn btn-link text-danger p-0 mt-1" data-index="${index}" type="button">remover</button>
                </div>
            `;
        });

        cartList.innerHTML = html;
        cartTotal.textContent = currencyFormatter.format(total);
        precheckoutSummary.innerHTML = cart.map(item => `<li>${item.quantity}x ${item.name}</li>`).join('');
        precheckoutWeight.textContent = `${weightFormatter.format(totalWeight)} kg`;
        finalizeButton.disabled = !phoneInput.value.trim() || !cart.length;
    }

    function calculateItemTotal(item) {
        let total = item.price * item.quantity;
        item.addons.forEach(addon => {
            total += addon.price * item.quantity;
        });
        return total;
    }

    function addToCart(productElement) {
        const productData = JSON.parse(productElement.dataset.product);
        const quantity = parseInt(productElement.querySelector('.quantity-input').value, 10) || 1;
        const addons = Array.from(productElement.querySelectorAll('.addon-checkbox:checked')).map(addon => ({
            name: addon.dataset.name,
            price: parseFloat(addon.dataset.price),
        }));

        cart.push({
            product_id: productData.id,
            name: productData.name,
            price: parseFloat(productData.price),
            weight: parseFloat(productData.weight),
            quantity,
            addons,
        });

        productElement.querySelector('.quantity-input').value = 1;
        productElement.querySelectorAll('.addon-checkbox').forEach(input => (input.checked = false));
        renderCart();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function handleCustomerLookup() {
        const phone = phoneInput.value.trim();
        if (!phone) return;

        fetch(`${customerUrl}?phone=${encodeURIComponent(phone)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                if (!data || !data.found) {
                    return;
                }
                nameInput.value = data.name || '';
                addressInput.value = data.address || '';
                finalizeButton.disabled = !cart.length || !phoneInput.value.trim();
            })
            .catch(() => {});
    }

    function submitOrder() {
        if (!cart.length) return;

        const payload = {
            phone: phoneInput.value.trim(),
            customer_name: nameInput.value.trim(),
            address: addressInput.value.trim(),
            note: noteInput.value.trim(),
            cart,
        };

        finalizeButton.disabled = true;

        fetch(orderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        })
            .then(response => response.json())
            .then(data => {
                finalizeButton.disabled = false;

                if (!data.success) {
                    alert(data.message || 'Não foi possível finalizar o pedido.');
                    return;
                }

                window.open(data.whatsapp_url, '_blank');
            })
            .catch(() => {
                finalizeButton.disabled = false;
                alert('Não foi possível finalizar o pedido.');
            });
    }

    function applyPrecheckoutMode() {
        if (!precheckoutPanel) return;
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            if (!precheckoutExpanded) {
                precheckoutPanel.classList.add('mobile-collapsed');
            }
            precheckoutToggle?.classList.remove('d-none');
        } else {
            precheckoutExpanded = true;
            precheckoutPanel.classList.remove('mobile-collapsed');
            precheckoutToggle?.classList.add('d-none');
        }
    }

    function attachEvents() {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => addToCart(button.closest('.product-card')));
        });

        initDescriptionToggles();

        cartList.addEventListener('click', event => {
            const target = event.target;
            if (target.matches('button[data-index]')) {
                removeCartItem(parseInt(target.dataset.index, 10));
            }
        });

        phoneInput.addEventListener('blur', handleCustomerLookup);
        phoneInput.addEventListener('input', () => {
            finalizeButton.disabled = !phoneInput.value.trim() || !cart.length;
        });

        addressInput.addEventListener('input', function () {
            const value = this.value.trim();
            addressList.innerHTML = '';

            if (value.length < 3) return;

            for (let i = 1; i <= 3; i++) {
                const option = document.createElement('option');
                option.value = `${value} - sugestão ${i}`;
                addressList.appendChild(option);
            }
        });

        finalizeButton.addEventListener('click', submitOrder);

        precheckoutToggle?.addEventListener('click', () => {
            precheckoutExpanded = true;
            precheckoutPanel?.classList.remove('mobile-collapsed');
            precheckoutToggle.classList.add('d-none');
            precheckoutPanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        applyPrecheckoutMode();
        window.addEventListener('resize', applyPrecheckoutMode);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachEvents);
    } else {
        attachEvents();
    }
</script>