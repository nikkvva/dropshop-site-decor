<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Корзина: добавить, изменить количество, удалить, счётчик в шапке, товар не в наличии. */
class CartTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $id, array $override = []): Product
    {
        return Product::create(array_merge([
            'core_id' => 100 + $id, 'slug' => 'p-'.$id, 'name' => 'Товар '.$id,
            'price' => 250, 'is_available' => true, 'pictures' => [],
        ], $override));
    }

    public function test_add_update_and_remove(): void
    {
        $a = $this->product(1);
        $b = $this->product(2, ['price' => 100]);

        $this->post(route('cart.add', $a), ['quantity' => 2])->assertRedirect(route('cart'));
        $this->post(route('cart.add', $b));
        $this->post(route('cart.add', $a)); // тот же товар ещё раз — количество растёт

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Товар 1')
            ->assertSee('name="quantity['.$a->id.']" value="3"', false)
            ->assertSee('850 ₴') // 3 × 250 + 100
            ->assertSee('<span class="count-box">4</span>', false)
            ->assertSee(route('checkout'));

        // количество ограничено 1…99
        $this->post('/cart/update', ['quantity' => [$a->id => 500, $b->id => 0]]);
        $this->assertSame([$a->id => 99, $b->id => 1], session('cart'));

        $this->post(route('cart.remove', $a));
        $this->assertSame([$b->id => 1], session('cart'));
    }

    public function test_unavailable_product_cannot_be_added(): void
    {
        $product = $this->product(1, ['is_available' => false]);

        $this->post(route('cart.add', $product))->assertSessionHas('error');
        $this->assertSame([], session('cart', []));
    }

    public function test_product_that_ran_out_blocks_checkout(): void
    {
        $product = $this->product(1);
        $this->post(route('cart.add', $product));
        $product->update(['is_available' => false]); // синк снял с продажи, пока товар лежал в корзине

        $this->get('/cart')
            ->assertSee('Немає в наявності')
            ->assertDontSee(route('checkout'));

        $this->get('/checkout')->assertRedirect(route('cart'));
    }

    public function test_empty_cart(): void
    {
        $this->get('/cart')->assertOk()->assertSee('Кошик порожній')->assertDontSee('count-box', false);
        $this->get('/checkout')->assertRedirect(route('cart'));
    }
}
