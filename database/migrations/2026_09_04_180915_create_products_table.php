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
        Schema::create('products', function (Blueprint $table) {
        $table->uuid('id')->primary(); // Использование UUID в качестве PK
        $table->string('title');
        $table->string('slug')->unique(); // ЧПУ для SEO
        $table->text('description')->nullable();
        $table->unsignedInteger('price_cents'); // Цена в копейках (например, 1000 = 10.00 руб)
        $table->unsignedInteger('stock')->default(0); // Количество на складе
        $table->string('status')->default('draft'); // Статусы: draft, published, archived
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
