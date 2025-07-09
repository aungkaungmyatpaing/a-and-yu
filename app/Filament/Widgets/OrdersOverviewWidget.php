<?php

// app/Filament/Widgets/OrdersOverviewWidget.php
namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class OrdersOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get current month stats
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        
        $currentMonthOrders = Order::where('date', '>=', $currentMonth)->count();
        $lastMonthOrders = Order::where('date', '>=', $lastMonth)
            ->where('date', '<', $currentMonth)
            ->count();
        
        $ordersGrowth = $lastMonthOrders > 0 
            ? (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 
            : 0;

        // Revenue stats
        $currentMonthRevenue = Order::where('date', '>=', $currentMonth)->sum('total_amount');
        $lastMonthRevenue = Order::where('date', '>=', $lastMonth)
            ->where('date', '<', $currentMonth)
            ->sum('total_amount');
        
        $revenueGrowth = $lastMonthRevenue > 0 
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
            : 0;

        return [
            Stat::make('Total Orders', Order::count())
                ->description($ordersGrowth >= 0 ? "{$ordersGrowth}% increase" : "{$ordersGrowth}% decrease")
                ->descriptionIcon($ordersGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Monthly Revenue',number_format($currentMonthRevenue). ' mmk' )
                ->description($revenueGrowth >= 0 ? "{$revenueGrowth}% increase" : "{$revenueGrowth}% decrease")
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Pending Orders', Order::where('delivered', false)->count())
                ->description('Awaiting delivery')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Products', Product::count())
                ->description('In inventory')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}