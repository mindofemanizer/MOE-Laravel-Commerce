# MOE-Laravel-Commerce

Commerce module for MOE ecosystem — Store, Product, Cart, Order (Single & Multi Store).

## Installation

```bash
composer require moe/laravel-commerce
php artisan vendor:publish --provider="Moe\Commerce\CommerceServiceProvider" --tag="commerce-config"
php artisan vendor:publish --provider="Moe\Commerce\CommerceServiceProvider" --tag="commerce-migrations"
php artisan migrate
```

## Mode

### Single Store
```php
// config/commerce.php
'mode' => 'single_store',
```
- Tanpa model Store
- Produk langsung dijual
- Checkout langsung

### Multi Store
```php
// config/commerce.php
'mode' => 'multi_store',
```
- Produk milik Store
- Checkout split per toko
- Fee per toko

## What's Included

### Models

| Model | Table | Description |
|-------|-------|-------------|
| `Store` | `commerce_stores` | Toko penjual (multi store) |
| `Product` | `commerce_products` | Produk |
| `Category` | `commerce_categories` | Kategori produk |
| `SubCategory` | `commerce_sub_categories` | Sub kategori |
| `Cart` | `commerce_carts` | Keranjang |
| `CartItem` | `commerce_cart_items` | Item keranjang |
| `Order` | `commerce_orders` | Pesanan |
| `OrderItem` | `commerce_order_items` | Item pesanan |
| `Review` | `commerce_reviews` | Ulasan |
| `Wishlist` | `commerce_wishlists` | Daftar keinginan |
| `ProductImage` | `commerce_product_images` | Gambar produk |
| `ProductVariant` | `commerce_product_variants` | Varian produk |

### Services

| Service | Description |
|---------|-------------|
| `CartService` | Add, update, remove, group by store |
| `CheckoutService` | Process checkout, validate stock, create orders |
| `OrderService` | Cancel, status update, refund, history |

### Contracts

| Contract | Description |
|----------|-------------|
| `SellableInterface` | Interface untuk produk yang bisa dijual |
| `HasStoreInterface` | Interface untuk model yang punya toko |
| `CheckoutInterface` | Interface untuk checkout |
| `OrderInterface` | Interface untuk order |

## Usage

### Cart

```php
use Moe\Commerce\Services\CartService;

$cartService = app(CartService::class);

// Add to cart
$cartService->add($productId, 2);

// Get cart
$items = $cartService->get();

// Group by store (multi-store checkout)
$grouped = $cartService->getGroupedByStore();
```

### Checkout

```php
use Moe\Commerce\Services\CheckoutService;

$checkoutService = app(CheckoutService::class);

// Validate stock
$isValid = $checkoutService->validateStock($items);

// Process checkout
$orders = $checkoutService->process($items, $address, $shipping, $payment);
```

### Order

```php
use Moe\Commerce\Services\OrderService;

$orderService = app(OrderService::class);

// Get order
$order = $orderService->getByNumber('ORD-20260712-ABC123');

// Cancel order
$orderService->cancel($order, 'Changed my mind');

// Update status
$orderService->updateStatus($order, 'processing');
```

## Config

```php
// config/commerce.php
return [
    'mode' => 'multi_store', // single_store | multi_store
    'tables' => [
        'stores' => 'commerce_stores',
        'products' => 'commerce_products',
        // ...
    ],
    'default_fee_rate' => '10',
];
```

## Requirements

- PHP ^8.2
- Laravel ^12.0|^13.0
- `moe/laravel-core`
- `moe/laravel-inventory`
- `moe/laravel-shipping`

## License

MIT
