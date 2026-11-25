<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    /**
     * Описание поставщика без опасного HTML: только простые теги и без атрибутов
     * (убирает <script>, onclick=..., style=...). Выводить через {!! !!}.
     */
    public function safeDescription(): string
    {
        $html = strip_tags((string) $this->description, '<p><br><ul><ol><li><b><strong><i><em><h2><h3><h4><table><tr><td><th>');

        return preg_replace('/<(\w+)\b[^>]*>/', '<$1>', $html);
    }

    /** Короткий текст для meta description: описание без тегов, до 160 символов. */
    public function metaDescription(): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $this->description))));

        return Str::limit($text ?: $this->name, 157);
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
