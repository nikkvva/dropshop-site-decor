<?php

namespace App\Http\Controllers;

use App\Models\Product;

/** Главная: новинки и один товар крупно — только товары в наличии. */
class HomeController extends Controller
{
    public function __invoke()
    {
        // последние добавленные
        $arrivals = Product::available()->latest('id')->limit(8)->get();

        return view('home', [
            'arrivals' => $arrivals,
            // самый дорогой — как «флагман» витрины; не повторяем товар из «Новинок»
            'featured' => Product::available()->whereNotIn('id', $arrivals->pluck('id'))->orderByDesc('price')->first(),
        ]);
    }
}
