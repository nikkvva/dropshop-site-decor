<?php

namespace App\Http\Controllers;

use App\Models\Product;

/** sitemap.xml — список страниц для поисковиков. */
class SitemapController extends Controller
{
    public function __invoke()
    {
        return response()
            ->view('sitemap', ['products' => Product::available()->withPhoto()->get(['slug', 'updated_at'])])
            ->header('Content-Type', 'application/xml');
    }
}
