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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date_order')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->readOnly()
                    ->prefix('Rp.'),
                Repeater::make('order_details')
                    ->schema([
                        Select::make('product_name')
                            ->options(fn () => Product::all()->pluck('name', 'id'))
                            ->live()
                            ->afterStateUpdated(function (?string $state, ?string $old, Set $set) {
                                $set('product_price', Product::find($state)->price);
                            })
                            ->required(),
                        TextInput::make('product_price')
                            ->prefix('Rp.')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('product_amount')
                            ->disabled(fn (Get $get): bool => !$get('product_name'))
                            ->required(fn (Get $get): bool => !!$get('product_name'))
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (?int $state, ?int $old, Set $set, Get $get) {
                                $productPrice = $get('product_price');
                                $set('sub_total', $productPrice * $state);

                                $orderDetails = $get('../../order_details');
                                $arrSubTotalOrderDetails = Arr::pluck($orderDetails, 'sub_total');
                                $set('../../total_price', array_sum($arrSubTotalOrderDetails));
                                // dd($orderDetails, $arrSubTotalOrderDetails, array_sum($arrSubTotalOrderDetails));
                                // $total = Arr::pluck(, '*.sub_total');
                                // dd($total);
                                // dd($state, $old, $get('../../total_price'), $get('../../order_details'));
                                // $set('product_price', Product::find($state)->price);
                            }),
                        TextInput::make('sub_total')
                            ->readOnly()
                            ->numeric()
                            ->prefix('Rp.')
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_order')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
}
