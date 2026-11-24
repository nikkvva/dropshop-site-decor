<?php

namespace App\Http\Controllers;

use App\Models\Product;

/** Главная: акции, новинки и один товар крупно — только товары в наличии. */
class HomeController extends Controller
{
    public function __invoke()
    {
        // последние добавленные
        $arrivals = Product::available()->latest('id')->limit(8)->get();

        return view('home', [
            // самые большие скидки
            'deals' => Product::available()
                ->whereColumn('old_price', '>', 'price')
                ->orderByRaw('(old_price - price) / old_price desc')
                ->limit(6)
                ->get(),
            'arrivals' => $arrivals,
            // самый дорогой — как «флагман» витрины; не повторяем товар из «Новинок»
            'featured' => Product::available()->whereNotIn('id', $arrivals->pluck('id'))->orderByDesc('price')->first(),
        ]);
    }
}
