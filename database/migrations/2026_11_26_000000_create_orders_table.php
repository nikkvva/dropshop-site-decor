<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Заказы покупателей. Сначала сохраняются здесь, потом уходят в dropshop-core (POST /api/orders).
 * core_number пустой — заказ ещё не дошёл до dropshop-core (повтор — команда orders:resend).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone', 32);
            $table->string('email')->nullable();
            $table->string('city');
            $table->string('delivery_type', 16);
            $table->string('delivery_point');
            $table->string('payment_method', 16);
            $table->text('comment')->nullable();
            $table->decimal('total', 10, 2);
            $table->string('core_number', 32)->nullable(); // номер заказа в dropshop-core
            $table->timestamp('sent_at')->nullable();
            $table->text('send_error')->nullable(); // последняя ошибка отправки
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('core_id'); // id товара в dropshop-core — уходит в заказ
            $table->string('name'); // название и цена на момент заказа
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
