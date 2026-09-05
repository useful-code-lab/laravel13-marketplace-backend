<?php

namespace App\Filament\Resources;


use App\Filament\Resources\OrderResource\Pages;
use Domain\Orders\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о заказе')
                    ->schema([
                        TextInput::make('id')
                            ->label('UUID Заказа')
                            ->disabled(), // UUID нельзя менять

                        Select::make('status')
                            ->options([
                                'pending' => 'Ожидает оплаты',
                                'paid' => 'Оплачен',
                                'shipped' => 'Доставлен',
                                'cancelled' => 'Отменен',
                            ])
                            ->required(),

                        TextInput::make('total_cents')
                            ->label('Сумма (в копейках)')
                            ->disabled(),
                    ])->columns(3),

                Section::make('Состав заказа (Товары)')
                    ->schema([
                        // Выводим связанные позиции order_items
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Товар')
                                    ->relationship('product', 'title')
                                    ->disabled(),
                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->disabled(),
                                TextInput::make('price_cents')
                                    ->label('Цена на момент покупки')
                                    ->disabled(),
                            ])
                            ->columns(3)
                            ->disabled() // Запрещаем менеджеру менять состав чека вручную
                            ->addable(false)
                            ->deletable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Заказа')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_cents')
                    ->label('Итого')
                    ->money('USD', divideBy: 100)
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'paid' => 'Оплачен',
                        'shipped' => 'Доставлен',
                        'cancelled' => 'Отменен',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'paid' => 'Оплачен',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Позволит менеджеру зайти и переключить статус
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
