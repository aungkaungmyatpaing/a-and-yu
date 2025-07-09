<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Products';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::select('products.*')
                    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->selectRaw('SUM(order_items.qty) as total_sold')
                    ->groupBy('products.id')
                    ->orderByDesc('total_sold')
                    ->limit(10)
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
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Total Sold')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return OrderItem::where('product_id', $record->id)->sum('qty');
                    }),
            ])
            ->defaultSort('total_sold', 'desc');
    }
}
