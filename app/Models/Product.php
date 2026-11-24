<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Товар сайта — копия товара из dropshop-core, обновляется командой products:sync. */
class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'is_available' => 'boolean',
            'params' => 'array',
            'pictures' => 'array',
        ];
    }

    /** Только товары, которые можно купить: Product::available()->... */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /** Первое фото поставщика или null. */
    public function image(): ?string
    {
        return $this->pictures[0] ?? null;
    }

    /** Скидка в процентах, если старая цена выше текущей, иначе null. */
    public function discount(): ?int
    {
        if (! $this->old_price || $this->old_price <= $this->price) {
            return null;
        }

        return (int) round(($this->old_price - $this->price) / $this->old_price * 100);
    }

    /** Цена для показа: 1 250 ₴ */
    public static function money($value): string
    {
        return number_format((float) $value, 0, ',', ' ').' ₴';
    }
}
