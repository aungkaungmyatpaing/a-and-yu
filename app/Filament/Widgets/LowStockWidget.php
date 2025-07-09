<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Alert';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::where('qty', '<=', 10)->orderBy('qty', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('design')
                    ->label('Design')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('quality')
                    ->label('Quality')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('qty')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state) => $state <= 5 ? 'danger' : 'warning'),
                
                Tables\Columns\TextColumn::make('date')
                    ->label('Last Updated')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('qty', 'asc')
            ->emptyStateHeading('No Low Stock Items')
            ->emptyStateDescription('All products are well stocked!')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
