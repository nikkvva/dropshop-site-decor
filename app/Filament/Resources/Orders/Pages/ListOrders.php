<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;

/** Список заказов (без кнопки «Создать»: заказы приходят только с сайта). */
class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
