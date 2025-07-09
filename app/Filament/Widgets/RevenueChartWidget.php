<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '15s';

    protected function getData(): array
    {
        $data = Order::select(
            DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
            DB::raw('SUM(total_amount) as revenue')
        )
        ->where('date', '>=', now()->subMonths(11))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (mmk)',
                    'data' => $data->pluck('revenue')->map(fn($amount) => $amount)->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
            ],
            'labels' => $data->pluck('month')->map(function ($month) {
                return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
