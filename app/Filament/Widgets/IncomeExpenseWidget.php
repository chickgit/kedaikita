<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IncomeExpenseWidget extends BaseWidget
{
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

        $todayOrders = Order::query()
            ->where('date_order', now()->format('Y-m-d'))
            ->get('total_price');

        $weeklyOrders = Order::query()
            ->whereBetween('date_order', [now()->startOfWeek(), now()->endOfWeek()])
            ->get('total_price');

        $monthlyOrders = Order::query()
            ->whereBetween('date_order', [now()->startOfMonth(), now()->endOfMonth()])
            ->get('total_price');

        return [
            Stat::make('Today Income', $todayOrders->sum('total_price')),
            Stat::make('This Week Income', $weeklyOrders->sum('total_price')),
            Stat::make('This Month Income', $monthlyOrders->sum('total_price')),
            Stat::make('Today Expense', $todayExpenses->sum('amount')),
            Stat::make('This Week Expense', $weeklyExpenses->sum('amount')),
            Stat::make('This Month Expense', $monthlyExpenses->sum('amount')),
        ];
    }
}
