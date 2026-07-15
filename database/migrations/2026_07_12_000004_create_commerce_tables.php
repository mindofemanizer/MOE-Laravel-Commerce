<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stores
        Schema::create('commerce_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('origin_village_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_official')->default(false);
            $table->decimal('fee_rate', 5, 2)->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_sales')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Categories
        Schema::create('commerce_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('commerce_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Sub Categories
        Schema::create('commerce_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('commerce_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Products
        Schema::create('commerce_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('commerce_stores')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('commerce_categories')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('commerce_sub_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sku', 100)->unique()->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('retail_price', 15, 2);
            $table->decimal('wholesale_price', 15, 2)->nullable();
            $table->integer('minimum_order')->default(1);
            $table->string('unit', 20)->default('pcs');
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('total_sold')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
        });

        // Product Images
        Schema::create('commerce_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('commerce_products')->cascadeOnDelete();
            $table->string('image_url');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Product Variants
        Schema::create('commerce_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('commerce_products')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku', 100)->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Carts
        Schema::create('commerce_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Cart Items
        Schema::create('commerce_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('commerce_carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('commerce_products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // Orders
        Schema::create('commerce_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('commerce_stores')->nullOnDelete();
            $table->string('status', 50)->default('pending');
            $table->string('payment_method', 50);
            $table->string('payment_status', 50)->default('unpaid');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->text('notes')->nullable();
            $table->json('shipping_address_snapshot')->nullable();
            $table->string('shipping_courier')->nullable();
            $table->string('shipping_service')->nullable();
            $table->string('shipping_etd')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['store_id', 'status']);
        });

        // Order Items
        Schema::create('commerce_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('commerce_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('commerce_products')->restrictOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->string('unit', 20)->default('pcs');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->timestamps();
        });

        // Reviews
        Schema::create('commerce_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('commerce_products')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('commerce_orders')->nullOnDelete();
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->text('reply')->nullable();
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Wishlists
        Schema::create('commerce_wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('commerce_products')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_wishlists');
        Schema::dropIfExists('commerce_reviews');
        Schema::dropIfExists('commerce_order_items');
        Schema::dropIfExists('commerce_orders');
        Schema::dropIfExists('commerce_cart_items');
        Schema::dropIfExists('commerce_carts');
        Schema::dropIfExists('commerce_product_variants');
        Schema::dropIfExists('commerce_product_images');
        Schema::dropIfExists('commerce_products');
        Schema::dropIfExists('commerce_sub_categories');
        Schema::dropIfExists('commerce_categories');
        Schema::dropIfExists('commerce_stores');
    }
};
