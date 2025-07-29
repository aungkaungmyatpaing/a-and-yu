<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .invoice-title {
            font-size: 20px;
            color: #666;
            margin-bottom: 10px;
        }

        .invoice-phone {
            font-size: 15px;
            color: #666;
            margin-bottom: 10px;
            margin-top: 5px;
        }
        
        .invoice-number {
            font-size: 16px;
            background: #2563eb;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }
        
        .info-box {
            flex: 1;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }
        
        .info-box h3 {
            font-size: 14px;
            color: #2563eb;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .progress-section {
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 8px;
        }
        
        .progress-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        
        .progress-item {
            flex: 1;
        }
        
        .progress-label {
            font-size: 10px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .progress-value {
            font-size: 18px;
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .items-table th {
            background: #2563eb;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .items-table tbody tr:hover {
            background-color: #f3f4f6;
        }
        
        .summary-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .summary-row.total {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            border-top: 2px solid #2563eb;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        @media print {
            body { margin: 0; }
            .invoice-container { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">A & Yu Graduation suit & Uniform</div>
            <div class="invoice-title">ORDER INVOICE VOUCHER</div>
            <div class="invoice-number">Invoice #{{ $order->invoice_number }}</div>
            <div class="invoice-phone">09 254 446 004</div>
        </div>

        <!-- Customer & Order Info -->
        <div class="info-section">
            <div class="info-box">
                <h3>Customer Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $customer->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $customerPhones ?: 'N/A' }}</span>
                </div>
            </div>
            
            <div class="info-box">
                <h3>Order Details</h3>
                <div class="info-row">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value">{{ $order->date ? \Carbon\Carbon::parse($order->date)->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">End Date:</span>
                    <span class="info-value">{{ $order->end_date ? \Carbon\Carbon::parse($order->end_date)->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Progress Days:</span>
                    <span class="info-value">{{ $order->progress_day ?? 'N/A' }} Days</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge {{ $order->delivered ? 'status-delivered' : 'status-pending' }}">
                            {{ $order->delivered ? 'Delivered' : 'In Progress' }}
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <!-- <div class="progress-section">
            <div class="progress-title">Order Progress Status</div>
            <div class="progress-info">
                <div class="progress-item">
                    <div class="progress-label">PROGRESS PERCENTAGE</div>
                    <div class="progress-value">{{ $progressPercent }}</div>
                </div>
                <div class="progress-item">
                    <div class="progress-label">CURRENT STAGE</div>
                    <div class="progress-value">{{ $progressStage }}</div>
                </div>
            </div>
        </div> -->

        <!-- Order Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Product Name</th>
                    <th style="width: 25%;">Design</th>
                    <th style="width: 20%;">Quality</th>
                    <th style="width: 10%;">Qty</th>
                    <!-- <th style="width: 10%;">Unit Price</th> -->
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td>{{ $item->product->design ?? 'N/A' }}</td>
                    <td>{{ $item->product->quality ?? 'Not specified' }}</td>
                    <td style="text-align: center;">{{ $item->qty }}</td>
                    <!-- <td style="text-align: right;">
                        @if($item->product && $item->product->price)
                            {{ number_format($item->product->price) }} MMK
                        @else
                            N/A
                        @endif
                    </td> -->
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-row">
                <span>Total Items:</span>
                <span>{{ $totalItems }}</span>
            </div>
            <div class="summary-row">
                <span>Total Quantity:</span>
                <span>{{ $totalQuantity }}</span>
            </div>
            <div class="summary-row total">
                <span>Total Amount:</span>
                <span>{{ number_format($order->total_amount) }} MMK</span>
            </div>
        </div>

        <!-- Note Section -->
        @if($order->note)
        <div class="info-box" style="margin-top: 20px;">
            <h3>Order Notes</h3>
            <p>{{ $order->note }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
            <p>Created at {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>