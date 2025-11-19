<?php

namespace App\Providers\plugins\products;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['publicCatalog', 'customerLookup', 'storeOrder']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $categories = DB::table('user_product_categories')
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $products = DB::table('user_products')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $addons = $products->isEmpty()
            ? collect()
            : DB::table('user_product_addons')
                ->whereIn('product_id', $products->pluck('id')->all())
                ->orderBy('name')
                ->get()
                ->groupBy('product_id');

        $settings = DB::table('user_product_settings')
            ->where('user_id', $user->id)
            ->first();

        $editingProductId = (int) $request->query('edit');
        $editingProduct = null;
        $editingAddons = collect();

        if ($editingProductId) {
            $editingProduct = DB::table('user_products')
                ->where('user_id', $user->id)
                ->where('id', $editingProductId)
                ->first();

            if ($editingProduct) {
                $editingAddons = DB::table('user_product_addons')
                    ->where('product_id', $editingProduct->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view($this->resolveView('index'), [
            'categories' => $categories,
            'products' => $products,
            'addons' => $addons,
            'settings' => $settings,
            'publicUrl' => route('products.catalog', ['username' => $user->name]),
            'editingProduct' => $editingProduct,
            'editingAddons' => $editingAddons,
        ]);
    }

    public function categories()
    {
        $user = Auth::user();

        $categories = DB::table('user_product_categories')
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return view($this->resolveView('categories'), [
            'categories' => $categories,
            'publicUrl' => route('products.catalog', ['username' => $user->name]),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        DB::table('user_product_categories')->insert([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('products.categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function updateCategory(int $id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $category = DB::table('user_product_categories')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $category) {
            return redirect()->route('products.categories.index')->with('error', 'Categoria não encontrada.');
        }

        DB::table('user_product_categories')
            ->where('id', $category->id)
            ->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => now(),
            ]);

        return redirect()->route('products.categories.index')->with('success', 'Categoria atualizada.');
    }

    public function storeProduct(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => [
                'required',
                Rule::exists('user_product_categories', 'id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
            ],
            'description' => 'required|string|max:500',
            'price' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:5120',
            'addon_names' => 'array',
            'addon_names.*' => 'nullable|string|max:150',
            'addon_prices' => 'array',
            'addon_prices.*' => 'nullable|numeric|min:0',
        ]);

        $imagePath = $this->storeImage($request, $user->id);

        $productId = DB::table('user_products')->insertGetId([
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'weight' => $request->weight,
            'image_path' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $addonNames = $request->input('addon_names', []);
        $addonPrices = $request->input('addon_prices', []);

        $this->persistAddons($productId, $addonNames, $addonPrices);

        return redirect()->route('products.index')->with('success', 'Produto criado com sucesso.');
    }

    public function updateProduct(int $id, Request $request)
    {
        $user = Auth::user();

        $product = DB::table('user_products')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $product) {
            return redirect()->route('products.index')->with('error', 'Produto não encontrado.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => [
                'required',
                Rule::exists('user_product_categories', 'id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
            ],
            'description' => 'required|string|max:500',
            'price' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:5120',
            'addon_names' => 'array',
            'addon_names.*' => 'nullable|string|max:150',
            'addon_prices' => 'array',
            'addon_prices.*' => 'nullable|numeric|min:0',
        ]);

        $imagePath = $this->storeImage($request, $user->id, $product->image_path);

        DB::table('user_products')
            ->where('id', $product->id)
            ->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'weight' => $request->weight,
                'image_path' => $imagePath,
                'updated_at' => now(),
            ]);

        DB::table('user_product_addons')->where('product_id', $product->id)->delete();
        $this->persistAddons($product->id, $request->input('addon_names', []), $request->input('addon_prices', []));

        return redirect()->route('products.index', ['edit' => $product->id])->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroyProduct(int $id)
    {
        $user = Auth::user();

        $product = DB::table('user_products')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $product) {
            return redirect()->route('products.index')->with('error', 'Produto não encontrado.');
        }

        DB::table('user_product_addons')->where('product_id', $id)->delete();
        DB::table('user_products')->where('id', $id)->delete();

        if ($product->image_path) {
            $this->deleteIfExists($product->image_path);
        }

        return redirect()->route('products.index')->with('success', 'Produto removido.');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'whatsapp_number' => 'nullable|string|max:20',
            'catalog_enabled' => 'sometimes|boolean',
        ]);

        $existing = DB::table('user_product_settings')->where('user_id', $user->id)->first();

        $payload = [
            'whatsapp_number' => $request->whatsapp_number,
            'catalog_enabled' => $request->boolean('catalog_enabled'),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('user_product_settings')
                ->where('user_id', $user->id)
                ->update($payload);
        } else {
            DB::table('user_product_settings')->insert(array_merge($payload, [
                'user_id' => $user->id,
                'created_at' => now(),
            ]));
        }

        return redirect()->route('products.index')->with('success', 'Configurações atualizadas.');
    }

    public function publicCatalog(string $username, Request $request)
    {
        $user = DB::table('users')->where('name', $username)->first();

        if (! $user) {
            abort(404);
        }

        $categories = DB::table('user_product_categories')
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $products = DB::table('user_products')
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $addons = $products->isEmpty()
            ? collect()
            : DB::table('user_product_addons')
                ->whereIn('product_id', $products->pluck('id')->all())
                ->orderBy('name')
                ->get()
                ->groupBy('product_id');

        $settings = DB::table('user_product_settings')->where('user_id', $user->id)->first();

        if (! $settings || ! $settings->catalog_enabled) {
            abort(404);
        }

        $view = $request->boolean('embed') ? 'catalog-embed' : 'catalog';

        return view($this->resolveView($view), [
            'user' => $user,
            'categories' => $categories,
            'products' => $products,
            'addons' => $addons,
            'settings' => $settings,
        ]);
    }

    public function customerLookup(string $username, Request $request): JsonResponse
    {
        $user = DB::table('users')->where('name', $username)->first();

        if (! $user) {
            abort(404);
        }

        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $record = DB::table('user_product_orders')
            ->where('user_id', $user->id)
            ->where('phone', $request->phone)
            ->orderByDesc('created_at')
            ->first();

        if (! $record) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'name' => $record->customer_name,
            'address' => $record->address,
        ]);
    }

    public function storeOrder(string $username, Request $request): JsonResponse
    {
        $user = DB::table('users')->where('name', $username)->first();

        if (! $user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|integer',
            'cart.*.name' => 'required|string|max:150',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
            'cart.*.weight' => 'required|numeric|min:0',
            'cart.*.addons' => 'array',
            'cart.*.addons.*.name' => 'required|string|max:150',
            'cart.*.addons.*.price' => 'required|numeric|min:0',
        ]);

        $normalizedCart = $this->normalizeCart($data['cart'], $user->id);
        $cartSummary = $this->summarizeCart($normalizedCart);

        $settings = DB::table('user_product_settings')->where('user_id', $user->id)->first();

        if (! $settings || ! $settings->catalog_enabled) {
            return response()->json(['message' => 'O catálogo não está disponível no momento.'], 422);
        }

        if (! $settings->whatsapp_number) {
            return response()->json(['message' => 'O vendedor ainda não configurou o número de WhatsApp.'], 422);
        }

        DB::table('user_product_orders')->insert([
            'user_id' => $user->id,
            'phone' => $data['phone'],
            'customer_name' => $data['customer_name'],
            'address' => $data['address'],
            'note' => $data['note'],
            'cart_snapshot' => json_encode($normalizedCart, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payloadForMessage = $data;
        $payloadForMessage['cart'] = $normalizedCart;
        $message = $this->buildWhatsappMessage($payloadForMessage, $cartSummary);
        $number = preg_replace('/\D+/', '', $settings->whatsapp_number);

        if ($number === '') {
            return response()->json(['message' => 'Número de WhatsApp inválido.'], 422);
        }

        $whatsappUrl = 'https://wa.me/' . $number . '?text=' . urlencode($message);

        return response()->json([
            'success' => true,
            'whatsapp_url' => $whatsappUrl,
            'summary' => $cartSummary,
        ]);
    }

    protected function normalizeCart(array $items, int $userId): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'cart' => 'Nenhum item foi enviado.',
            ]);
        }

        $productIds = array_unique(array_column($items, 'product_id'));

        $products = DB::table('user_products')
            ->whereIn('id', $productIds)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('id');

        $addons = DB::table('user_product_addons')
            ->whereIn('product_id', $productIds)
            ->get()
            ->groupBy('product_id');

        $normalized = [];

        foreach ($items as $item) {
            $product = $products->get($item['product_id'] ?? 0);

            if (! $product) {
                throw ValidationException::withMessages([
                    'cart' => 'Um dos produtos informados não está disponível.',
                ]);
            }

            $quantity = max(1, (int) $item['quantity']);
            $normalizedAddons = [];

            if (! empty($item['addons']) && is_array($item['addons'])) {
                foreach ($item['addons'] as $addon) {
                    $available = ($addons[$product->id] ?? collect())->firstWhere('name', $addon['name']);

                    if (! $available) {
                        throw ValidationException::withMessages([
                            'cart' => 'Um dos adicionais selecionados não está disponível.',
                        ]);
                    }

                    $normalizedAddons[] = [
                        'name' => $available->name,
                        'price' => (float) $available->price,
                    ];
                }
            }

            $normalized[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'weight' => (float) $product->weight,
                'quantity' => $quantity,
                'addons' => $normalizedAddons,
            ];
        }

        return $normalized;
    }

    protected function summarizeCart(array $items): array
    {
        $summaryLines = [];
        $totalValue = 0;
        $totalWeight = 0;

        foreach ($items as $item) {
            $lineValue = (float) $item['price'] * (int) $item['quantity'];
            $lineWeight = (float) $item['weight'] * (int) $item['quantity'];
            $addonsText = [];

            if (! empty($item['addons']) && is_array($item['addons'])) {
                foreach ($item['addons'] as $addon) {
                    $addonPrice = isset($addon['price']) ? (float) $addon['price'] : 0;
                    $lineValue += $addonPrice * (int) $item['quantity'];
                    $addonsText[] = sprintf('+ %s (R$ %s)', $addon['name'], number_format($addonPrice, 2, ',', '.'));
                }
            }

            $totalValue += $lineValue;
            $totalWeight += $lineWeight;

            $summaryLines[] = trim(sprintf(
                '%dx %s - R$ %s %s',
                $item['quantity'],
                $item['name'],
                number_format($lineValue, 2, ',', '.'),
                $addonsText ? '(' . implode(', ', $addonsText) . ')' : ''
            ));
        }

        return [
            'lines' => $summaryLines,
            'total_value' => $totalValue,
            'total_weight' => $totalWeight,
        ];
    }

    protected function buildWhatsappMessage(array $data, array $summary): string
    {
        $lines = [];
        $lines[] = 'Novo pedido via catálogo digital';
        $lines[] = 'Cliente: ' . $data['customer_name'];
        $lines[] = 'Telefone: ' . $data['phone'];
        $lines[] = 'Endereço: ' . $data['address'];
        $lines[] = '';
        $lines[] = 'Itens:';
        $lines = array_merge($lines, $summary['lines']);
        $lines[] = '';
        $lines[] = 'Total: R$ ' . number_format($summary['total_value'], 2, ',', '.');
        $lines[] = 'Peso total: ' . number_format($summary['total_weight'], 2, ',', '.') . ' kg';

        if (! empty($data['note'])) {
            $lines[] = '';
            $lines[] = 'Observações: ' . $data['note'];
        }

        return implode("\n", $lines);
    }

    protected function resolveView(string $view): string
    {
        return view()->exists("products.$view") ? "products.$view" : "products::$view";
    }

    protected function storeImage(Request $request, int $userId, ?string $currentPath = null): ?string
    {
        if (! $request->hasFile('image')) {
            return $currentPath;
        }

        $file = $request->file('image');

        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'image' => 'Não foi possível enviar esta imagem. Tente novamente com outro arquivo.',
            ]);
        }

        $directory = "uploads/products/{$userId}";
        $this->ensureDirectoryExists($directory);

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::uuid()->toString() . '.' . $extension;
        $destination = public_path($directory);

        try {
            $file->move($destination, $filename);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'image' => 'Não foi possível salvar esta imagem. Por favor, tente novamente.',
            ]);
        }

        $path = $directory . '/' . $filename;

        if ($currentPath && $currentPath !== $path) {
            $this->deleteIfExists($currentPath);
        }

        return $path;
    }

    protected function persistAddons(int $productId, array $addonNames, array $addonPrices): void
    {
        foreach ($addonNames as $index => $addonName) {
            $name = trim((string) $addonName);

            if ($name === '') {
                continue;
            }

            $price = isset($addonPrices[$index]) ? (float) $addonPrices[$index] : 0;

            DB::table('user_product_addons')->insert([
                'product_id' => $productId,
                'name' => $name,
                'price' => $price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    protected function ensureDirectoryExists(string $relativePath): void
    {
        $fullPath = public_path($relativePath);

        if (! File::isDirectory($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }
    }

    protected function deleteIfExists(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $fullPath = public_path($relativePath);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}