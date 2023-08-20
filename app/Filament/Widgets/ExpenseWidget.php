<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ExpenseWidget extends BaseWidget
{
    // protected static string $view = 'filament.widgets.expense-widget';

    protected function getStats(): array
    {
        $todayExpenses = Expense::query()
            ->where('date_added', now()->format('Y-m-d'))
            ->get();

        $weeklyExpenses = Expense::query()
            ->whereBetween('date_added', [now()->startOfWeek(), now()->endOfWeek()])
            ->get('amount');

        $monthlyExpenses = Expense::query()
            ->whereBetween('date_added', [now()->startOfMonth(), now()->endOfMonth()])
            ->get('amount');

        return [
            Stat::make('Today Expense', $todayExpenses->sum('amount')),
            Stat::make('This Week Expense', $weeklyExpenses->sum('amount')),
            Stat::make('This Month Expense', $monthlyExpenses->sum('amount')),
        ];
    }
}
