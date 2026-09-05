<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use Domain\Products\Models\Product;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->lazy() // Автогенерация slug на лету в админке
                            ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->disabled() // Запрещаем редактировать вручную
                            ->dehydrated() // Но разрешаем отправлять в БД
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Коммерческие данные')
                    ->schema([
                        TextInput::make('price_cents')
                            ->label('Цена (в копейках)')
                            ->numeric()
                            ->required(),

                        TextInput::make('stock')
                            ->label('Остаток на складе')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Select::make('status')
                            ->options([
                                'draft' => 'Черновик',
                                'published' => 'Опубликован',
                                'archived' => 'Архив',
                            ])
                            ->default('draft')
                            ->required(),

                        Select::make('vendor_id')
                            ->label('Продавец')
                            ->relationship('vendor', 'name') // Filament автоматически свяжет по vendor_id
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_cents')
                    ->label('Цена')
                    ->money('USD', divideBy: 100) // Красиво форматирует копейки в доллары/рубли
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Склад')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'archived',
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликован',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
