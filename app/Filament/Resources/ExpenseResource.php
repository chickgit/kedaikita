<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('forms.expense.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('forms.expense.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('forms.expense.navigation_label');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date_added')
                    ->label(__('fields.date_added'))
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label(__('fields.amount'))
                    ->required()
                    ->minValue(1)
                    ->default(1)
                    ->live(debounce:500)
                    ->afterStateUpdated(function (?string $state, ?string $old, Set $set, Get $get) {
                        $set('total_price', $state * $get('price'));
                    })
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label(__('fields.price'))
                    ->required()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('Rp.')
                    ->live(debounce:500)
                    ->afterStateUpdated(function (?string $state, ?string $old, Set $set, Get $get) {
                        $set('total_price', $state * $get('amount'));
                    })
                    ->numeric(),
                Forms\Components\TextInput::make('total_price')
                    ->label(__('fields.total_price'))
                    ->default(0)
                    ->readOnly()
                    ->prefix('Rp.')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                Tables\Columns\TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_added')
                    ->label(__('fields.date_added'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('fields.amount'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('fields.price'))
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('fields.total_price'))
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('date_added', 'desc')
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
