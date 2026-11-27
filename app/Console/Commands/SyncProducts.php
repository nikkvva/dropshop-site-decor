<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Забирает товары своей категории из dropshop-core и обновляет таблицу products.
 * Запускается раз в час (routes/console.php).
 *
 * Товары, которые пропали из категории, не удаляем (на них могут ссылаться заказы),
 * а помечаем «нет в наличии».
 */
class SyncProducts extends Command
{
    protected $signature = 'products:sync';

    protected $description = 'Синхронизировать товары с dropshop-core';

    public function handle(): int
    {
        $config = config('services.core');
        $seen = [];
        $page = 1;

        do {
            $response = Http::withHeaders(['X-Api-Key' => $config['key']])
                ->acceptJson()
                ->timeout(30)
                ->retry(3, 2000)
                ->get(rtrim($config['url'], '/')."/api/categories/{$config['category_id']}/products", ['page' => $page])
                ->throw();

            foreach ($response->json('data') ?? [] as $item) {
                $this->save($item);
                $seen[] = $item['id'];
            }

            $lastPage = $response->json('meta.last_page');
            $page++;
        } while ($page <= $lastPage);

        // пустой ответ — скорее сбой в dropshop-core, чем пустая категория: не прячем весь магазин
        if ($seen === []) {
            $this->error('dropshop-core не вернул ни одного товара — ничего не меняю.');

            return self::FAILURE;
        }

        $hidden = Product::whereNotIn('core_id', $seen)->where('is_available', true)->update(['is_available' => false]);

        $this->info('Товаров: '.count($seen).'. Пропали из категории: '.$hidden.'.');

        return self::SUCCESS;
    }

    private function save(array $item): void
    {
        $name = $item['name_ua'] ?: $item['name'];

        $product = Product::firstOrNew(['core_id' => $item['id']]);

        // адрес задаём один раз: поставщик переименует товар — старая ссылка (и место в Google) не сломается.
        // id в конце — чтобы адрес был уникальным даже у товаров с одинаковым названием
        $product->slug ??= Str::slug($name, '-', 'uk').'-'.$item['id'];

        $product->fill([
            'name' => $name,
            'description' => $item['description_ua'] ?: $item['description'],
            'vendor' => $item['vendor'],
            'vendor_code' => $item['vendor_code'],
            'price' => $item['price'],
            'old_price' => $item['old_price'],
            'currency' => $item['currency'] ?? 'UAH',
            'is_available' => $item['is_available'],
            'params' => $item['params'],
            'pictures' => $item['pictures'],
            'group_id' => $item['group_id'],
            'core_category_id' => $item['category_id'],
        ])->save();
    }
}
