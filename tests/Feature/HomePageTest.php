<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Главная показывает только товары в наличии и без зачёркнутой цены поставщика. */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $id, array $override = []): Product
    {
        return Product::create(array_merge([
            'core_id' => $id, 'slug' => 'p-'.$id, 'name' => 'Товар '.$id,
            'price' => 750, 'old_price' => 1000, 'is_available' => true,
            'pictures' => ['https://img.test/'.$id.'.jpg'],
        ], $override));
    }

    public function test_it_shows_available_products_without_old_price(): void
    {
        $this->product(1, ['name' => 'Павербанк Remax']);
        $this->product(2, ['name' => 'Знятий з продажу', 'is_available' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Павербанк Remax')
            ->assertSee('750 ₴')
            // старая цена поставщика есть у всех товаров — это не настоящая скидка, не показываем
            ->assertDontSee('1 000 ₴')
            ->assertDontSee('-25%')
            ->assertDontSee('Знятий з продажу');
    }

    public function test_it_works_without_products(): void
    {
        $this->get('/')->assertOk();
    }
}
