<div class="catalog-container"
     data-order-url="{{ route('products.orders.store', ['username' => $user->name]) }}"
     data-customer-url="{{ route('products.customer.lookup', ['username' => $user->name]) }}">

    <style>
        /* ================================
           BASE GERAL
        ================================= */
        
#finalize-order {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    padding: 0.55rem 0 !important;
    font-size: 14px !important;
    border-radius: 10px !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}


	.precheckout-panel {
    padding: 0 1rem !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow-x: hidden !important;
}
	
	
.precheckout-panel .form-control,
.precheckout-panel textarea {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}
		
		
		.catalog-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 1.5rem 1rem 3rem;
            background: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Roboto", "Helvetica Neue", sans-serif;
            text-align: left;
        }
		
		

        .catalog-container h1,
        .catalog-container h2,
        .catalog-container h3 {
            font-weight: 400;
            margin: 0;
        }

        .catalog-header {
            margin-bottom: 1.5rem;
        }

        .catalog-header h1 {
            font-size: 18px;
            color: #111111;
            margin-bottom: 4px;
        }

        .catalog-header p {
            font-size: 13px;
            color: #7c7c7c;
        }

        .catalog-grid {
            display: block;
        }

        /* ================================
           CATEGORIAS / TÍTULOS
        ================================= */
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
            font-weight: 400;
            color: #111111;
        }

        .category-heading .text-muted {
            font-size: 13px;
            color: #8b8b8b;
        }

        /* ================================
           PRODUTOS – LISTA
        ================================= */
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
            gap: 14px;
            align-items: flex-start;
        }

        .product-card-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            font-weight: 400;
        }

        .product-card-info b,
        .product-card-info strong {
            font-weight: 400 !important;
        }

        .product-title {
            font-size: 17px;
            font-weight: 400;
            color: #111111;
            margin-bottom: 4px;
        }

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
            display: none !important;
        }

        .product-weight {
            display: none !important;
        }

        .product-price-row {
            margin-top: 6px;
            font-size: 16px;
            font-weight: 400;
            color: #111111;
        }

        .product-thumb-wrapper {
            width: 110px;
            height: 110px;
            border-radius: 18px;
            overflow: hidden;
            flex-shrink: 0;
            background: #f3f4f6;
        }

        .product-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .quantity-input {
            display: none !important; /* escondido, só lógica */
        }

        .product-actions .add-to-cart {
            border-radius: 999px;
            padding: 0.4rem 1.4rem;
            font-size: 14px;
            font-weight: 400;
        }

        /* ================================
           ADICIONAIS (NO PRODUTO)
        ================================= */
        .addon-list {
            list-style: none;
            padding-left: 0;
            margin: 6px 0 0;
        }

        .addon-list li {
            margin-bottom: 4px;
            font-size: 13px;
            color: #666;
        }

        .addon-list .form-check {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .addon-list .form-check-label {
            font-weight: 400;
        }

        /* ================================
           CARRINHO – FINAL DA PÁGINA
        ================================= */
        .cart-section-bottom {
            margin-top: 2.5rem;
        }

        .cart-panel {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.4rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        .cart-panel h2 {
            font-size: 18px;
            color: #111;
            margin-bottom: 0.75rem;
        }

        .cart-item {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-name {
            font-size: 15px;
            color: #111;
            margin-bottom: 2px;
            font-weight: 400;
        }

        .cart-item-addons {
            font-size: 13px;
            color: #777;
        }

        .cart-item-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 8px;
        }

        .cart-qty-control {
            display: inline-flex;
            align-items: center;
            gap: 16px;
        }

        .cart-qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid #d0d0d0;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #b0b0b0;
            cursor: pointer;
            padding: 0;
        }

        .cart-qty-value {
            min-width: 20px;
            text-align: center;
            font-size: 16px;
            color: #111;
            font-weight: 400;
        }

        .cart-price-pill {
            flex: 1;
            text-align: center;
            padding: 8px 16px;
            border-radius: 999px;
            background: #ff0040;
            color: #ffffff;
            font-size: 15px;
            font-weight: 400;
            white-space: nowrap;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            font-size: 15px;
            color: #111;
        }

        .addon-remove-cart-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #ff0040;
            border: none;
            color: #fff;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
        }

        .addon-remove-cart-btn:hover {
            background: #cc0033;
        }

        /* ================================
           PRÉ-CHECKOUT
        ================================= */
        .precheckout-panel.mobile-collapsed {
            display: none;
        }

        .precheckout-panel {
            font-size: 13px;
        }

        .precheckout-panel .form-control,
