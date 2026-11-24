<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Синк товаров из dropshop-core: API подменяем, настоящий dropshop-core не нужен. */
class SyncProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.core' => ['url' => 'http://core.test', 'key' => 'secret', 'category_id' => 5]]);
    }

    private function item(int $id, array $override = []): array
    {
        return array_merge([
            'id' => $id, 'vendor_code' => 'A'.$id, 'vendor' => 'Remax',
            'name' => 'Павербанк', 'name_ua' => 'Павербанк Remax', 'description' => 'ru', 'description_ua' => 'ua',
            'price' => '500.00', 'old_price' => null, 'currency' => 'UAH', 'is_available' => true,
            'params' => ['Цвет' => 'Белый'], 'pictures' => ['https://img.test/1.jpg'],
            'group_id' => null, 'category_id' => 5,
        ], $override);
    }

    private function page(array $items, int $page, int $lastPage): array
    {
        return ['data' => $items, 'meta' => ['page' => $page, 'last_page' => $lastPage, 'total' => 0]];
    }

    public function test_it_fetches_all_pages_with_api_key(): void
    {
        Http::fake([
            'core.test/api/categories/5/products?page=1' => Http::response($this->page([$this->item(1)], 1, 2)),
            'core.test/api/categories/5/products?page=2' => Http::response($this->page([$this->item(2)], 2, 2)),
        ]);

        $this->artisan('products:sync')->assertSuccessful();

        $this->assertSame(2, Product::count());
        $product = Product::firstWhere('core_id', 1);
        $this->assertSame('Павербанк Remax', $product->name);   // берём украинское название
        $this->assertSame('ua', $product->description);
        $this->assertSame('paverbank-remax-1', $product->slug);
        $this->assertSame(['Цвет' => 'Белый'], $product->params);
        Http::assertSent(fn ($request) => $request->hasHeader('X-Api-Key', 'secret'));
    }

    public function test_it_updates_existing_and_hides_missing_products(): void
    {
        Http::fake(['core.test/*' => Http::sequence()
            ->push($this->page([$this->item(1), $this->item(2)], 1, 1))
            ->push($this->page([$this->item(1, ['price' => '450.00', 'is_available' => false])], 1, 1)),
        ]);

        $this->artisan('products:sync')->assertSuccessful();
        $this->artisan('products:sync')->assertSuccessful();

        $this->assertSame(2, Product::count());                  // пропавший товар не удалён
        $this->assertSame('450.00', Product::firstWhere('core_id', 1)->price);
        $this->assertFalse(Product::firstWhere('core_id', 1)->is_available);
        $this->assertFalse(Product::firstWhere('core_id', 2)->is_available); // пропал из категории
    }
}
