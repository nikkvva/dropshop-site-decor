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
    ];

    public function __invoke(Request $request)
    {
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'new';

        $query = Product::available()->withPhoto();

        match ($sort) {
            'cheap' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            default => $query->latest('id'),
        };

        return view('catalog', [
            'products' => $query->paginate(24)->withQueryString(),
            'sort' => $sort,
        ]);
    }
}