.precheckout-panel textarea {
    width: 100% !important;
    box-sizing: border-box !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
.precheckout-panel {
    padding-left: 0 !important;
    padding-right: 0 !important;
}

.cart-panel {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
}


        .precheckout-box {
            background: #f7fdf8;
            border: 1px solid #d1f5dc;
            border-radius: .75rem;
            padding: 0.9rem;
            margin-top: 0.75rem;
        }

        .precheckout-title {
            font-size: 13px;
            color: #222;
            margin: 0 0 4px;
        }

        .field-label {
            display: none; /* só placeholder */
        }

        .address-helper {
            font-size: .75rem;
            color: #6b7280;
            margin-top: .25rem;
        }

        .mobile-precheckout-toggle {
            display: block !important;
            margin: 1rem 0;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            font-weight: 400;
        }

        .text-muted {
            color: #7c7c7c !important;
            font-weight: 400 !important;
        }

        /* resumo delicado */
        #precheckout-summary {
            padding-left: 1rem;
            margin: 0 0 4px;
        }

        #precheckout-summary li {
            margin-bottom: 4px;
            font-size: 12px;
            color: #444;
        }

        .summary-item-note {
            font-size: 11px;
            color: #777;
            margin-top: 1px;
        }

        @media (max-width: 767.98px) {
            .catalog-container {
                padding: 1rem 0.75rem 2.5rem;
            }

            .product-thumb-wrapper {
                width: 96px;
                height: 96px;
                border-radius: 14px;
            }
        }
    </style>

    <div class="catalog-header">
        <h1>{{ $user->name }}</h1>
        <p class="text-muted">Escolha seus produtos e finalize o pedido.</p>
    </div>

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
                        <article class="product-card"
                                 data-product="{{ json_encode(['id' => $product->id, 'name' => $product->name, 'price' => $product->price, 'weight' => $product->weight], JSON_UNESCAPED_UNICODE) }}">
                            <div class="product-card-body">
                                <div class="product-card-info">
                                    <div class="product-title">{{ $product->name }}</div>
                                    <p class="product-description">{{ $product->description }}</p>
                                    <button class="description-toggle" type="button" hidden>... ver mais</button>

                                    <div class="product-price-row">
                                        R$ {{ number_format($product->price, 2, ',', '.') }}
                                    </div>

                                    @if($productAddons->isNotEmpty())
                                        <p class="mb-1">Adicionais</p>
                                        <ul class="addon-list">
                                            @foreach($productAddons as $addon)
                                                <li>
                                                    <label class="form-check">
                                                        <input type="checkbox"
                                                               class="form-check-input addon-checkbox"
                                                               data-name="{{ $addon->name }}"
                                                               data-price="{{ $addon->price }}">
                                                        <span class="form-check-label">
                                                            {{ $addon->name }} (R$ {{ number_format($addon->price, 2, ',', '.') }})
                                                        </span>
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <div class="d-flex gap-2 flex-wrap product-actions">
                                        <input type="number" min="1" value="1"
                                               class="form-control quantity-input">
                                        <button class="btn btn-outline-secondary add-to-cart" type="button">
                                            Adicionar
                                        </button>
                                    </div>
                                </div>

                                @if($product->image_path)
                                    <div class="product-thumb-wrapper">
                                        <img src="{{ asset($product->image_path) }}"
                                             alt="{{ $product->name }}"
                                             class="product-thumb">
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
    </div>

    <!-- CARRINHO E PRÉ-CHECKOUT NO FINAL DA PÁGINA -->
    <div class="cart-section-bottom">
        <div class="cart-panel mb-3">
            <h2>Carrinho</h2>

            <div id="cart-list" class="mb-3 text-muted">
                Seu carrinho está vazio.
            </div>

            <div class="cart-total-row">
                <span>Total estimado:</span>
                <span id="cart-total">R$ 0,00</span>
            </div>
        </div>

        <button class="btn btn-success w-100 mobile-precheckout-toggle"
                type="button" id="open-precheckout">
            PEDIR AGORA
        </button>

        <div class="cart-panel precheckout-panel mobile-collapsed">
            <h2>Pré-checkout</h2>
            <p class="small text-muted">
                Preencha seus dados para finalizar o pedido pelo WhatsApp.
            </p>
            <form id="checkout-form" class="needs-validation" novalidate>
                <div class="mb-2">
                    <label class="field-label">Telefone</label>
                    <input type="tel" class="form-control" id="checkout-phone"
                           placeholder="Telefone (00) 00000-0000" required autofocus>
                </div>
                <div class="mb-2">
                    <label class="field-label">Nome completo</label>
                    <input type="text" class="form-control" id="checkout-name"
                           placeholder="Nome completo" required>
                </div>
                <div class="mb-2">
                    <label class="field-label">Endereço completo</label>
                    <input type="text" class="form-control" id="checkout-address"
                           list="address-suggestions"
                           placeholder="Endereço completo (rua, número, bairro)" required>
                    <small class="address-helper">Digite o nome da rua para receber sugestões
                        automáticas.</small>
                    <datalist id="address-suggestions"></datalist>
                </div>
                <div class="mb-2">
                    <label class="field-label">Observações</label>
                    <textarea class="form-control" id="checkout-note" rows="2"
                              placeholder="Observações sobre o pedido / item"></textarea>
                </div>
                <div class="precheckout-box">
                    <p class="precheckout-title">Resumo do pré-checkout</p>
                    <ul id="precheckout-summary">
                        <li class="text-muted">Adicione itens para visualizar o resumo.</li>
                    </ul>
                </div>
                <button class="btn btn-success w-100 mt-3" id="finalize-order"
                        type="button" disabled>Finalizar pelo WhatsApp
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const cart = [];
    const currencyFormatter = new Intl.NumberFormat('pt-BR', {style: 'currency', currency: 'BRL'});
    const weightFormatter = new Intl.NumberFormat('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const container = document.querySelector('.catalog-container');
    const orderUrl = container.dataset.orderUrl;
    const customerUrl = container.dataset.customerUrl;
    const cartList = document.getElementById('cart-list');
    const cartTotal = document.getElementById('cart-total');
    const precheckoutSummary = document.getElementById('precheckout-summary');
    const finalizeButton = document.getElementById('finalize-order');
    const phoneInput = document.getElementById('checkout-phone');
    const nameInput = document.getElementById('checkout-name');
    const addressInput = document.getElementById('checkout-address');
    const noteInput = document.getElementById('checkout-note');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const addressList = document.getElementById('address-suggestions');
    const precheckoutPanel = document.querySelector('.precheckout-panel');
    const precheckoutToggle = document.getElementById('open-precheckout');

    function initDescriptionToggles() {
        document.querySelectorAll('.product-description').forEach(description => {
            if (description.dataset.toggleBound === '1') return;

            const toggle = description.nextElementSibling;
            if (!toggle || !toggle.classList.contains('description-toggle')) return;

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

    function calculateItemTotal(item) {
        let total = item.price * item.quantity;
        item.addons.forEach(addon => {
            total += addon.price * item.quantity;
        });
        return total;
    }

    function addonsAreEqual(a, b) {
        if (a.length !== b.length) return false;
        const key = (x) => `${x.name}|${x.price}`;
        const sa = a.map(key).sort();
        const sb = b.map(key).sort();
        return sa.every((v, i) => v === sb[i]);
    }

    function addToCart(productElement) {
        const productData = JSON.parse(productElement.dataset.product);
        const quantityInput = productElement.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput.value, 10) || 1;

        const addons = Array.from(productElement.querySelectorAll('.addon-checkbox:checked')).map(addon => ({
            name: addon.dataset.name,
            price: parseFloat(addon.dataset.price)
        }));

        const existingIndex = cart.findIndex(item =>
            item.product_id === productData.id && addonsAreEqual(item.addons, addons)
        );

        if (existingIndex !== -1) {
            cart[existingIndex].quantity += quantity;
        } else {
            cart.push({
                product_id: productData.id,
                name: productData.name,
                price: parseFloat(productData.price),
                weight: parseFloat(productData.weight),
                quantity,
                addons
            });
        }

        quantityInput.value = 1;
        productElement.querySelectorAll('.addon-checkbox').forEach(input => (input.checked = false));
        renderCart();
    }

function renderCart() {
    if (!cart.length) {
        cartList.innerHTML = '<p class="text-muted mb-0">Seu carrinho está vazio.</p>';
        cartTotal.textContent = currencyFormatter.format(0);
        precheckoutSummary.innerHTML = '<li class="text-muted">Adicione itens para visualizar o resumo.</li>';
        finalizeButton.disabled = true;
        return;
    }

    let html = '';
    let total = 0;
    let totalWeight = 0;

    cart.forEach((item, index) => {
        const addonsHtml = item.addons.map((addon, addonIndex) => `
            <div class="cart-item-addons" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                <span>+ ${addon.name} (${currencyFormatter.format(addon.price)})</span>
                <button class="addon-remove-cart-btn"
                    data-item="${index}"
                    data-addon="${addonIndex}">
                    🗑
                </button>
            </div>
        `).join('');

        const itemTotal = calculateItemTotal(item);
        const itemWeight = item.weight * item.quantity;
        total += itemTotal;
        totalWeight += itemWeight;

        html += `
            <div class="cart-item">
                <div class="cart-item-name">${item.name}</div>
                ${addonsHtml}
                <div class="cart-item-footer">
                    <div class="cart-qty-control">
                        <button class="cart-qty-btn cart-qty-minus" data-index="${index}">-</button>
                        <span class="cart-qty-value">${item.quantity}</span>
                        <button class="cart-qty-btn cart-qty-plus" data-index="${index}">+</button>
                    </div>
                    <div class="cart-price-pill">
                        ${currencyFormatter.format(itemTotal)}
                    </div>
                </div>
            </div>
        `;
    });

    cartList.innerHTML = html;
    cartTotal.textContent = currencyFormatter.format(total);

    // 🔽 AQUI: resumo do pré-checkout com total do pedido
    let summaryHtml = cart.map(item => {
        const addonsNote = item.addons.length
            ? `<div class="summary-item-note">+ ${item.addons.map(a => a.name).join(', ')}</div>`
            : '';
        return `<li><div>${item.quantity}x ${item.name}</div>${addonsNote}</li>`;
    }).join('');

    summaryHtml += `
        <li style="margin-top:6px; font-weight:600;">
            Total do pedido: ${currencyFormatter.format(total)}
        </li>
    `;

    precheckoutSummary.innerHTML = summaryHtml;

    finalizeButton.disabled = !phoneInput.value.trim() || !cart.length;
}


    function handleCustomerLookup() {
        const phone = phoneInput.value.trim();
        if (!phone) return;

        fetch(`${customerUrl}?phone=${encodeURIComponent(phone)}`, {
            headers: {'X-Requested-With': 'XMLHttpRequest'},
        })
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                if (!data || !data.found) return;

                nameInput.value = data.name || '';
                addressInput.value = data.address || '';
                finalizeButton.disabled = !cart.length || !phoneInput.value.trim();
            })
            .catch(() => {
            });
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

    function attachEvents() {
        /* adicionar ao carrinho */
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => addToCart(button.closest('.product-card')));
        });

        initDescriptionToggles();

        /* clique dentro da área do carrinho */
        cartList.addEventListener('click', event => {
            const target = event.target;

            /* remover adicional do item no carrinho */
            if (target.closest('.addon-remove-cart-btn')) {
                const btn = target.closest('.addon-remove-cart-btn');
                const itemIndex = parseInt(btn.dataset.item, 10);
                const addonIndex = parseInt(btn.dataset.addon, 10);

                if (!Number.isNaN(itemIndex) && !Number.isNaN(addonIndex) && cart[itemIndex]) {
                    cart[itemIndex].addons.splice(addonIndex, 1);
                    renderCart();
                }
                return;
            }

            /* quantidade + */
            if (target.classList.contains('cart-qty-plus')) {
                const index = parseInt(target.dataset.index, 10);
                if (!Number.isNaN(index) && cart[index]) {
                    cart[index].quantity += 1;
                    renderCart();
                }
                return;
            }

            /* quantidade - */
            if (target.classList.contains('cart-qty-minus')) {
                const index = parseInt(target.dataset.index, 10);
                if (!Number.isNaN(index) && cart[index]) {
                    cart[index].quantity -= 1;
                    if (cart[index].quantity <= 0) {
                        cart.splice(index, 1);
                    }
                    renderCart();
                }
                return;
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
            precheckoutPanel?.classList.remove('mobile-collapsed');
            precheckoutToggle.classList.add('d-none');
            precheckoutPanel?.scrollIntoView({behavior: 'smooth', block: 'start'});
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachEvents);
    } else {
        attachEvents();
    }
</script>
