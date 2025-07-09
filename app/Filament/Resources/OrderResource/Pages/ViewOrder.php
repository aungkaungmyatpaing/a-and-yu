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

                                Infolists\Components\TextEntry::make('date')
                                    ->label('Order Date')
                                    ->date(),

                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Customer')
                                    ->placeholder('No customer assigned'),

                                Infolists\Components\IconEntry::make('delivered')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

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
