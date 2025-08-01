<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Session;


class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('print_invoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn ($record) => route('orders.invoice', $record))
                ->openUrlInNewTab(),

            Actions\Action::make('toggle_invoice_image')
                ->label('Invoice Image')
                ->action(function () {
                    // Toggle visibility by setting a session variable
                    $isVisible = !Session::get('invoice_image_visible', false);
                    Session::put('invoice_image_visible', $isVisible);
                }),
            Actions\Action::make('toggle_note_image')
                ->label('Check Note')
                ->action(function () {
                    // Toggle visibility by setting a session variable
                    $isVisible = !Session::get('note_image_visible', false);
                    Session::put('note_image_visible', $isVisible);
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $isViewingInvoiceImage = Session::get('invoice_image_visible', false);
        $isViewingNoteImage = Session::get('note_image_visible', false);

        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->badge()
                                    ->color('primary'),


                                Infolists\Components\IconEntry::make('delivered')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                Infolists\Components\TextEntry::make('progress_percent')
                                    ->label('Progress %')
                                    ->default(function ($record) {
                                        // Calculate progress percent in the view
                                        if ($record->delivered) {
                                            return '100%';
                                        }

                                        if (!$record->date || !$record->progress_day) {
                                            return 'N/A';
                                        }

                                        $start = \Carbon\Carbon::parse($record->date);
                                        $now = \Carbon\Carbon::now();

                                        $daysPassed = $start->diffInDays($now);
                                        $percent = min(round(($daysPassed / $record->progress_day) * 100), 100);

                                        return $percent . '%';
                                    })
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('progress_stage')
                                    ->label('Progress Stage')
                                    ->default(function ($record) {
                                        if ($record->delivered) {
                                            return 'Delivery';
                                        }

                                        if (!$record->date || !$record->progress_day) {
                                            return 'Not Started';
                                        }

                                        $start = \Carbon\Carbon::parse($record->date);
                                        $now = \Carbon\Carbon::now();

                                        $daysPassed = $start->diffInDays($now);
                                        $percent = min(round(($daysPassed / $record->progress_day) * 100), 100);

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
                                            $percent < 100 => 'Parking + Order Check',
                                            default => 'Delivery',
                                        };
                                    })
                                    ->badge()
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('progress_day')
                                    ->suffix( ' Days')
                                    ->numeric()
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('date')
                                    ->label('Order Date')
                                    ->date(),

                                Infolists\Components\TextEntry::make('end_date')
                                    ->label('Order End Date')
                                    ->placeholder('No End Date')
                                    ->date(),

                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Customer')
                                    ->placeholder('No customer assigned'),
                                    
                               Infolists\Components\TextEntry::make('user.phones')
                                    ->label('Customer Phones')
                                    ->default(fn ($record) => collect([
                                        $record->user?->phone,
                                        $record->user?->phone_2,
                                        $record->user?->phone_3,
                                    ])->filter()->implode(', '))
                                    ->placeholder('No customer phones'),

                                Infolists\Components\TextEntry::make('total_amount')
                                    ->suffix(' mmk'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime(),
                            ]),
                    ]),
                Infolists\Components\Section::make('Note Image')
                    ->schema([
                        TextEntry::make('note'),
                        SpatieMediaLibraryImageEntry::make('note_img')
                            ->label('Note Image')
                            ->collection('image')
                            ->width('100%')
                            ->height('auto')
                    ])
                    ->visible($isViewingNoteImage),
                Infolists\Components\Section::make('Invoice Image')
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('image')
                            ->label('Uploaded Invoice')
                            ->collection('image')
                            ->width('100%')
                            ->height('auto')
                    ])
                    ->visible($isViewingInvoiceImage),
                Infolists\Components\Section::make('Order Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('orderItems')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('product.name')
                                            ->label('Product'),

                                        Infolists\Components\TextEntry::make('product.design')
                                            ->label('Design'),

                                        Infolists\Components\TextEntry::make('product.quality')
                                            ->label('Quality')
                                            ->placeholder('Not specified'),

                                        Infolists\Components\TextEntry::make('qty')
                                            ->label('Quantity')
                                            ->numeric(),
                                    ]),
                            ])
                            ->contained(false),
                    ]),

                Infolists\Components\Section::make('Order Summary')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_items')
                                    ->label('Total Items')
                                    ->getStateUsing(fn ($record) => $record->orderItems->count()),

                                Infolists\Components\TextEntry::make('total_quantity')
                                    ->label('Total Quantity')
                                    ->getStateUsing(fn ($record) => $record->orderItems->sum('qty')),
                            ]),
                    ]),
            ]);
    }
}
