<?php

use Illuminate\Support\Facades\Schedule;

// Товары и наличие из dropshop-core — раз в час (выполняет контейнер scheduler).
Schedule::command('products:sync')->hourly()->withoutOverlapping();

// Заказы, которые не дошли до dropshop-core сразу (не было связи).
Schedule::command('orders:resend')->everyTenMinutes()->withoutOverlapping();
