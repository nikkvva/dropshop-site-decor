<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Корзина посетителя. Хранится в сессии как [id товара => количество].
 * Цены и наличие в сессии не храним — каждый раз берём из таблицы products.
 */
class Cart
{
    public const MAX_QUANTITY = 99; // ограничения API заказов в dropshop-core

    public const MAX_LINES = 50;

    public function items(): array
    {
        return session('cart', []);
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $items = $this->items();

        if (! isset($items[$product->id]) && count($items) >= self::MAX_LINES) {
            return;
        }

        $items[$product->id] = $this->clamp(($items[$product->id] ?? 0) + $quantity);
        session(['cart' => $items]);
    }

    /** Новые количества из формы корзины: [id товара => количество]. */
    public function update(array $quantities): void
    {
        $items = $this->items();

        foreach ($quantities as $id => $quantity) {
            if (isset($items[$id])) {
                $items[$id] = $this->clamp((int) $quantity);
            }
        }

        session(['cart' => $items]);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        session(['cart' => $items]);
    }

    public function clear(): void
    {
        session()->forget('cart');
    }

    /** Сколько штук в корзине — для счётчика в шапке. */
    public function count(): int
    {
        return array_sum($this->items());
    }

    /**
     * Строки корзины: товар, количество, сумма строки.
     * @return Collection<int, object{product: Product, quantity: int, sum: float}>
     */
    public function lines(): Collection
    {
        $items = $this->items();

        return Product::findMany(array_keys($items))->map(fn (Product $product) => (object) [
            'product' => $product,
            'quantity' => $items[$product->id],
            'sum' => (float) $product->price * $items[$product->id],
        ]);
    }

    /** Сумма товаров в наличии (то, что можно оформить). */
    public static function total(Collection $lines): float
    {
        return $lines->filter(fn ($line) => $line->product->is_available)->sum('sum');
    }

    private function clamp(int $quantity): int
    {
        return max(1, min(self::MAX_QUANTITY, $quantity));
    }
}
