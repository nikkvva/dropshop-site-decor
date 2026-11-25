<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Order;
use App\Services\CoreOrderSender;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Раздел «Заказы»: заказы с сайта, только просмотр.
 * Дальше заказ ведётся в dropshop-core; здесь видно, дошёл ли он туда, и можно дослать вручную.
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $modelLabel = 'заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?int $navigationSort = -1;

    /** Число неотправленных заказов — красным рядом с пунктом меню. */
    public static function getNavigationBadge(): ?string
    {
        $count = Order::whereNull('core_number')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('№')->sortable(),
                TextColumn::make('created_at')->label('Создан')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('customer_name')->label('Покупатель')->searchable(),
                TextColumn::make('phone')->label('Телефон')->searchable(),
                TextColumn::make('total')->label('Сумма')->money('UAH')->sortable(),
                TextColumn::make('payment_method')->label('Оплата')->formatStateUsing(fn (string $state) => Order::PAYMENT_METHODS[$state] ?? $state),
                TextColumn::make('core_number')
                    ->label('В dropshop-core')
                    ->badge()
                    ->placeholder('не отправлен')
                    ->color('success'),
            ])
            ->filters([
                TernaryFilter::make('sent')
                    ->label('Отправлен в dropshop-core')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('core_number'),
                        false: fn (Builder $query) => $query->whereNull('core_number'),
                    ),
            ])
            ->recordActions([self::sendAction()])
            ->defaultSort('id', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Покупатель')->schema([
                    TextEntry::make('customer_name')->label('Имя'),
                    TextEntry::make('phone')->label('Телефон'),
                    TextEntry::make('email')->label('Email')->placeholder('—'),
                    TextEntry::make('comment')->label('Комментарий')->placeholder('—'),
                ]),
                Section::make('Доставка и оплата')->schema([
                    TextEntry::make('city')->label('Город'),
                    TextEntry::make('delivery_type')->label('Способ')->formatStateUsing(fn (string $state) => Order::DELIVERY_TYPES[$state] ?? $state),
                    TextEntry::make('delivery_point')->label('Отделение / почтомат'),
                    TextEntry::make('payment_method')->label('Оплата')->formatStateUsing(fn (string $state) => Order::PAYMENT_METHODS[$state] ?? $state),
                ]),
                Section::make('dropshop-core')->columnSpanFull()->columns(3)->schema([
                    TextEntry::make('core_number')->label('Номер в dropshop-core')->placeholder('не отправлен'),
                    TextEntry::make('sent_at')->label('Отправлен')->dateTime('d.m.Y H:i')->placeholder('—'),
                    TextEntry::make('send_error')->label('Последняя ошибка')->placeholder('—')->color('danger'),
                ]),
                Section::make('Товары')->columnSpanFull()->schema([
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->table([
                            TableColumn::make('Товар'),
                            TableColumn::make('Цена'),
                            TableColumn::make('Кол-во'),
                        ])
                        ->schema([
                            TextEntry::make('name'),
                            TextEntry::make('price')->money('UAH'),
                            TextEntry::make('quantity'),
                        ]),
                    TextEntry::make('total')->label('Итого')->money('UAH'),
                ]),
            ]);
    }

    /** Кнопка «Отправить в dropshop-core» — для заказов, которые не ушли сами. */
    public static function sendAction(): Action
    {
        return Action::make('sendToCore')
            ->label('Отправить в dropshop-core')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->visible(fn (Order $record): bool => ! $record->isSent())
            ->requiresConfirmation()
            ->action(function (Order $record, CoreOrderSender $sender): void {
                $result = $sender->send($record);

                $result === CoreOrderSender::SENT
                    ? Notification::make()->success()->title('Заказ отправлен: '.$record->core_number)->send()
                    : Notification::make()->danger()->title('Не отправлен')->body($record->send_error)->persistent()->send();
            });
    }

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
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
