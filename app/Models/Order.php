<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Заказ покупателя. Товары — items(). Отправка в dropshop-core — App\Services\CoreOrderSender. */
class Order extends Model
{
    protected $guarded = [];

    // ключи совпадают с API dropshop-core
    public const DELIVERY_TYPES = [
        'branch' => 'Відділення',
        'postomat' => 'Поштомат',
    ];

    public const PAYMENT_METHODS = [
        'cod' => 'Оплата при отриманні',
        'prepaid' => 'Передоплата на картку',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'sent_at' => 'datetime',
            'unavailable_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Дошёл ли заказ до dropshop-core. */
    public function isSent(): bool
    {
        return $this->core_number !== null;
    }
}
