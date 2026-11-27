<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Cart;
use App\Services\CoreOrderSender;
use App\Services\NovaPoshta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Оформление заказа: форма → заказ у себя → сразу в dropshop-core.
 * Нет связи с dropshop-core — покупатель всё равно видит «Дякуємо», заказ дошлёт orders:resend.
 */
class CheckoutController extends Controller
{
    public function show(Cart $cart)
    {
        $lines = $cart->lines();

        if ($redirect = $this->redirectIfNotReady($lines)) {
            return $redirect;
        }

        return view('checkout', ['lines' => $lines, 'total' => Cart::total($lines)]);
    }

    public function store(Request $request, Cart $cart, CoreOrderSender $sender, NovaPoshta $np)
    {
        $request->merge(['phone' => self::normalizePhone($request->string('phone', strict: true)->toString())]);

        $data = $request->validate([
            // минимум 2 слова из букв; внутри слова можно дефис и апостроф (Мар’яна, Кос-Анатольська)
            'customer_name' => ['required', 'string', 'max:255', "regex:/^\pL+(?:['’ʼ-]\pL+)*(?:\s+\pL+(?:['’ʼ-]\pL+)*)+$/u"],
            'phone' => ['required', 'string', 'regex:/^\+380\d{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'city_ref' => ['nullable', 'uuid'],
            'delivery_type' => ['required', Rule::in(array_keys(Order::DELIVERY_TYPES))],
            'delivery_point' => ['required', 'string', 'max:255'],
            'delivery_point_ref' => ['nullable', 'uuid'],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'customer_name.regex' => 'Вкажіть прізвище та ім’я літерами, наприклад: Петренко Іван.',
            'phone.regex' => 'Вкажіть український номер, наприклад: +380 50 123 45 67.',
        ], [
            'customer_name' => 'прізвище та ім’я',
            'phone' => 'телефон',
            'city' => 'місто',
            'delivery_type' => 'спосіб доставки',
            'delivery_point' => 'відділення або поштомат',
            'payment_method' => 'спосіб оплати',
            'comment' => 'коментар',
        ]);

        $data = [...collect($data)->except('city_ref')->all(), ...$this->deliveryPoint($data, $np)];

        $lines = $cart->lines();

        if ($redirect = $this->redirectIfNotReady($lines)) {
            return $redirect;
        }

        $order = DB::transaction(function () use ($data, $lines): Order {
            $order = Order::create([...$data, 'total' => Cart::total($lines)]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line->product->id,
                    'core_id' => $line->product->core_id,
                    'name' => $line->product->name,
                    'price' => $line->product->price,
                    'quantity' => $line->quantity,
                ]);
            }

            return $order;
        });

        if ($sender->send($order) === CoreOrderSender::UNAVAILABLE) {
            // dropshop-core ничего не создал — убираем и у себя, покупатель поправит корзину
            $order->delete();

            return redirect()->route('cart')->with('error', 'Поки ви оформлювали замовлення, деякі товари закінчилися. Видаліть їх з кошика й оформіть замовлення ще раз.');
        }

        $cart->clear();
        session(['last_order_id' => $order->id]);

        return redirect()->route('checkout.done');
    }

    /** «Дякуємо»: показываем только последний заказ этого посетителя (по сессии, не по id в адресе). */
    public function done()
    {
        $order = Order::with('items')->find(session('last_order_id'));

        if (! $order) {
            return redirect()->route('home');
        }

        return view('checkout-done', [
            'order' => $order,
            'paymentDetails' => config('shop.payment_details'),
        ]);
    }

    /** «050 123 45 67», «+38 (050) 123-45-67» → «+380501234567»; не похоже на украинский номер — оставляем как есть (не пройдёт проверку). */
    private static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = '38'.$digits;
        }

        return strlen($digits) === 12 && str_starts_with($digits, '380') ? '+'.$digits : $phone;
    }

    /**
     * Пункт выдачи только из подсказок: сверяем Ref с API (тот город, тот тип), название берём из API.
     * API не отвечает — принимаем текст как есть, чтобы не терять заказ.
     */
    private function deliveryPoint(array $data, NovaPoshta $np): array
    {
        $point = filled($data['delivery_point_ref'] ?? null) ? $np->point($data['delivery_point_ref']) : null;

        if ($point && $point['city_ref'] === ($data['city_ref'] ?? null) && $point['type'] === $data['delivery_type']) {
            return ['delivery_point' => $point['name'], 'delivery_point_ref' => $point['ref']];
        }

        if ($np->available()) {
            throw ValidationException::withMessages(['delivery_point' => 'Оберіть місто, а потім відділення або поштомат зі списку підказок.']);
        }

        return ['delivery_point' => $data['delivery_point'], 'delivery_point_ref' => null];
    }

    private function redirectIfNotReady($lines)
    {
        if ($lines->isEmpty()) {
            return redirect()->route('cart');
        }

        if ($lines->contains(fn ($line) => ! $line->product->is_available)) {
            return redirect()->route('cart')->with('error', 'Видаліть з кошика товари, яких немає в наявності.');
        }

        return null;
    }
}
