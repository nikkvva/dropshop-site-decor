<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Раздел «Товары» в админке сайта. Только просмотр:
 * товары приходят из dropshop-core и перезаписываются при каждой синхронизации.
 */
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $modelLabel = 'товар';

    protected static ?string $pluralModelLabel = 'Товары';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Фото')
                    ->state(fn (Product $record): ?string => $record->pictures[0] ?? null)
                    ->checkFileExistence(false)
                    ->imageHeight(56)
                    ->square(),
                TextColumn::make('name')->label('Название')->searchable()->sortable()->wrap()->lineClamp(2),
                TextColumn::make('vendor_code')->label('Артикул')->searchable()->toggleable(),
                TextColumn::make('price')->label('Цена')->money('UAH')->sortable(),
                IconColumn::make('is_available')->label('В наличии')->boolean()->sortable(),
                TextColumn::make('updated_at')->label('Обновлён')->dateTime('d.m.Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_available')->label('В наличии'),
            ])
            ->defaultSort('name');
    }

    // Запрещаем создание, правку и удаление — даже по прямой ссылке.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
        ];
    }
}
