<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Товар сайта — копия товара из dropshop-core, обновляется командой products:sync. */
class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'is_available' => 'boolean',
            'params' => 'array',
            'pictures' => 'array',
        ];
    }
}
