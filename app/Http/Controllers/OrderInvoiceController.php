<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderInvoiceController extends Controller
{
    public function show(Order $order)
    {
        // Eager load necessary relations
        $order->load(['user', 'orderItems.product', 'media']);

        // Compute progress percent
        $progressPercent = $this->calculateProgressPercent($order);
        $progressStage = $this->calculateProgressStage($order);

        // Prepare phones
        $customerPhones = collect([
            $order->user?->phone,
            $order->user?->phone_2,
            $order->user?->phone_3
        ])->filter()->implode(', ');

        return view('invoices.order-voucher', [
            'order' => $order,
            'customer' => $order->user,
            'customerPhones' => $customerPhones,
            'orderItems' => $order->orderItems,
            'totalItems' => $order->orderItems->count(),
            'totalQuantity' => $order->orderItems->sum('qty'),
            'progressPercent' => $progressPercent,
            'progressStage' => $progressStage,
            'generatedAt' => Carbon::now(),
        ]);
    }

    protected function calculateProgressPercent($order)
    {
        if ($order->delivered) return '100%';

        if (!$order->date || !$order->progress_day) return 'N/A';

        $start = Carbon::parse($order->date);
        $now = Carbon::now();

        $daysPassed = $start->diffInDays($now);
        $percent = min(round(($daysPassed / $order->progress_day) * 100), 100);

        return $percent . '%';
    }

    protected function calculateProgressStage($order)
    {
        if ($order->delivered) return 'Delivery';

        if (!$order->date || !$order->progress_day) return 'Not Started';

        $start = Carbon::parse($order->date);
        $now = Carbon::now();

        $daysPassed = $start->diffInDays($now);
        $percent = min(round(($daysPassed / $order->progress_day) * 100), 100);

        return match (true) {
            $percent < 10 => 'Pending',
            $percent < 20 => 'Order Confirmed',
            $percent < 30 => 'Fabric Purchased',
            $percent < 40 => 'Cutting',
            $percent < 50 => 'Collar Attached',
            $percent < 60 => 'Threading Completed',
            $percent < 70 => 'Sewing in Progress',
            $percent < 80 => 'Washing Done',
            $percent < 90 => 'Buttonhole + Ironing',
            $percent < 100 => 'Packing + Order Check',
            default => 'Delivery',
        };
    }
}
