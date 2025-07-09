<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusWidget extends ChartWidget
{
    protected static ?string $heading = 'Order Status Distribution';
    protected static ?int $sort = 7;

    protected function getData(): array
    {
        $delivered = Order::where('delivered', true)->count();
        $pending = Order::where('delivered', false)->count();

        return [
            'datasets' => [
                [
                    'data' => [$delivered, $pending],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                    ],
                ],
            ],
            'labels' => ['Delivered', 'Pending'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
