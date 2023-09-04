<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('forms.order.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('forms.order.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('forms.order.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('fields.email'))
                    ->email()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('date_order')
                    ->label(__('fields.date_order'))
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('total_price')
                    ->label(__('fields.total_price'))
                    ->required()
                    ->readOnly()
                    ->live()
                    ->prefix('Rp.'),
                Repeater::make('order_details')
                    ->label(__('fields.order_details'))
                    ->schema([
                        Select::make('product_name')
                            ->label(__('fields.product_name'))
                            ->options(fn () => Product::orderBy('name', 'asc')->get()->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (?string $state, ?string $old, Set $set, Get $get) {
                                $set('product_price', Product::find($state)->price);
                                $set('product_amount', 1);
                                self::calculateTotalPrice($set, $get);
                            })
                            ->required(),
                        TextInput::make('product_price')
                            ->label(__('fields.product_price'))
                            ->prefix('Rp.')
                            ->numeric()
                            ->live()
                            ->readOnly(),
                        TextInput::make('product_amount')
                            ->label(__('fields.product_amount'))
                            ->disabled(fn (Get $get): bool => !$get('product_name'))
                            ->required(fn (Get $get): bool => !!$get('product_name'))
                            ->numeric()
                            ->live(debounce:500)
                            ->afterStateUpdated(function (?int $state, ?int $old, Set $set, Get $get) {
                                self::calculateTotalPrice($set, $get);
                            }),
                        TextInput::make('sub_total')
                            ->label(__('fields.sub_total'))
                            ->readOnly()
                            ->numeric()
                            ->live()
                            ->prefix('Rp.'),
                        TextInput::make('notes')
                            ->label(__('fields.notes'))
                    ])
                    ->columnSpanFull()
                    ->grid(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('fields.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('fields.total_price'))
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_order')
                    ->label(__('fields.date_order'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_order', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    private static function calculateTotalPrice(Set $set, Get $get): void
    {
        $productAmount = $get('product_amount');
        $productPrice = $get('product_price');
        $set('sub_total', $productPrice * $productAmount);

        $orderDetails = $get('../../order_details');
        $arrSubTotalOrderDetails = Arr::pluck($orderDetails, 'sub_total');
        $set('../../total_price', array_sum($arrSubTotalOrderDetails));
    }
}
