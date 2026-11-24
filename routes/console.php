<?php

use Illuminate\Support\Facades\Schedule;

// Товары и наличие из dropshop-core — раз в час (выполняет контейнер scheduler).
Schedule::command('products:sync')->hourly()->withoutOverlapping();
