# Changelog

All notable changes to this package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-08-11

### Added
- Single Store and Multi Store mode support via `config/commerce.php`
- Models: `Store`, `Product`, `Category`, `SubCategory`, `Cart`, `CartItem`, `Order`, `OrderItem`, `Review`, `Wishlist`, `ProductImage`, `ProductVariant`, `Payment`, `Invoice`, `Refund`
- `CartService` with add, update, remove, and group-by-store methods
- `CheckoutService` with stock validation and order processing
- `OrderService` with cancel, status update, refund, and history
- `InvoiceService` for invoice generation
- Contracts: `SellableInterface`, `HasStoreInterface`, `CheckoutInterface`, `OrderInterface`
- Events: `OrderPlaced`, `OrderStatusChanged`, `RefundRequested`
- `CommerceServiceProvider` with config and migration publishing
- Configurable fee rate per store via `config/commerce.php`
