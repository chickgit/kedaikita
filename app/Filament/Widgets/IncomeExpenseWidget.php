<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use function Filament\Support\format_money;

class IncomeExpenseWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $todayExpenses = Expense::query()
            ->where('date_added', now()->format('Y-m-d'))
            ->get('amount');

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

        $overallIncome = Order::all('total_price')->sum('total_price');
        $overallExpense = Expense::all('amount')->sum('amount');

        return [
            Stat::make('Today Income', $this->format_money($todayOrders->sum('total_price'))),
            Stat::make('This Week Income', $this->format_money($weeklyOrders->sum('total_price'))),
            Stat::make('This Month Income', $this->format_money($monthlyOrders->sum('total_price'))),
            Stat::make('Today Expense', $this->format_money($todayExpenses->sum('amount'))),
            Stat::make('This Week Expense', $this->format_money($weeklyExpenses->sum('amount'))),
            Stat::make('This Month Expense', $this->format_money($monthlyExpenses->sum('amount'))),
            Stat::make('Overall Income', $this->format_money($overallIncome)),
            Stat::make('Overall Expense', $this->format_money($overallExpense)),
            Stat::make('Balance', $this->format_money($overallIncome - $overallExpense)),
        ];
    }

    protected function format_money(int $amount,)
    {
        return format_money($amount, 'IDR');
    }
}
