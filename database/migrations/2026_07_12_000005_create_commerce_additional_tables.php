<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('commerce_orders')->cascadeOnDelete();
            $table->string('method', 50);
            $table->decimal('amount', 15, 2);
            $table->string('status', 50)->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('commerce_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('commerce_orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('commerce_order_items')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamp('refunded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_refunds');
        Schema::dropIfExists('commerce_payments');
    }
};
