<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderItemsRelationManagerResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('progress_day')
                                    ->label('Progress Days')
                                    ->numeric()
                                    ->default(30)
                                    ->suffix( 'Days')
                                    ->nullable()
                                    ->hint('Number of days expected to complete'),
                                Forms\Components\DatePicker::make('date')
                                    ->label('Order Date')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('Y-m-d'),
                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Order End Date')
                                    ->nullable()
                                    ->displayFormat('Y-m-d'),
                                Forms\Components\Select::make('user_id')
                                    ->label('Customer')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('school_name')
                                            ->nullable()
                                            ->hint('Optional')
                                            ->maxLength(255),
                                           Forms\Components\TextInput::make('email')
                                                ->nullable()
                                                ->email()
                                                ->hint('Optional')
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('phone')
                                                ->tel()
                                                ->nullable()
                                                ->hint('Optional')
                                                ->unique(ignoreRecord: true),
                                            Forms\Components\TextInput::make('phone_2')
                                                ->tel()
                                                ->nullable()
                                                ->hint('Optional')
                                                ->unique(ignoreRecord: true),
                                            Forms\Components\TextInput::make('phone_3')
                                                ->tel()
                                                ->nullable()
                                                ->hint('Optional')
                                                ->unique(ignoreRecord: true),
                                            Forms\Components\TextInput::make('address')
                                                ->nullable()
                                                ->hint('Optional'),
                                            SpatieMediaLibraryFileUpload::make('image')
                                                    ->collection('image')
                                                    ->conversion('thumb')
                                                    ->nullable()
                                                    ->columnSpanFull(),
                                    ])
                                    ->nullable(),

                                Forms\Components\TextInput::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return 'INV-' . now()->format('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT);
                                    }),
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total Amount')
                                    ->nullable()
                                    ->suffix(' mmk')
                                    ->hint('Optional')
                                    ->numeric()
                                    ->default(0),

                                Forms\Components\Toggle::make('delivered')
                                    ->label('Delivered')
                                    ->default(false),
                                Textarea::make('note')
                                    ->nullable()
                                    ->hint('Optional')
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('note_img')
                                    ->label('Note Image')
                                    ->collection('note_img')
                                    ->conversion('thumb')
                                    ->nullable()
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->label('Invoice')
                                    ->collection('image')
                                    ->conversion('thumb')
                                    ->nullable()
                                    ->columnSpanFull(),     
                        ]),
                    ]),

                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('orderItems')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(3)
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
                                                    ->multiple()
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

                                        Forms\Components\Placeholder::make('product_info')
                                            ->label('Product Info')
                                            ->content(function (Forms\Get $get) {
                                                $productId = $get('product_id');
                                                if (!$productId) {
                                                    return 'Select a product to view details';
                                                }
                                                
                                                $product = Product::find($productId);
                                                if (!$product) {
                                                    return 'Product not found';
                                                }
                                                
                                                return "Design: {$product->design}\nQuality: {$product->quality}\nStock: {$product->qty}";
                                            }),
                                    ]),
                            ])
                            ->collapsible()
                            ->cloneable()
                            ->deletable()
                            ->addActionLabel('Add Item')
                            ->defaultItems(1)
                            ->minItems(1)
                            ->orderColumn('id')
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                // You can add any data manipulation here before creating
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                // You can add any data manipulation here before saving
                                return $data;
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    Tables\Columns\TextColumn::make('invoice_number')
                        ->label('Invoice #')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('progress_percent')
                        ->label('Progress %')
                        ->getStateUsing(function (Order $record) {
                            // If the order is delivered, set progress to 100%
                            if ($record->delivered) {
                                return '100%';
                            }

                            // If there is no start date or progress day, return 'N/A'
                            if (!$record->date || !$record->progress_day) {
                                return 'N/A';
                            }

                            // Calculate the progress
                            $start = \Carbon\Carbon::parse($record->date);
                            $now = \Carbon\Carbon::now();

                            $daysPassed = $start->diffInDays($now);
                            $percent = min(round(($daysPassed / $record->progress_day) * 100), 100);

                            return $percent . '%';
                        })
                        ->toggleable(),
                    Tables\Columns\TextColumn::make('progress_stage')
                        ->label('Progress Stage')
                        ->getStateUsing(function (Order $record) {
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
                        ->toggleable(),


                    Tables\Columns\TextColumn::make('date')
                        ->label('Order Date')
                        ->date()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('end_date')
                        ->label('Order End Date')
                        ->date()
                        ->toggleable()
                        ->placeholder('No End Date')
                        ->sortable(),

                    Tables\Columns\TextInputColumn::make('progress_day')
                        ->label('Progress Days')
                        ->type('number')
                        ->rules(['required', 'integer', 'min:1'])
                        ->sortable()
                        ->beforeStateUpdated(function ($record, $state) {
                            // Optional: Add validation or logging here
                        })
                        ->afterStateUpdated(function ($record, $state) {
                            Notification::make()
                                ->title('Progress Days Updated')
                                ->body("Progress days updated to {$state} days for order {$record->invoice_number}")
                                ->success()
                                ->send();
                        }),


                    Tables\Columns\TextColumn::make('user.name')
                        ->label('Customer')
                        ->searchable()
                        ->sortable()
                        ->placeholder('No customer'),
                    Tables\Columns\TextColumn::make('user.phone')
                        ->label('Phone')
                        ->searchable()
                        ->sortable()
                        ->toggleable()
                        ->placeholder('No customer phone'),
                    Tables\Columns\TextColumn::make('user.phone_2')
                        ->label('Phone 2')
                        ->searchable()
                        ->sortable()
                        ->toggleable()
                        ->placeholder('No customer phone 2'),
                    Tables\Columns\TextColumn::make('user.phone_3')
                        ->label('Phone 3')
                        ->searchable()
                        ->sortable()
                        ->toggleable()
                        ->placeholder('No customer phone 3'),
                    Tables\Columns\TextColumn::make('user.school_name')
                        ->label('School')
                        ->searchable()
                        ->sortable()
                        ->placeholder('No Shcool'),

                    Tables\Columns\TextColumn::make('order_items_count')
                        ->label('Items')
                        ->getStateUsing(function ($record) {
                            // Count the related orderItems for each Order
                            return $record->orderItems()->count();
                        })
                        ->sortable(),

                    Tables\Columns\TextColumn::make('total_quantity')
                        ->label('Total Qty')
                        ->getStateUsing(function (Order $record) {
                            return $record->orderItems->sum('qty');
                        })
                        ->sortable(false),

                    Tables\Columns\TextColumn::make('total_amount')
                        ->suffix(' mmk')
                        ->sortable(false),

                    Tables\Columns\IconColumn::make('delivered')
                        ->boolean()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('created_at')
                        ->label('Created')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    Tables\Columns\TextColumn::make('updated_at')
                        ->label('Updated')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('delivered')
                    ->label('Delivery Status')
                    ->boolean()
                    ->trueLabel('Delivered')
                    ->falseLabel('Not Delivered')
                    ->native(false),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('end_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('From End Date'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('To End Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggle_delivered')
                    ->label(fn (Order $record) => $record->delivered ? 'Mark as Not Delivered' : 'Mark as Delivered')
                    ->icon(fn (Order $record) => $record->delivered ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Order $record) => $record->delivered ? 'danger' : 'success')
                    ->action(fn (Order $record) => $record->update(['delivered' => !$record->delivered]))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_delivered')
                        ->label('Mark as Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['delivered' => true])))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('mark_not_delivered')
                        ->label('Mark as Not Delivered')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['delivered' => false])))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
                    OrderItemsRelationManager::class, // Register the relation manager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
