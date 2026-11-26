<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Оформление заказа: заказ у себя → dropshop-core. dropshop-core и Новая Почта подменены через Http::fake. */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const KYIV = '8d5a980d-391c-11dd-90d9-001a92567626';

    private const BRANCH = '1ec09d88-e1c2-11e3-8c4a-0050568002cf';

    private Product $product;

    private bool $npUp = true; // false — API Новой Почты не отвечает

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.core' => ['url' => 'http://core.test', 'key' => 'secret', 'category_id' => 1]]);

        // Новая Почта знает одно отделение в Києві
        Http::fake(['api.novaposhta.ua/*' => fn (Request $r) => $this->npUp ? Http::response(['success' => true, 'data' => match ($r['calledMethod']) {
            'getWarehouseTypes' => [['Ref' => 'x']],
            'getWarehouses' => ($r['methodProperties']['Ref'] ?? null) === self::BRANCH ? [[
                'Ref' => self::BRANCH, 'CityRef' => self::KYIV, 'CategoryOfWarehouse' => 'Branch',
                'TypeOfWarehouse' => '841339c7-591a-42e2-8233-7a0a00f0ed6f', 'Description' => 'Відділення №5: вул. Хрещатик, 1',
            ]] : [],
        }]) : Http::failedConnection()]);

        $this->product = Product::create([
            'core_id' => 555, 'slug' => 'powerbank', 'name' => 'Павербанк',
            'price' => 600, 'is_available' => true, 'pictures' => [],
        ]);
        $this->post(route('cart.add', $this->product), ['quantity' => 2]);
    }

    private function form(array $override = []): array
    {
        return array_merge([
            'customer_name' => 'Іван Петренко',
            'phone' => '+380 50 123 45 67',
            'city' => 'м. Київ, Київська обл.',
            'city_ref' => self::KYIV,
            'delivery_type' => 'branch',
            'delivery_point' => 'Відділення №5',
            'delivery_point_ref' => self::BRANCH,
            'payment_method' => 'cod',
        ], $override);
    }

    public function test_order_is_saved_and_sent_to_core(): void
    {
        Http::fake(['core.test/api/orders' => Http::response(['number' => 'ORD-261126-00007', 'total' => '1200.00', 'items' => []], 201)]);

        $this->get('/checkout')->assertOk()->assertSee('Павербанк')->assertSee('1 200 ₴');

        $this->post('/checkout', $this->form())->assertRedirect(route('checkout.done'));

        $order = Order::with('items')->sole();
        $this->assertSame('ORD-261126-00007', $order->core_number);
        $this->assertSame('+380501234567', $order->phone);
        $this->assertSame('Відділення №5: вул. Хрещатик, 1', $order->delivery_point); // название — из API
        $this->assertSame(self::BRANCH, $order->delivery_point_ref);
        $this->assertEquals(1200, $order->total);
        $this->assertSame(555, $order->items[0]->core_id);
        $this->assertSame([], session('cart', []));

        Http::assertSent(fn (Request $request) => $request->hasHeader('X-Api-Key', 'secret')
            && $request['external_id'] === (string) $order->id
            && $request['items'] === [['product_id' => 555, 'quantity' => 2]]);

        $this->get(route('checkout.done'))
            ->assertOk()
            ->assertSee('Замовлення № '.$order->id.' прийнято')
            ->assertDontSee('Оплата на картку');
    }

    public function test_prepaid_shows_payment_details(): void
    {
        Http::fake(['core.test/*' => Http::response(['number' => 'ORD-1'], 201)]);
        config(['shop.payment_details' => 'Картка 4441 1111 2222 3333, Іванов І.']);

        $this->post('/checkout', $this->form(['payment_method' => 'prepaid']));

        $this->get(route('checkout.done'))->assertSee('Оплата на картку')->assertSee('4441 1111 2222 3333');
    }

    public function test_core_down_keeps_order_and_resend_delivers_it(): void
    {
        // первый запрос — нет связи, второй (orders:resend) — dropshop-core ответил
        Http::fake(['core.test/*' => Http::sequence()->pushFailedConnection()->push(['number' => 'ORD-2'], 200)]);

        // покупатель всё равно видит «Дякуємо»
        $this->post('/checkout', $this->form())->assertRedirect(route('checkout.done'));
        $order = Order::sole();
        $this->assertNull($order->core_number);
        $this->assertStringContainsString('Нет связи', $order->send_error);

        $this->artisan('orders:resend')->assertSuccessful();

        $this->assertSame('ORD-2', $order->fresh()->core_number);
        $this->assertNull($order->fresh()->send_error);
    }

    public function test_out_of_stock_in_core_returns_to_cart(): void
    {
        Http::fake(['core.test/*' => Http::response(['message' => 'Some products are out of stock.', 'unavailable' => [555]], 422)]);

        $this->post('/checkout', $this->form())
            ->assertRedirect(route('cart'))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertFalse($this->product->fresh()->is_available); // сразу снят с продажи, не ждём синка
        $this->assertNotEmpty(session('cart')); // корзина осталась — покупатель уберёт товар сам
    }

    public function test_invalid_form_shows_errors(): void
    {
        Http::fake();

        $this->post('/checkout', $this->form(['phone' => 'abc', 'customer_name' => '', 'delivery_type' => 'courier']))
            ->assertSessionHasErrors(['phone', 'customer_name', 'delivery_type']);

        $this->assertSame(0, Order::count());
        Http::assertNothingSent();
    }

    public function test_name_and_phone_rules(): void
    {
        Http::fake(['core.test/*' => Http::response(['number' => 'ORD-1'], 201)]);

        foreach (['Іван', 'Іван 123', 'R2 D2'] as $name) {
            $this->post('/checkout', $this->form(['customer_name' => $name]))->assertSessionHasErrors('customer_name');
        }
        foreach (['+7 900 123 45 67', '050 123 45', '+380 50 123 45 678'] as $phone) {
            $this->post('/checkout', $this->form(['phone' => $phone]))->assertSessionHasErrors('phone');
        }
        $this->assertSame(0, Order::count());

        $this->post('/checkout', $this->form(['customer_name' => 'Кос-Анатольська Мар’яна', 'phone' => '(050) 123-45-67']))
            ->assertSessionHasNoErrors();
        $this->assertSame('+380501234567', Order::sole()->phone);
    }

    public function test_point_only_from_list(): void
    {
        Http::fake(['core.test/*' => Http::response(['number' => 'ORD-1'], 201)]);

        // текст без выбора из подсказки, пункт другого города, другого типа
        foreach ([['delivery_point_ref' => ''], ['city_ref' => '00000000-0000-0000-0000-000000000000'], ['delivery_type' => 'postomat']] as $override) {
            $this->post('/checkout', $this->form($override))->assertSessionHasErrors('delivery_point');
        }

        $this->assertSame(0, Order::count());
        Http::assertSent(fn (Request $r) => str_contains($r->body(), '"methodProperties":{}')); // с [] API отвечает ошибкой
    }

    public function test_np_down_accepts_point_as_text(): void
    {
        Http::fake(['core.test/*' => Http::response(['number' => 'ORD-1'], 201)]);
        $this->npUp = false;

        $this->post('/checkout', $this->form(['city_ref' => '', 'delivery_point_ref' => '']))->assertSessionHasNoErrors();

        $order = Order::sole();
        $this->assertSame('Відділення №5', $order->delivery_point);
        $this->assertNull($order->delivery_point_ref);
    }

    public function test_done_page_without_order_redirects_home(): void
    {
        $this->get(route('checkout.done'))->assertRedirect(route('home'));
    }

    public function test_admin_sees_orders(): void
    {
        Http::fake(['core.test/*' => Http::failedConnection()]);
        $this->post('/checkout', $this->form());
        $order = Order::sole();

        $this->actingAs(User::factory()->create());
        $this->get('/admin/orders')->assertOk()->assertSee('Іван Петренко')->assertSee('не отправлен');
        $this->get('/admin/orders/'.$order->id)->assertOk()->assertSee('Павербанк')->assertSee('Відділення №5');
    }
}
