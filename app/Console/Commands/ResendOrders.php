<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\CoreOrderSender;
use Illuminate\Console\Command;

/**
 * Досылает в dropshop-core заказы, которые не ушли сразу (не было связи).
 * Запускается каждые 10 минут (routes/console.php). Старше 2 дней не трогаем —
 * такие разбирать вручную (кнопка в админке). Дублей не будет: dropshop-core узнаёт заказ по external_id.
 * «Нет в наличии» (unavailable_at) тоже не трогаем: повтор не поможет, нужно звонить покупателю.
 */
class ResendOrders extends Command
{
    protected $signature = 'orders:resend';

    protected $description = 'Дослать в dropshop-core неотправленные заказы';

    public function handle(CoreOrderSender $sender): int
    {
        $orders = Order::with('items')
            ->whereNull('core_number')
            ->whereNull('unavailable_at')
            ->where('created_at', '>=', now()->subDays(2))
            ->get();

        foreach ($orders as $order) {
            $result = $sender->send($order);
            $this->line("Заказ {$order->id}: {$result}".($order->send_error ? " ({$order->send_error})" : ''));
        }

        return self::SUCCESS;
    }
}
