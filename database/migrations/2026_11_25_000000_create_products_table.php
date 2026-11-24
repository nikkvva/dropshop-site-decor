<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Локальная копия товаров из dropshop-core (команда products:sync).
 * Сайт эти данные только читает: главный источник — dropshop-core.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('core_id')->unique(); // id товара в dropshop-core — по нему шлём заказ
            $table->string('slug')->unique();                // адрес страницы: /product/{slug}
            $table->string('name');
            $table->longText('description')->nullable();     // HTML от поставщика
            $table->string('vendor')->nullable();            // бренд
            $table->string('vendor_code')->nullable();       // артикул
            $table->decimal('price', 10, 2);
            $table->decimal('old_price', 10, 2)->nullable(); // зачёркнутая цена
            $table->string('currency', 3)->default('UAH');
            $table->boolean('is_available')->default(false);
            $table->json('params')->nullable();              // характеристики: {"Цвет": "Белый", ...}
            $table->json('pictures')->nullable();            // ссылки на фото поставщика
            $table->string('group_id')->nullable();          // варианты одного товара (цвет, размер)
            $table->unsignedBigInteger('core_category_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
