<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function test_products_without_photo_are_hidden_from_showcase(): void
    {
        $this->product(1, ['name' => 'Павербанк з фото']);
        $this->product(2, ['name' => 'Новинка без фото', 'pictures' => []]);
        $this->product(3, ['name' => 'Ще без фото', 'pictures' => null]);

        foreach (['/', '/catalog', '/product/p-1'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('без фото');
        }
        $this->get('/sitemap.xml')->assertSee('p-1')->assertDontSee('p-2')->assertDontSee('p-3');

        // по прямой ссылке страница открывается, вместо фото — заглушка
        $this->get('/product/p-2')->assertOk()->assertSee('Новинка без фото')->assertSee('images/no-photo.svg');
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

    public function test_it_sorts_by_price(): void
    {
        $this->product(1, ['name' => 'Середній', 'price' => 500]);
        $this->product(2, ['name' => 'Дешевий', 'price' => 100]);
        $this->product(3, ['name' => 'Дорогий', 'price' => 900, 'old_price' => 1000]);
        $this->product(4, ['name' => 'Акційний', 'price' => 600, 'old_price' => 1200]);

        $this->get('/catalog?sort=cheap')->assertSeeInOrder(['Дешевий', 'Середній', 'Акційний', 'Дорогий']);
        $this->get('/catalog?sort=expensive')->assertSeeInOrder(['Дорогий', 'Акційний', 'Середній', 'Дешевий']);
        // неизвестная сортировка (в т.ч. старая ?sort=discount) — не ошибка, а «новинки»
        $this->get('/catalog?sort=discount')->assertOk()->assertSeeInOrder(['Акційний', 'Дорогий', 'Дешевий']);
        // массив вместо строки (так шлют боты) — тоже не ошибка
        $this->get('/catalog?sort[]=cheap')->assertOk();
    }

    public function test_pagination_shows_window_not_all_pages(): void
    {
        // 42 страницы, открыта 20-я: соседние + края, остальное — «...» (иначе на телефоне шире экрана)
        $html = (new LengthAwarePaginator([], 1000, 24, 20, ['path' => '/catalog']))
            ->onEachSide(1)->links('partials.pagination')->toHtml();

        foreach ([1, 2, 19, 20, 21, 41, 42] as $page) {
            $this->assertStringContainsString('>'.$page.'<', $html);
        }
        $this->assertStringNotContainsString('>10<', $html);
        $this->assertStringContainsString('...', $html);
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
