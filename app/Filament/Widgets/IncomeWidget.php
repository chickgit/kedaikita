<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IncomeWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $todayOrders = Order::query()
            ->where('date_order', now())
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
        ];
    }
}
