<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/** Каталог: товары в наличии, сортировка, по 24 на страницу. */
class CatalogController extends Controller
{
    /** ?sort=... → подпись в выпадающем списке */
    public const SORTS = [
        'new' => 'Новинки',
        'cheap' => 'Спочатку дешевші',
        'expensive' => 'Спочатку дорожчі',
        'discount' => 'Найбільша знижка',
    ];

    public function __invoke(Request $request)
    {
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'new';

        $query = Product::available();

        match ($sort) {
            'cheap' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            // товары без скидки — в конце; * 1.0 — чтобы SQLite в тестах не делил нацело
            'discount' => $query->orderByRaw('case when old_price > price then (old_price - price) * 1.0 / old_price else 0 end desc'),
            default => $query->latest('id'),
        };

        return view('catalog', [
            'products' => $query->paginate(24)->withQueryString(),
            'sort' => $sort,
        ]);
    }
}
