<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOrderProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:check-progress {--dry-run : Show what would be updated without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check order progress and mark as delivered when progress days are reached';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking order progress...');

        // Get orders that are not delivered and have progress_day set
        $orders = Order::where('delivered', false)
            ->whereNotNull('progress_day')
            ->whereNotNull('date')
            ->where('progress_day', '>', 0)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders found to check.');
            return Command::SUCCESS;
        }

        $updatedCount = 0;
        $today = Carbon::now();

        $this->info("Found {$orders->count()} orders to check.");
        
        // Create a table to show the results
        $tableData = [];

        foreach ($orders as $order) {
            $orderDate = Carbon::parse($order->date);
            $expectedEndDate = $orderDate->copy()->addDays($order->progress_day);
            $isOverdue = $today->greaterThanOrEqualTo($expectedEndDate);
            $daysPassed = $orderDate->diffInDays($today);

            $tableData[] = [
                'Invoice' => $order->invoice_number,
                'Customer' => $order->user->name ?? 'N/A',
                'Order Date' => $orderDate->format('Y-m-d'),
                'Progress Days' => $order->progress_day,
                'Expected End' => $expectedEndDate->format('Y-m-d'),
                'Days Passed' => $daysPassed,
                'Status' => $isOverdue ? 'OVERDUE' : 'IN PROGRESS',
                'Action' => $isOverdue ? 'WILL UPDATE' : 'NO ACTION'
            ];

            if ($isOverdue) {
                if (!$this->option('dry-run')) {
                    // Update the order to delivered
                    $order->update([
                        'delivered' => true,
                        'end_date' => $today->format('Y-m-d') // Set end date to today
                    ]);
                    
                    $this->line("✓ Updated order {$order->invoice_number} to delivered");
                } else {
                    $this->line("➤ Would update order {$order->invoice_number} to delivered (dry run)");
                }
                
                $updatedCount++;
            }
        }

        // Display the table
        $this->table([
            'Invoice',
            'Customer', 
            'Order Date',
            'Progress Days',
            'Expected End',
            'Days Passed',
            'Status',
            'Action'
        ], $tableData);

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN MODE: No orders were actually updated.");
            $this->info("Orders that would be marked as delivered: {$updatedCount}");
        } else {
            $this->info("Successfully updated {$updatedCount} orders to delivered status.");
        }

        // Show summary
        if ($updatedCount > 0) {
            $this->newLine();
            $this->info('Summary:');
            $this->line("- Total orders checked: {$orders->count()}");
            $this->line("- Orders " . ($this->option('dry-run') ? 'that would be ' : '') . "marked as delivered: {$updatedCount}");
        }

        return Command::SUCCESS;
    }
}