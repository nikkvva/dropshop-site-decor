<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Отправляет заказ сайта в dropshop-core (POST /api/orders).
 * external_id = id заказа на сайте: повторная отправка того же заказа не создаёт дубль в dropshop-core.
 */
class CoreOrderSender
{
    public const SENT = 'sent';

    public const UNAVAILABLE = 'unavailable'; // какого-то товара уже нет — dropshop-core заказ не принял

    public const FAILED = 'failed'; // нет связи или другая ошибка — повторит orders:resend

    public function send(Order $order): string
    {
        $config = config('services.core');

        try {
            $response = Http::withHeaders(['X-Api-Key' => $config['key']])
                ->acceptJson()
                ->timeout(15)
                ->post(rtrim($config['url'], '/').'/api/orders', $this->payload($order));
        } catch (ConnectionException $e) {
            $order->update(['send_error' => 'Нет связи с dropshop-core: '.$e->getMessage()]);

            return self::FAILED;
        }

        if ($response->successful()) {
            $order->update(['core_number' => $response->json('number'), 'sent_at' => now(), 'send_error' => null]);

            return self::SENT;
        }

        if ($response->status() === 422 && $response->json('unavailable')) {
            $coreIds = $response->json('unavailable');
            // не ждём часового синка: сразу снимаем эти товары с продажи на сайте
            Product::whereIn('core_id', $coreIds)->update(['is_available' => false]);
            $order->update(['send_error' => 'Нет в наличии (id в dropshop-core): '.implode(', ', $coreIds)]);

            return self::UNAVAILABLE;
        }

        $order->update(['send_error' => 'Ответ '.$response->status().': '.Str::limit($response->body(), 500)]);

        return self::FAILED;
    }

    private function payload(Order $order): array
    {
        return [
            'external_id' => (string) $order->id,
            ...$order->only(['customer_name', 'phone', 'email', 'city', 'delivery_type', 'delivery_point', 'payment_method', 'comment']),
            'items' => $order->items->map(fn (OrderItem $item) => [
                'product_id' => $item->core_id,
                'quantity' => $item->quantity,
            ])->all(),
        ];
    }
}
