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
        
            // Связь с таблицей orders по UUID строке без вызова самого класса
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            
            // Связь с таблицей products по UUID строке
            $table->foreignUuid('product_id')->constrained('products');
            
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price_cents');
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
