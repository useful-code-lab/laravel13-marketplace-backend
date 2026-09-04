<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(\Domain\Orders\Models\Order::class, 'order_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\Domain\Products\Models\Product::class, 'product_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price_cents'); // Фиксируем цену на момент покупки
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
