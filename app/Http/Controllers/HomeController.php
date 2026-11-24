<?php

namespace App\Http\Controllers;

use App\Models\Product;

/** Главная: акции, новинки и один товар крупно — только товары в наличии. */
class HomeController extends Controller
{
    public function __invoke()
    {
        return view('home', [
            // самые большие скидки
            'deals' => Product::available()
                ->whereColumn('old_price', '>', 'price')
                ->orderByRaw('(old_price - price) / old_price desc')
                ->limit(6)
                ->get(),
            // последние добавленные
            'arrivals' => Product::available()->latest('id')->limit(8)->get(),
            // самый дорогой — как «флагман» витрины
            'featured' => Product::available()->orderByDesc('price')->first(),
        ]);
    }
}
