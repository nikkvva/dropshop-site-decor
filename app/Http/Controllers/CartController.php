<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\Request;

/** Страница корзины и кнопки «До кошика», «Оновити», «Видалити». */
class CartController extends Controller
{
    public function show(Cart $cart)
    {
        $lines = $cart->lines();

        return view('cart', [
            'lines' => $lines,
            'total' => Cart::total($lines),
            'hasUnavailable' => $lines->contains(fn ($line) => ! $line->product->is_available),
        ]);
    }

    public function add(Request $request, Product $product, Cart $cart)
    {
        if (! $product->is_available) {
            return back()->with('error', 'Цього товару зараз немає в наявності.');
        }

        $cart->add($product, (int) $request->input('quantity', 1));

        return redirect()->route('cart')->with('status', 'Товар додано до кошика.');
    }

    public function update(Request $request, Cart $cart)
    {
        $cart->update((array) $request->input('quantity', []));

        return redirect()->route('cart');
    }

    public function remove(Product $product, Cart $cart)
    {
        $cart->remove($product->id);

        return redirect()->route('cart');
    }
}
