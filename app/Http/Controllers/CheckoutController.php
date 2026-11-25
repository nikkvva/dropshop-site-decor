<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Cart;
use App\Services\CoreOrderSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function store(Request $request, Cart $cart, CoreOrderSender $sender)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{10,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'delivery_type' => ['required', Rule::in(array_keys(Order::DELIVERY_TYPES))],
            'delivery_point' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'customer_name' => 'прізвище та ім’я',
            'phone' => 'телефон',
            'city' => 'місто',
            'delivery_type' => 'спосіб доставки',
            'delivery_point' => 'відділення або поштомат',
            'payment_method' => 'спосіб оплати',
            'comment' => 'коментар',
        ]);

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
