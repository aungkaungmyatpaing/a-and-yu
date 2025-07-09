<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Orders';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '15s';

    protected function getData(): array
    {
        $data = Order::select(
            DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as revenue')
        )
        ->where('date', '>=', now()->subMonths(11))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $data->pluck('month')->map(function ($month) {
                return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
