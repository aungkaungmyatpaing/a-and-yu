<?php

namespace App\Filament\Resources\OrderItemsRelationManagerResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $recordTitleAttribute = 'product.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn (Product $record): string => "{$record->name} - {$record->design}")
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('design')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('quality')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('qty')
                                    ->label('Stock Quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\DatePicker::make('date')
                                    ->required()
                                    ->default(now()),
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->collection('image')
                                    ->conversion('thumb')
                                    ->nullable()
                            ]),

                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->step(1),
                    ]),

                Forms\Components\Placeholder::make('product_details')
                    ->label('Product Details')
                    ->content(function (Forms\Get $get) {
                        $productId = $get('product_id');
                        if (!$productId) {
                            return 'Select a product to view details';
                        }
                        
                        $product = Product::find($productId);
                        if (!$product) {
                            return 'Product not found';
                        }
                        
                        return "Product: {$product->name}\nDesign: {$product->design}\nQuality: {$product->quality}\nStock: {$product->qty}";
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.design')
                    ->label('Design')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.quality')
                    ->label('Quality')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.qty')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
