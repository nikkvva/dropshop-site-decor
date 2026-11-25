<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Каталог: только товары в наличии, сортировка, страницы; sitemap. */
class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $id, array $override = []): Product
    {
        return Product::create(array_merge([
            'core_id' => $id, 'slug' => 'p-'.$id, 'name' => 'Товар '.$id,
            'price' => 750, 'old_price' => null, 'is_available' => true,
            'pictures' => ['https://img.test/'.$id.'.jpg'],
        ], $override));
    }

    public function test_it_shows_only_available_products_with_links(): void
    {
        $this->product(1, ['name' => 'Павербанк Remax']);
        $this->product(2, ['name' => 'Знятий з продажу', 'is_available' => false]);

        $this->get('/catalog')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Павербанк Remax')
            ->assertSee(route('product', 'p-1'))
            ->assertDontSee('Знятий з продажу');
    }

    public function test_it_sorts_by_price_and_discount(): void
    {
        $this->product(1, ['name' => 'Середній', 'price' => 500]);
        $this->product(2, ['name' => 'Дешевий', 'price' => 100]);
        $this->product(3, ['name' => 'Дорогий', 'price' => 900, 'old_price' => 1000]);   // -10%
        $this->product(4, ['name' => 'Акційний', 'price' => 600, 'old_price' => 1200]);  // -50%

        $this->get('/catalog?sort=cheap')->assertSeeInOrder(['Дешевий', 'Середній', 'Акційний', 'Дорогий']);
        $this->get('/catalog?sort=expensive')->assertSeeInOrder(['Дорогий', 'Акційний', 'Середній', 'Дешевий']);
        $this->get('/catalog?sort=discount')->assertSeeInOrder(['Акційний', 'Дорогий']);
        // неизвестная сортировка — не ошибка, а «новинки»
        $this->get('/catalog?sort=hack')->assertOk()->assertSeeInOrder(['Акційний', 'Дорогий', 'Дешевий']);
    }

    public function test_it_paginates_by_24_and_keeps_sort_out_of_canonical(): void
    {
        foreach (range(1, 25) as $id) {
            $this->product($id);
        }

        $this->get('/catalog?sort=cheap')
            ->assertSee('<link rel="canonical" href="'.route('catalog').'">', false)
            ->assertSee('page=2');
        $this->get('/catalog?page=2')->assertOk()->assertSee('Товар 1</a>', false);
    }

    public function test_sitemap_lists_available_products(): void
    {
        $this->product(1);
        $this->product(2, ['is_available' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('product', 'p-1'), false)
            ->assertDontSee(route('product', 'p-2'), false);
    }
}
