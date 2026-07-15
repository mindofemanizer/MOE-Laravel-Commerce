<?php

namespace Moe\Commerce\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Moe\Commerce\Models\Cart;
use Moe\Commerce\Models\CartItem;
use Moe\Commerce\Models\Product;

class CartService
{
    private const SESSION_KEY = 'commerce_guest_cart';

    /**
     * Get cart items (authenticated or guest).
     */
    public function get(): array
    {
        if (Auth::check()) {
            return $this->getFromDb();
        }

        return $this->getFromSession();
    }

    /**
     * Add item to cart.
     */
    public function add(int $productId, int $quantity = 1): array
    {
        $product = Product::with('inventory')->findOrFail($productId);

        if (! $product->isAvailable()) {
            throw new \Exception('Produk tidak tersedia');
        }

        if ($product->getStock() < $quantity) {
            throw new \Exception('Stok tidak mencukupi');
        }

        if (Auth::check()) {
            return $this->addToDb($productId, $quantity);
        }

        return $this->addToSession($productId, $quantity);
    }

    /**
     * Update item quantity.
     */
    public function update(int $productId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        $product = Product::with('inventory')->findOrFail($productId);
        $stock = $product->getStock();

        if ($stock < $quantity) {
            throw new \Exception('Stok tidak mencukupi');
        }

        if (Auth::check()) {
            return $this->updateDb($productId, $quantity);
        }

        return $this->updateSession($productId, $quantity);
    }

    /**
     * Remove item from cart.
     */
    public function remove(int $productId): array
    {
        if (Auth::check()) {
            return $this->removeFromDb($productId);
        }

        return $this->removeFromSession($productId);
    }

    /**
     * Clear entire cart.
     */
    public function clear(): void
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cart->items()->delete();
                $cart->delete();
            }
        } else {
            Session::forget(self::SESSION_KEY);
        }
    }

    /**
     * Get cart count.
     */
    public function getCount(): int
    {
        return collect($this->get())->sum('quantity');
    }

    /**
     * Get cart subtotal.
     */
    public function getSubtotal(): float
    {
        return (float) collect($this->get())->sum('subtotal');
    }

    /**
     * Get items grouped by store (for multi-store checkout).
     */
    public function getGroupedByStore(): array
    {
        $items = $this->get();

        return collect($items)
            ->groupBy('store_id')
            ->map(function ($storeItems, $storeId) {
                return [
                    'store_id' => $storeId,
                    'store_name' => $storeItems->first()->store_name ?? 'Toko',
                    'store_slug' => $storeItems->first()->store_slug ?? 'toko',
                    'items' => $storeItems,
                    'subtotal' => $storeItems->sum('subtotal'),
                ];
            })
            ->values()
            ->toArray();
    }

    // ── Database Methods ──────────────────────────────────────

    protected function getFromDb(): array
    {
        $cart = Cart::with('items.product.images')->where('user_id', Auth::id())->first();

        if (! $cart) {
            return [];
        }

        return $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'price' => $item->product->retail_price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'image' => $item->product->primary_image,
                'stock' => $item->product->getStock(),
                'store_id' => $item->product->store_id,
                'store_name' => $item->product->store?->name,
                'store_slug' => $item->product->store?->slug,
            ];
        })->toArray();
    }

    protected function addToDb(int $productId, int $quantity): array
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $product = Product::find($productId);

        $existing = $cart->items()->where('product_id', $productId)->first();

        if ($existing) {
            $newQty = $existing->quantity + $quantity;
            $existing->update([
                'quantity' => min($newQty, $product->getStock()),
                'subtotal' => $product->retail_price * min($newQty, $product->getStock()),
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $product->retail_price,
                'subtotal' => $product->retail_price * $quantity,
            ]);
        }

        return $this->getFromDb();
    }

    protected function updateDb(int $productId, int $quantity): array
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $product = Product::find($productId);
            $item->update([
                'quantity' => min($quantity, $product->getStock()),
                'subtotal' => $product->retail_price * min($quantity, $product->getStock()),
            ]);
        }

        return $this->getFromDb();
    }

    protected function removeFromDb(int $productId): array
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        $cart->items()->where('product_id', $productId)->delete();

        return $this->getFromDb();
    }

    // ── Session Methods ───────────────────────────────────────

    protected function getFromSession(): array
    {
        $guestCart = Session::get(self::SESSION_KEY, []);

        return collect($guestCart)->map(function ($item) {
            $product = Product::find($item['product_id']);
            if (! $product) {
                return null;
            }

            return [
                'product_id' => $item['product_id'],
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->retail_price,
                'quantity' => $item['quantity'],
                'subtotal' => $product->retail_price * $item['quantity'],
                'image' => $product->primary_image,
                'stock' => $product->getStock(),
                'store_id' => $product->store_id,
                'store_name' => $product->store?->name,
                'store_slug' => $product->store?->slug,
            ];
        })->filter()->values()->toArray();
    }

    protected function addToSession(int $productId, int $quantity): array
    {
        $guestCart = Session::get(self::SESSION_KEY, []);
        $product = Product::find($productId);

        $existing = collect($guestCart)->search(fn ($item) => $item['product_id'] === $productId);

        if ($existing !== false) {
            $guestCart[$existing]['quantity'] += $quantity;
        } else {
            $guestCart[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $product->retail_price,
            ];
        }

        Session::put(self::SESSION_KEY, $guestCart);

        return $this->getFromSession();
    }

    protected function updateSession(int $productId, int $quantity): array
    {
        $guestCart = Session::get(self::SESSION_KEY, []);

        $key = collect($guestCart)->search(fn ($item) => $item['product_id'] === $productId);

        if ($key !== false) {
            $guestCart[$key]['quantity'] = $quantity;
        }

        Session::put(self::SESSION_KEY, $guestCart);

        return $this->getFromSession();
    }

    protected function removeFromSession(int $productId): array
    {
        $guestCart = Session::get(self::SESSION_KEY, []);
        $guestCart = array_values(array_filter($guestCart, fn ($item) => $item['product_id'] !== $productId));
        Session::put(self::SESSION_KEY, $guestCart);

        return $this->getFromSession();
    }
}
