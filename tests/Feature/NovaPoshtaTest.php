<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Подсказки Новой Почты: города и отделения/почтоматы (API подменён через Http::fake). */
class NovaPoshtaTest extends TestCase
{
    private const KYIV = '8d5a980d-391c-11dd-90d9-001a92567626';

    public function test_cities_only_with_branches(): void
    {
        Http::fake(['api.novaposhta.ua/*' => Http::response(['success' => true, 'data' => [['Addresses' => [
            ['Present' => 'м. Київ, Київська обл.', 'DeliveryCity' => self::KYIV, 'Warehouses' => 2000],
            ['Present' => 'с. Київець, Львівська обл.', 'DeliveryCity' => 'x', 'Warehouses' => 0],
        ]]]])]);

        $this->getJson('/np/cities?q=київ')
            ->assertOk()
            ->assertExactJson([['name' => 'м. Київ, Київська обл.', 'ref' => self::KYIV]]);

        Http::assertSent(fn (Request $r) => $r['calledMethod'] === 'searchSettlements' && $r['methodProperties']['CityName'] === 'київ');
    }

    public function test_branches_of_both_types_sorted_by_number(): void
    {
        // API отвечает по типу пункта (TypeOfWarehouseRef)
        $byType = [
            '841339c7-591a-42e2-8233-7a0a00f0ed6f' => [ // поштове відділення
                ['Number' => '10', 'CategoryOfWarehouse' => 'Branch', 'Description' => 'Відділення №10'],
                ['Number' => '2', 'CategoryOfWarehouse' => 'Branch', 'Description' => 'Відділення №2'],
                ['Number' => '7', 'CategoryOfWarehouse' => 'DropOff', 'Description' => 'Пункт прийому №7'],
            ],
            '9a68df70-0267-42a8-bb5c-37f427e36ee4' => [ // вантажне
                ['Number' => '1', 'CategoryOfWarehouse' => 'Branch', 'Description' => 'Відділення №1 (до 1000 кг)'],
            ],
        ];
        Http::fake(fn (Request $r) => Http::response(['success' => true, 'data' => $byType[$r['methodProperties']['TypeOfWarehouseRef']]]));

        $this->getJson('/np/points?city_ref='.self::KYIV.'&type=branch')
            ->assertExactJson(['Відділення №1 (до 1000 кг)', 'Відділення №2', 'Відділення №10']);

        // повтор — из кэша, без новых запросов к API
        $this->getJson('/np/points?city_ref='.self::KYIV.'&type=branch')->assertOk();
        Http::assertSentCount(2);
    }

    public function test_postomats_only_by_search(): void
    {
        Http::fake(['api.novaposhta.ua/*' => Http::response(['success' => true, 'data' => [
            ['Number' => '1414', 'CategoryOfWarehouse' => 'Postomat', 'Description' => 'Поштомат №1414: вул. Назарука, 1'],
        ]])]);

        $this->getJson('/np/points?city_ref='.self::KYIV.'&type=postomat')->assertExactJson([]);
        Http::assertNothingSent(); // без текста не грузим тысячи почтоматов

        $this->getJson('/np/points?city_ref='.self::KYIV.'&type=postomat&q=Назарука')
            ->assertExactJson(['Поштомат №1414: вул. Назарука, 1']);
        Http::assertSent(fn (Request $r) => $r['methodProperties']['FindByString'] === 'Назарука'
            && $r['methodProperties']['TypeOfWarehouseRef'] === 'f9316480-5f2d-425d-bc2c-ac7cd29decf0');
    }

    public function test_api_error_gives_empty_list(): void
    {
        Http::fake(['api.novaposhta.ua/*' => Http::response(['success' => false, 'data' => [], 'errors' => ['API key expired']])]);

        $this->getJson('/np/cities?q=київ')->assertExactJson([]);
        $this->getJson('/np/cities?q=к')->assertExactJson([]); // короче 2 букв — не ищем
        $this->getJson('/np/points?city_ref=bad&type=branch')->assertStatus(422);
    }
}
