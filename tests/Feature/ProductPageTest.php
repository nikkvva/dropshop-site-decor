<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Страница товара: данные, плашка «нет в наличии», безопасное описание, варианты, SEO. */
class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $id, array $override = []): Product
    {
        return Product::create(array_merge([
            'core_id' => $id, 'slug' => 'p-'.$id, 'name' => 'Товар '.$id,
            'price' => 750, 'old_price' => 1000, 'is_available' => true,
            'vendor' => 'Remax', 'vendor_code' => 'RM-'.$id,
            'params' => ['Цвет' => 'Белый', 'Модель' => 'X1'],
            'pictures' => ['https://img.test/'.$id.'.jpg'],
            'description' => '<p>Опис товару</p>',
        ], $override));
    }

    public function test_it_shows_product_with_seo(): void
    {
        $this->product(1, ['name' => 'Павербанк Remax 20000']);

        $this->get('/product/p-1')
            ->assertOk()
            ->assertSee('<h1 class="aq-product-details-title mb-10">Павербанк Remax 20000</h1>', false)
            ->assertSee('750 ₴')
            ->assertDontSee('1 000 ₴') // старую цену поставщика не показываем
            // характеристики переведены словарём, незнакомые — как есть
            ->assertSee('<td>Колір</td><td>Білий</td>', false)
            ->assertSee('<td>Модель</td><td>X1</td>', false)
            ->assertDontSee('Белый')
            ->assertSee('В наявності')
            ->assertSee('<meta property="og:image" content="https://img.test/1.jpg">', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('https:\/\/schema.org\/InStock', false);
    }

    public function test_unavailable_product_opens_with_notice(): void
    {
        $this->product(1, ['is_available' => false]);

        $this->get('/product/p-1')
            ->assertOk()
            ->assertSee('Немає в наявності')
            ->assertSee('OutOfStock');
    }

    public function test_unknown_product_is_404(): void
    {
        $this->get('/product/nope')->assertNotFound();
    }

    public function test_description_is_cleaned(): void
    {
        $this->product(1, ['description' => '<p style="color:red" onclick="alert(1)">Текст <strong>жирний</strong></p><script>alert(2)</script><img src=x onerror="alert(3)">']);

        $this->get('/product/p-1')
            ->assertSee('<p>Текст <strong>жирний</strong></p>', false)
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('onclick', false)
            ->assertDontSee('onerror', false);
    }

    public function test_it_links_variants_of_same_group(): void
    {
        $this->product(1, ['group_id' => 'g1']);
        $this->product(2, ['group_id' => 'g1', 'name' => 'Варіант рожевий']);
        $this->product(3, ['group_id' => 'g2', 'name' => 'Чужа група']);

        $this->get('/product/p-1')
            ->assertSee('Інші варіанти')
            ->assertSee(route('product', 'p-2'));

        $this->get('/product/p-3')->assertDontSee('Інші варіанти');
    }
}
