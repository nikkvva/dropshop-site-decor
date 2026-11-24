<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\ListRecords;

/** Список товаров (без кнопки «Создать»). */
class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
}
