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

    /** Все отделения города: ['Відділення №1: вул. ...', ...] */
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

    /** Почтоматы города по номеру или улице (до 50). */
    public function postomats(string $cityRef, string $query): array
    {
        return $this->remember("np:postomats:$cityRef:".md5(mb_strtolower($query)), fn () => array_values($this->warehouses([
            'CityRef' => $cityRef,
            'TypeOfWarehouseRef' => self::POSTOMAT_TYPE,
            'FindByString' => $query,
            'Limit' => '50',
            'Page' => '1',
        ])));
    }

    /** getWarehouses → ['номер-описание' => описание], только пункты выдачи. */
    private function warehouses(array $properties): array
    {
        $points = [];
        foreach ($this->call('AddressGeneral', 'getWarehouses', $properties) as $point) {
            if (in_array($point['CategoryOfWarehouse'] ?? '', self::CATEGORIES, true)) {
                $points[(int) ($point['Number'] ?? 0).'-'.$point['Description']] = $point['Description'];
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
                'methodProperties' => $properties,
            ]);
        } catch (Throwable) {
            return [];
        }

        return $response->json('success') ? (array) $response->json('data') : [];
    }

    /** Кэш на сутки, но пустой ответ (ошибка API) не кэшируем. */
    private function remember(string $key, callable $load): array
    {
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
