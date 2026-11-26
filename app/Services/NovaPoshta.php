<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Подсказки адреса Новой Почты на оформлении заказа.
 * API отвечает и без ключа; ключ (NOVA_POSHTA_API_KEY) — на случай, если без него начнут ограничивать.
 * Ошибка API → пустой список: поля на сайте остаются обычным текстом.
 */
class NovaPoshta
{
    private const URL = 'https://api.novaposhta.ua/v2.0/json/';

    // Типы пунктов (TypeOfWarehouseRef), проверено на API 26.11.2026.
    // Отделений в городе сотни — отдаём список целиком (Київ: 433 за ~0,6 с).
    // Почтоматов тысячи (Київ: полный список > 10 с) — только поиск по номеру или улице.
    private const BRANCH_TYPES = [
        '841339c7-591a-42e2-8233-7a0a00f0ed6f', // поштове відділення
        '9a68df70-0267-42a8-bb5c-37f427e36ee4', // вантажне відділення
    ];

    private const POSTOMAT_TYPE = 'f9316480-5f2d-425d-bc2c-ac7cd29decf0';

    // CategoryOfWarehouse: среди этих типов попадаются пункты только для приёма (DropOff) и склады (Fulfillment) — их не показываем.
    // Store — пункт выдачи в магазине, забрать посылку там можно.
    private const CATEGORIES = ['Branch', 'Store', 'Postomat'];

    /** Населённые пункты с отделениями: [['name' => 'м. Київ, Київська обл.', 'ref' => '...'], ...] */
    public function cities(string $query): array
    {
        return $this->remember('np:cities:'.md5(mb_strtolower($query)), function () use ($query) {
            $data = $this->call('Address', 'searchSettlements', ['CityName' => $query, 'Limit' => '20', 'Page' => '1']);

            return collect($data[0]['Addresses'] ?? [])
                ->filter(fn ($city) => ($city['Warehouses'] ?? 0) > 0 && filled($city['DeliveryCity'] ?? null))
                ->map(fn ($city) => ['name' => $city['Present'], 'ref' => $city['DeliveryCity']])
                ->values()
                ->all();
        });
    }

    /** Все отделения города: [['name' => 'Відділення №1: вул. ...', 'ref' => '...'], ...] */
    public function branches(string $cityRef): array
    {
        return $this->remember("np:branches:$cityRef", function () use ($cityRef) {
            // отделений одного типа в городе заведомо меньше 2000 (Київ: ~430) — одной страницы хватает
            $points = [];
            foreach (self::BRANCH_TYPES as $typeRef) {
                $points += $this->warehouses(['CityRef' => $cityRef, 'TypeOfWarehouseRef' => $typeRef, 'Limit' => '2000', 'Page' => '1']);
            }

            ksort($points, SORT_NATURAL); // по номеру: №1, №2, … №10

            return array_values($points);
        });
    }

    /**
     * Почтоматы города по номеру или улице (до 50).
     * API ищет точный кусок текста: «перемоги 18» не находит ничего. Поэтому «улица + дом» делим:
     * в API уходит улица, из ответа оставляем дом (18 → «18», «18а», но не «185»).
     */
    public function postomats(string $cityRef, string $query): array
    {
        return $this->remember("np:postomats:$cityRef:".md5(mb_strtolower($query)), function () use ($cityRef, $query) {
            $house = null;
            if (preg_match('/^(.*\pL.*?)[\s,]+(\d+\pL?)$/u', $query, $m)) {
                [$query, $house] = [$m[1], $m[2]];
            }

            $points = $this->warehouses([
                'CityRef' => $cityRef,
                'TypeOfWarehouseRef' => self::POSTOMAT_TYPE,
                'FindByString' => $query,
                'Limit' => $house ? '500' : '50', // на одной улице почтоматов может быть больше 50
                'Page' => '1',
            ]);

            if ($house) {
                $points = array_filter($points, fn ($point) => preg_match('/,\s*'.preg_quote($house, '/').'(?!\d)/iu', $point['name']));
            }

            return array_slice(array_values($points), 0, 50);
        });
    }

    /** Пункт выдачи по Ref: ['name', 'ref', 'city_ref', 'type' => branch|postomat|null]; null — нет такого или API не ответил. */
    public function point(string $ref): ?array
    {
        $point = $this->call('AddressGeneral', 'getWarehouses', ['Ref' => $ref])[0] ?? null;

        if (! $point || ! in_array($point['CategoryOfWarehouse'] ?? '', self::CATEGORIES, true)) {
            return null;
        }

        $type = match (true) {
            in_array($point['TypeOfWarehouse'] ?? '', self::BRANCH_TYPES, true) => 'branch',
            ($point['TypeOfWarehouse'] ?? '') === self::POSTOMAT_TYPE => 'postomat',
            default => null,
        };

        return ['name' => $point['Description'], 'ref' => $point['Ref'], 'city_ref' => $point['CityRef'] ?? null, 'type' => $type];
    }

    /** Отвечает ли API сейчас (короткий запрос без кэша). */
    public function available(): bool
    {
        return (bool) $this->call('AddressGeneral', 'getWarehouseTypes', []);
    }

    /** getWarehouses → ['номер-описание' => ['name' => описание, 'ref' => Ref]], только пункты выдачи. */
    private function warehouses(array $properties): array
    {
        $points = [];
        foreach ($this->call('AddressGeneral', 'getWarehouses', $properties) as $point) {
            if (in_array($point['CategoryOfWarehouse'] ?? '', self::CATEGORIES, true)) {
                $points[(int) ($point['Number'] ?? 0).'-'.$point['Description']] = ['name' => $point['Description'], 'ref' => $point['Ref']];
            }
        }

        return $points;
    }

    private function call(string $model, string $method, array $properties): array
    {
        try {
            $response = Http::timeout(10)->post(self::URL, [
                'apiKey' => (string) config('services.nova_poshta.key'),
                'modelName' => $model,
                'calledMethod' => $method,
                'methodProperties' => $properties ?: new \stdClass, // пустой массив ушёл бы как [], API ждёт {}
            ]);
        } catch (Throwable) {
            return [];
        }

        return $response->json('success') ? (array) $response->json('data') : [];
    }

    /** Кэш на сутки, но пустой ответ (ошибка API) не кэшируем. Поменял формат ответа — подними v2, иначе сутки отдаётся старый. */
    private function remember(string $key, callable $load): array
    {
        $key = 'v2:'.$key;

        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }

        $result = $load();
        if ($result) {
            Cache::put($key, $result, now()->addDay());
        }

        return $result;
    }
}
