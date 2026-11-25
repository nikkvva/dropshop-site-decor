<?php

namespace App\Http\Controllers;

use App\Models\Product;

/** Страница товара. Товар не в наличии тоже открывается — с плашкой, а не 404. */
class ProductController extends Controller
{
    public function __invoke(Product $product)
    {
        // другие цвета/варианты того же товара (поставщик связывает их через group_id)
        $variants = $product->group_id
            ? Product::available()->where('group_id', $product->group_id)->whereKeyNot($product->id)->get()
            : collect();

        return view('product', [
            'product' => $product,
            'variants' => $variants,
            'related' => Product::available()
                ->where('core_category_id', $product->core_category_id)
                ->whereKeyNot($product->id)
                ->whereNotIn('id', $variants->pluck('id'))
                ->inRandomOrder()
                ->limit(4)
                ->get(),
        ]);
    }
}
