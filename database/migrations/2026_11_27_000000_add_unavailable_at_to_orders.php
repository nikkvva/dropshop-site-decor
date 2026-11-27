<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * unavailable_at — когда dropshop-core ответил «товара нет в наличии» на заказ, который не ушёл сразу.
 * Такой заказ orders:resend больше не шлёт: нужно позвонить покупателю (в админке он выделен).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('unavailable_at')->nullable()->after('send_error');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('unavailable_at');
        });
    }
};
